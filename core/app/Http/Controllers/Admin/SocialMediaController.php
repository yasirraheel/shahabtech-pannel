<?php

namespace App\Http\Controllers\Admin;

use App\Lib\FormProcessor;
use App\Models\SocialMedia;
use App\Models\AccountListing;
use App\Models\User;
use App\Constants\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SocialMediaController extends Controller
{
    public function index()
    {
        $pageTitle    = 'Manage Platforms';
        $socialsMedia = SocialMedia::searchable(['name'])->withCount('accountListing')->orderBy('name')->paginate(getPaginate());
        return view('admin.social_media.index', compact('pageTitle', 'socialsMedia'));
    }

    public function store(Request $request, $id = null)
    {
        $request->validate([
            'name'         => 'required',
            'domain'       => 'required',
            'url'          => 'required|url',
            'instructions' => 'nullable|string',
        ]);

        if ($id) {
            $socialMedia   = SocialMedia::findOrFail($id);
            $notifyMessage = 'Platform updated successfully';
        } else {
            $socialMedia   = new SocialMedia();
            $notifyMessage = 'Platform added successfully';
        }

        $socialMedia->name         = $request->name;
        $socialMedia->domain       = $request->domain;
        $socialMedia->url          = $request->url;
        $socialMedia->instructions = $request->instructions;
        $socialMedia->save();

        $notify[] = ['success', $notifyMessage];
        return back()->withNotify($notify);
    }

    public function info($id)
    {
        $socialMedia = SocialMedia::findOrFail($id);
        $form        = $socialMedia->form;
        $pageTitle   = 'Add Social Media Information';
        return view('admin.social_media.add_info', compact('pageTitle', 'socialMedia', 'form'));
    }

    public function infoStore(Request $request, $id)
    {
        $formProcessor = new FormProcessor();

        $generatorValidation = $formProcessor->generatorValidation();
        $validation          =  $generatorValidation['rules'];
        $request->validate($validation, $generatorValidation['messages']);

        $socialMedia   = SocialMedia::findOrFail($id);
        $generate      = $formProcessor->generate('social_media', true, 'id', $socialMedia->form_id);

        $socialMedia->form_id = $generate->id;
        $socialMedia->save();

        $notify[] = ['success', 'Social media requirements updated successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return SocialMedia::changeStatus($id);
    }

    public function delete($id)
    {
        $socialMedia = SocialMedia::withCount('accountListing')->findOrFail($id);

        // Delete associated accounts
        foreach ($socialMedia->accountListing as $account) {
            $account->delete();
        }

        $socialMedia->delete();

        $notify[] = ['success', 'Platform and all associated accounts deleted successfully'];
        return back()->withNotify($notify);
    }

    public function loadBalance(Request $request, $id)
    {
        $request->validate([
            'mode' => 'required|in:override_manual,keep_manual',
        ]);

        $platform = SocialMedia::findOrFail($id);
        
        // Get all active accounts for this platform
        $activeAccounts = AccountListing::where('social_media_id', $platform->id)
            ->where('status', Status::LISTING_ACTIVE)
            ->get();

        if ($activeAccounts->isEmpty()) {
            $notify[] = ['error', "Cannot load balance: No active accounts found for platform {$platform->name}."];
            return back()->withNotify($notify);
        }

        $allPlatformAccountIds = AccountListing::where('social_media_id', $platform->id)->pluck('id')->toArray();

        // Fetch all active (non-banned) users who have a valid subscription
        $users = User::where('status', Status::USER_ACTIVE)
            ->where('plan_id', '!=', 0)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->get();

        $updatedUsersCount = 0;
        $mode = $request->mode;

        // Track user counts per active account for round-robin load balancing
        $accountUserCounts = [];
        foreach ($activeAccounts as $acc) {
            $accId = (int) $acc->id;
            $count = User::whereJsonContains('account_ids', $accId)
                ->orWhereJsonContains('account_ids', (string) $accId)
                ->count();
            $accountUserCounts[$accId] = $count;
        }

        foreach ($users as $user) {
            $currentAccountIds = (array) ($user->account_ids ?? []);
            
            // Find if user currently has an account assigned for this platform
            $existingPlatformAccountIds = array_intersect($currentAccountIds, $allPlatformAccountIds);
            
            if (!empty($existingPlatformAccountIds) && $mode === 'keep_manual') {
                // Keep existing manual assignment for this platform
                continue;
            }

            // Remove all current accounts belonging to this platform from user's account_ids
            $otherPlatformAccountIds = array_diff($currentAccountIds, $allPlatformAccountIds);

            // Pick the active account with the lowest user count
            asort($accountUserCounts);
            $bestAccountId = key($accountUserCounts);

            $otherPlatformAccountIds[] = $bestAccountId;
            $accountUserCounts[$bestAccountId]++;

            $user->account_ids = array_values(array_unique($otherPlatformAccountIds));
            $user->timestamps = false;
            $user->save();
            $user->timestamps = true;

            $updatedUsersCount++;
        }

        $modeText = $mode === 'override_manual' ? 'overriding manual assignments' : 'keeping manual assignments';
        $notify[] = ['success', "Load balancing completed for {$platform->name}: {$updatedUsersCount} users updated across {$activeAccounts->count()} active account(s) ({$modeText})."];
        return back()->withNotify($notify);
    }
}
