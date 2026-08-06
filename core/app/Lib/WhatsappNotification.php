<?php

namespace App\Lib;

use App\Models\AccountListing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappNotification
{
    /**
     * Send WhatsApp alert notification to configured Admin WhatsApp numbers when an account cookie expires.
     */
    public static function sendCookieExpiryNotification(AccountListing $expiredAccount, string $errorReason = 'Cookie verification failed')
    {
        $numbers = (array) gs('admin_whatsapp');
        $numbers = array_values(array_filter(array_map('trim', $numbers)));

        if (empty($numbers)) {
            return;
        }

        $platform = $expiredAccount->socialMedia;
        $platformName = $platform ? $platform->name : 'N/A';
        $platformId = $expiredAccount->social_media_id;

        // Fetch all remaining valid accounts for this platform
        $validAccounts = AccountListing::where('social_media_id', $platformId)
            ->where('cookie_status', 1)
            ->where('id', '!=', $expiredAccount->id)
            ->get();

        $validCount = $validAccounts->count();

        // Build details of each valid account and its user count after load balancing
        $accountDetails = "";
        if ($validCount > 0) {
            foreach ($validAccounts as $acc) {
                $userCount = $acc->assignedUsersCount();
                $accountDetails .= "• {$acc->title} (ID #{$acc->id}): {$userCount} Users\n";
            }
        } else {
            $accountDetails = "⚠️ NO VALID ACCOUNTS REMAINING FOR THIS PLATFORM!\n";
        }

        $nowStr = now()->format('Y-m-d H:i:s');

        $message = "⚠️ *COOKIE EXPIRED ALERT*\n\n";
        $message .= "*Platform*: {$platformName}\n";
        $message .= "*Expired Account*: {$expiredAccount->title} (ID #{$expiredAccount->id})\n";
        $message .= "*Error Reason*: {$errorReason}\n\n";
        $message .= "*Remaining Valid Accounts*: {$validCount}\n";
        $message .= "*Post-Load Balancing User Counts*:\n{$accountDetails}\n";
        $message .= "*Time*: {$nowStr}";

        // Prepare contacts payload
        $contacts = [];
        foreach ($numbers as $num) {
            $cleanNum = preg_replace('/[^0-9]/', '', $num);
            if ($cleanNum) {
                $contacts[] = [
                    'number' => $cleanNum,
                    'message' => $message,
                ];
            }
        }

        if (empty($contacts)) {
            return;
        }

        try {
            $response = Http::withHeaders([
                'Api-key' => 'e637cd7e-c2bb-406f-ad30-8ae69178e1f6',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->withoutVerifying()->post('https://omnireach.shahabtech.com/api/whatsapp/send', [
                'contact' => $contacts
            ]);

            Log::info("WhatsApp Cookie Expiry Notification sent: " . $response->body());
        } catch (\Throwable $e) {
            Log::error("Failed to send WhatsApp Cookie Expiry Notification: " . $e->getMessage());
        }
    }
}
