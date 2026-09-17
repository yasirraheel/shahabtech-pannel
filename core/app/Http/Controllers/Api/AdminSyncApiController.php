<?php

namespace App\Http\Controllers\Api;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\AccountListingController;
use App\Models\AccountListing;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class AdminSyncApiController extends Controller
{
    public function __construct()
    {
        if (!headers_sent()) {
            @header("Access-Control-Allow-Origin: *");
            @header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Admin-Key, Accept");
            @header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        }
    }

    /**
     * Resolve deterministic secure admin sync key derived from APP_KEY
     */
    public static function getSyncToken()
    {
        return 'st_sync_' . substr(hash('sha256', config('app.key') . 'admin_sync_master_2026'), 0, 24);
    }

    /**
     * Authorize request via admin_key
     */
    private function authorizeAdmin(Request $request)
    {
        $configuredToken = self::getSyncToken();
        $providedToken = $request->header('X-Admin-Key') ?: $request->input('admin_key');

        if (empty($providedToken) || !hash_equals((string) $configuredToken, (string) $providedToken)) {
            return false;
        }
        return true;
    }

    /**
     * List all Google Flow accounts available for synchronization
     * GET /api/extension/admin-sync/accounts
     */
    public function accounts(Request $request)
    {
        if (!$this->authorizeAdmin($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid Admin Sync Key.',
            ], 401);
        }

        $accounts = AccountListing::where('status', Status::LISTING_ACTIVE)
            ->whereHas('socialMedia', function($q) {
                $q->active();
            })
            ->with('socialMedia:id,name,url,domain')
            ->get()
            ->map(function($acc) {
                $syncText = $acc->last_synced_at ? diffForHumans($acc->last_synced_at) : 'Never';
                $isFlow = stripos($acc->socialMedia->name, 'flow') !== false || stripos($acc->socialMedia->domain, 'flow') !== false || stripos($acc->url, 'flow') !== false;

                return [
                    'id'               => $acc->id,
                    'title'            => $acc->title,
                    'platform'         => $acc->socialMedia->name,
                    'is_flow'          => $isFlow,
                    'cookie_status'    => $acc->cookie_status,
                    'last_synced_at'   => $acc->last_synced_at ? $acc->last_synced_at->toDateTimeString() : null,
                    'last_synced_text' => $syncText,
                    'last_sync_source' => $acc->last_sync_source,
                ];
            });

        return response()->json([
            'success'  => true,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Ingest and sanitize fresh cookies sent from Admin Browser Extension
     * POST /api/extension/admin-sync
     */
    public function sync(Request $request)
    {
        if (!$this->authorizeAdmin($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid Admin Sync Key.',
            ], 401);
        }

        $request->validate([
            'account_id' => 'required|integer',
            'cookies'    => 'required',
        ]);

        $account = AccountListing::with('socialMedia')->find($request->account_id);
        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => "Account ID {$request->account_id} not found in panel.",
            ], 404);
        }

        $rawCookies = $request->input('cookies');
        if (is_string($rawCookies)) {
            $rawCookies = json_decode($rawCookies, true);
        }

        if (!is_array($rawCookies) || empty($rawCookies)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid cookie payload provided.',
            ], 422);
        }

        // Sanitize cookies using the platform's domain rules
        $platformDomain = $account->socialMedia->domain ?? 'flow.google.com';
        $cleaned = AccountListingController::sanitizeAccountCookies($rawCookies, $platformDomain);

        if (empty($cleaned)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid cookies found after sanitization.',
            ], 422);
        }

        // Save fresh cookies and update sync health markers
        $account->account_info = $cleaned;
        $account->cookie_status = 1;
        $account->cookie_checked_at = now();
        $account->cookie_check_error = null;
        $account->last_synced_at = now();
        $account->last_sync_source = $request->input('source', 'admin_extension');
        $account->save();

        return response()->json([
            'success'           => true,
            'message'           => 'Cookies synchronized successfully.',
            'account_id'        => $account->id,
            'title'             => $account->title,
            'cookie_count'      => count($cleaned),
            'synced_at'         => now()->toDateTimeString(),
            'synced_time_text'  => diffForHumans(now()),
        ]);
    }
}
