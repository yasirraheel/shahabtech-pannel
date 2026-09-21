<?php
namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Rules\FileTypeValidate;

class ManageUsersController extends Controller
{

    public function allUsers()
    {
        $pageTitle = 'All Users';
        if (request()->account_id) {
            $account = \App\Models\AccountListing::find(request()->account_id);
            if ($account) {
                $pageTitle = 'Users Assigned to: ' . $account->title;
            }
        }
        $users = $this->userData();
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function activeUsers()
    {
        $pageTitle = 'Active Users';
        if (request()->account_id) {
            $account = \App\Models\AccountListing::find(request()->account_id);
            if ($account) {
                $pageTitle = 'Active Users Assigned to: ' . $account->title;
            }
        }
        $users = $this->userData('active');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function expiredUsers()
    {
        $pageTitle = 'Expired Users';
        if (request()->account_id) {
            $account = \App\Models\AccountListing::find(request()->account_id);
            if ($account) {
                $pageTitle = 'Expired Users Assigned to: ' . $account->title;
            }
        }
        $users = $this->userData('expired');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function bannedUsers()
    {
        $pageTitle = 'Banned Users';
        $users = $this->userData('banned');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailUnverifiedUsers()
    {
        $pageTitle = 'Email Unverified Users';
        $users = $this->userData('emailUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function kycUnverifiedUsers()
    {
        $pageTitle = 'KYC Unverified Users';
        $users = $this->userData('kycUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function kycPendingUsers()
    {
        $pageTitle = 'KYC Unverified Users';
        $users = $this->userData('kycPending');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailVerifiedUsers()
    {
        $pageTitle = 'Email Verified Users';
        $users = $this->userData('emailVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function mobileUnverifiedUsers()
    {
        $pageTitle = 'Mobile Unverified Users';
        $users = $this->userData('mobileUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function mobileVerifiedUsers()
    {
        $pageTitle = 'Mobile Verified Users';
        $users = $this->userData('mobileVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    public function usersWithBalance()
    {
        $pageTitle = 'Users with Balance';
        $users = $this->userData('withBalance');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    protected function userData($scope = null){
        if ($scope) {
            $users = User::$scope();
        }else{
            $users = User::query();
        }

        if (request()->account_id) {
            $accId = (int) request()->account_id;
            $users = $users->where(function($q) use ($accId) {
                $q->whereJsonContains('account_ids', $accId)
                  ->orWhereJsonContains('account_ids', (string) $accId);
            });
        }
        
        $users = $users->searchable(['username','email']);
        
        if (request()->has('sort')) {
            $sortVal = request()->sort === 'last_seen' ? 'last_seen' : 'id';
            session(['admin_users_sort' => $sortVal]);
        }

        $currentSort = session('admin_users_sort', 'id');

        if ($currentSort === 'last_seen') {
            $users = $users->orderBy('last_seen', 'desc');
        } else {
            $users = $users->orderBy('id', 'desc');
        }
        
        return $users->paginate(getPaginate());
    }

    public function create()
    {
        $pageTitle = 'Add New User';
        $domain = parse_url(config('app.url') ?: url('/'), PHP_URL_HOST) ?: request()->getHost();
        $accounts = \App\Models\AccountListing::with('socialMedia')
            ->active()
            ->where('cookie_status', '!=', 0)
            ->orderBy('social_media_id', 'asc')
            ->get();
        return view('admin.users.create', compact('pageTitle', 'accounts', 'domain'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:80',
            'email_prefix' => 'nullable|string|max:60',
            'password' => 'nullable|string|min:4',
            'account_ids' => 'nullable|array',
            'account_ids.*' => 'integer|exists:account_listings,id',
        ]);

        $name = trim($request->name ?: ($request->firstname . ' ' . $request->lastname));
        $nameParts = array_values(array_filter(explode(' ', $name)));
        $firstname = $nameParts[0] ?? 'User';
        $lastname  = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : $firstname;

        $domain = parse_url(config('app.url') ?: url('/'), PHP_URL_HOST) ?: request()->getHost();
        $rawPrefix = trim($request->email_prefix ?: $request->email ?: $request->username ?: strtolower(str_replace(' ', '_', $name)));

        if (str_contains($rawPrefix, '@')) {
            $emailParts = explode('@', $rawPrefix);
            $emailPrefix = strtolower(preg_replace('/[^a-z0-9._-]/i', '', $emailParts[0]));
            $email = $emailPrefix . '@' . ($emailParts[1] ?: $domain);
            $username = $emailPrefix;
        } else {
            $emailPrefix = strtolower(preg_replace('/[^a-z0-9._-]/i', '', $rawPrefix));
            if (empty($emailPrefix)) {
                $emailPrefix = 'user_' . rand(1000, 9999);
            }
            $email = $emailPrefix . '@' . $domain;
            $username = $emailPrefix;
        }

        // Ensure unique username & email
        $baseUsername = $username;
        while (User::where('username', $username)->orWhere('email', $email)->exists()) {
            $username = $baseUsername . '_' . rand(100, 999);
            $email = $username . '@' . $domain;
        }

        $password = $request->password ?: \Illuminate\Support\Str::random(10);

        $user = new User();
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->email = $email;
        $user->username = $username;
        $user->password = \Illuminate\Support\Facades\Hash::make($password);
        $user->country_name = 'United States';
        $user->country_code = 'US';
        $user->dial_code = '1';
        $user->mobile = null;
        $user->plan_id = 0;
        $user->account_prices = [];
        $user->account_ids = array_values(array_map('intval', (array) ($request->account_ids ?? [])));
        $user->expires_at = now()->addDays(30);
        $user->is_trial = 0;
        $user->is_tester = $request->boolean('is_tester') ? 1 : 0;
        $user->is_exclusive = $request->boolean('is_exclusive') ? 1 : 0;

        // Force all verifications and active profile so user can log in immediately
        $user->ev = Status::VERIFIED;
        $user->sv = Status::VERIFIED;
        $user->kv = Status::KYC_VERIFIED;
        $user->tv = Status::DISABLE;
        $user->ts = Status::DISABLE;
        $user->status = Status::USER_ACTIVE;
        $user->profile_complete = 1;

        $user->save();

        $notify[] = ['success', 'User ' . $user->username . ' created successfully'];
        return redirect()->route('admin.users.detail', $user->id)->withNotify($notify);
    }

    public function detail($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = 'User Detail - ' . $user->username;
        $domain = parse_url(config('app.url') ?: url('/'), PHP_URL_HOST) ?: request()->getHost();

        $accounts = \App\Models\AccountListing::with('socialMedia')
            ->active()
            ->where('cookie_status', '!=', 0)
            ->orderBy('social_media_id', 'asc')
            ->get();

        return view('admin.users.detail', compact('pageTitle', 'user', 'accounts', 'domain'));
    }

    public function logout($id)
    {
        $user = User::findOrFail($id);
        \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $user->id)->delete();
        
        // Clear remember token so the "Remember Me" cookie cannot automatically log them back in
        $user->remember_token = null;
        $user->save();
        
        $notify[] = ['success', 'User has been logged out remotely.'];
        return back()->withNotify($notify);
    }

    public function kycDetails($id)
    {
        $pageTitle = 'KYC Details';
        $user = User::findOrFail($id);
        return view('admin.users.kyc_detail', compact('pageTitle','user'));
    }

    public function kycApprove($id)
    {
        $user = User::findOrFail($id);
        $user->kv = Status::KYC_VERIFIED;
        $user->save();

        notify($user,'KYC_APPROVE',[]);

        $notify[] = ['success','KYC approved successfully'];
        return to_route('admin.users.kyc.pending')->withNotify($notify);
    }

    public function kycReject(Request $request,$id)
    {
        $request->validate([
            'reason'=>'required'
        ]);
        $user = User::findOrFail($id);
        $user->kv = Status::KYC_UNVERIFIED;
        $user->kyc_rejection_reason = $request->reason;
        $user->save();

        notify($user,'KYC_REJECT',[
            'reason'=>$request->reason
        ]);

        $notify[] = ['success','KYC rejected successfully'];
        return to_route('admin.users.kyc.pending')->withNotify($notify);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:80',
            'email' => 'required|string|max:80',
            'password' => 'nullable|string|min:4',
            'account_ids' => 'nullable|array',
            'account_ids.*' => 'integer|exists:account_listings,id',
        ]);

        $name = trim($request->name ?: ($request->firstname . ' ' . $request->lastname));
        $nameParts = array_values(array_filter(explode(' ', $name)));
        $user->firstname = $nameParts[0] ?? $user->firstname;
        $user->lastname  = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : ($nameParts[0] ?? $user->lastname);

        $domain = parse_url(config('app.url') ?: url('/'), PHP_URL_HOST) ?: request()->getHost();
        $rawEmail = trim($request->email);
        if (!str_contains($rawEmail, '@')) {
            $rawEmail = strtolower(preg_replace('/[^a-z0-9._-]/i', '', $rawEmail)) . '@' . $domain;
        }

        // Check if email changed and if duplicate exists
        if ($rawEmail !== $user->email && User::where('email', $rawEmail)->where('id', '!=', $user->id)->exists()) {
            $notify[] = ['error', 'The email address is already taken.'];
            return back()->withNotify($notify);
        }
        $user->email = $rawEmail;

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        if ($request->has('account_ids_submitted')) {
            $user->account_ids = array_values(array_map('intval', (array) ($request->account_ids ?? [])));
        }

        if ($request->has('privileges_submitted')) {
            $user->is_tester = $request->boolean('is_tester') ? 1 : 0;
            $user->is_exclusive = $request->boolean('is_exclusive') ? 1 : 0;
        }

        $user->save();

        $notify[] = ['success', 'User details updated successfully.'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        $notify[] = ['success', 'User has been soft deleted.'];
        return back()->withNotify($notify);
    }

    public function deleteBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $count = User::whereIn('id', $request->ids)->delete();
        $notify[] = ['success', $count . ' user(s) deleted successfully.'];
        return back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'act' => 'required|in:add,sub',
            'remark' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $amount = $request->amount;
        $trx = getTrx();

        $transaction = new Transaction();

        if ($request->act == 'add') {
            $user->balance += $amount;

            $transaction->trx_type = '+';
            $transaction->remark = 'balance_add';

            $notifyTemplate = 'BAL_ADD';

            $notify[] = ['success', 'Balance added successfully'];

        } else {
            if ($amount > $user->balance) {
                $notify[] = ['error', $user->username . ' doesn\'t have sufficient balance.'];
                return back()->withNotify($notify);
            }

            $user->balance -= $amount;

            $transaction->trx_type = '-';
            $transaction->remark = 'balance_subtract';

            $notifyTemplate = 'BAL_SUB';
            $notify[] = ['success', 'Balance subtracted successfully'];
        }

        $user->save();

        $transaction->user_id = $user->id;
        $transaction->amount = $amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge = 0;
        $transaction->trx =  $trx;
        $transaction->details = $request->remark;
        $transaction->save();

        notify($user, $notifyTemplate, [
            'trx' => $trx,
            'amount' => showAmount($amount,currencyFormat:false),
            'remark' => $request->remark,
            'post_balance' => showAmount($user->balance,currencyFormat:false)
        ]);

        return back()->withNotify($notify);
    }

    public function login($id){
        $adminId = auth()->guard('admin')->id();
        Auth::loginUsingId($id);
        if ($adminId) {
            session(['is_admin_testing' => true, 'admin_id' => $adminId]);
        }
        return to_route('user.home');
    }

    public function status(Request $request,$id)
    {
        $user = User::findOrFail($id);
        if ($user->status == Status::USER_ACTIVE) {
            $request->validate([
                'reason'=>'required|string|max:255'
            ]);
            $user->status = Status::USER_BAN;
            $user->ban_reason = $request->reason;
            $notify[] = ['success','User banned successfully'];
        }else{
            $user->status = Status::USER_ACTIVE;
            $user->ban_reason = null;
            $notify[] = ['success','User unbanned successfully'];
        }
        $user->save();
        return back()->withNotify($notify);

    }


    public function showNotificationSingleForm($id)
    {
        $user = User::findOrFail($id);
        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning','Notification options are disabled currently'];
            return to_route('admin.users.detail',$user->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $user->username;
        return view('admin.users.notification_single', compact('pageTitle', 'user'));
    }

    public function sendNotificationSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required',
            'via'     => 'required|in:email,sms,push',
            'subject' => 'required_if:via,email,push',
            'image'   => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        $imageUrl = null;
        if($request->via == 'push' && $request->hasFile('image')){
            $imageUrl = fileUploader($request->image, getFilePath('push'));
        }

        $template = NotificationTemplate::where('act', 'DEFAULT')->where($request->via.'_status', Status::ENABLE)->exists();
        if(!$template){
            $notify[] = ['warning', 'Default notification template is not enabled'];
            return back()->withNotify($notify);
        }

        $user = User::findOrFail($id);
        notify($user,'DEFAULT',[
            'subject'=>$request->subject,
            'message'=>$request->message,
        ],[$request->via],pushImage:$imageUrl);
        $notify[] = ['success', 'Notification sent successfully'];
        return back()->withNotify($notify);
    }

    public function showNotificationAllForm()
    {
        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        $notifyToUser = User::notifyToUser();
        $users        = User::active()->count();
        $pageTitle    = 'Notification to Verified Users';

        if (session()->has('SEND_NOTIFICATION') && !request()->email_sent) {
            session()->forget('SEND_NOTIFICATION');
        }

        return view('admin.users.notification_all', compact('pageTitle', 'users', 'notifyToUser'));
    }

    public function sendNotificationAll(Request $request)
    {
        $request->validate([
            'via'                          => 'required|in:email,sms,push',
            'message'                      => 'required',
            'subject'                      => 'required_if:via,email,push',
            'start'                        => 'required|integer|gte:1',
            'batch'                        => 'required|integer|gte:1',
            'being_sent_to'                => 'required',
            'cooling_time'                 => 'required|integer|gte:1',
            'number_of_top_deposited_user' => 'required_if:being_sent_to,topDepositedUsers|integer|gte:0',
            'number_of_days'               => 'required_if:being_sent_to,notLoginUsers|integer|gte:0',
            'image'                        => ["nullable", 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'number_of_days.required_if'               => "Number of days field is required",
            'number_of_top_deposited_user.required_if' => "Number of top deposited user field is required",
        ]);

        if (!gs('en') && !gs('sn') && !gs('pn')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }


        $template = NotificationTemplate::where('act', 'DEFAULT')->where($request->via.'_status', Status::ENABLE)->exists();
        if(!$template){
            $notify[] = ['warning', 'Default notification template is not enabled'];
            return back()->withNotify($notify);
        }

        if ($request->being_sent_to == 'selectedUsers') {
            if (session()->has("SEND_NOTIFICATION")) {
                $request->merge(['user' => session()->get('SEND_NOTIFICATION')['user']]);
            } else {
                if (!$request->user || !is_array($request->user) || empty($request->user)) {
                    $notify[] = ['error', "Ensure that the user field is populated when sending an email to the designated user group"];
                    return back()->withNotify($notify);
                }
            }
        }

        $scope          = $request->being_sent_to;
        $userQuery      = User::oldest()->active()->$scope();

        if (session()->has("SEND_NOTIFICATION")) {
            $totalUserCount = session('SEND_NOTIFICATION')['total_user'];
        } else {
            $totalUserCount = (clone $userQuery)->count() - ($request->start-1);
        }


        if ($totalUserCount <= 0) {
            $notify[] = ['error', "Notification recipients were not found among the selected user base."];
            return back()->withNotify($notify);
        }


        $imageUrl = null;

        if ($request->via == 'push' && $request->hasFile('image')) {
            if (session()->has("SEND_NOTIFICATION")) {
                $request->merge(['image' => session()->get('SEND_NOTIFICATION')['image']]);
            }
            if ($request->hasFile("image")) {
                $imageUrl = fileUploader($request->image, getFilePath('push'));
            }
        }

        $users = (clone $userQuery)->skip($request->start - 1)->limit($request->batch)->get();

        foreach ($users as $user) {
            notify($user, 'DEFAULT', [
                'subject' => $request->subject,
                'message' => $request->message,
            ], [$request->via], pushImage: $imageUrl);
        }

        return $this->sessionForNotification($totalUserCount, $request);
    }


    private function sessionForNotification($totalUserCount, $request)
    {
        if (session()->has('SEND_NOTIFICATION')) {
            $sessionData                = session("SEND_NOTIFICATION");
            $sessionData['total_sent'] += $sessionData['batch'];
        } else {
            $sessionData               = $request->except('_token');
            $sessionData['total_sent'] = $request->batch;
            $sessionData['total_user'] = $totalUserCount;
        }

        $sessionData['start'] = $sessionData['total_sent'] + 1;

        if ($sessionData['total_sent'] >= $totalUserCount) {
            session()->forget("SEND_NOTIFICATION");
            $message = ucfirst($request->via) . " notifications were sent successfully";
            $url     = route("admin.users.notification.all");
        } else {
            session()->put('SEND_NOTIFICATION', $sessionData);
            $message = $sessionData['total_sent'] . " " . $sessionData['via'] . "  notifications were sent successfully";
            $url     = route("admin.users.notification.all") . "?email_sent=yes";
        }
        $notify[] = ['success', $message];
        return redirect($url)->withNotify($notify);
    }

    public function countBySegment($methodName){
        return User::active()->$methodName()->count();
    }

    public function list()
    {
        $query = User::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('username', 'like', '%' . request()->search . '%');
            });
        }
        $users = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success' => true,
            'users'   => $users,
            'more'    => $users->hasMorePages()
        ]);
    }

    public function notificationLog($id){
        $user = User::findOrFail($id);
        $pageTitle = 'Notifications Sent to '.$user->username;
        $logs = NotificationLog::where('user_id',$id)->with('user')->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle','logs','user'));
    }

}
