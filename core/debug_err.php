<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Bind ViewComposers
view()->composer('admin.partials.sidenav', function ($view) {
    $view->with([
        'bannedUsersCount'           => \App\Models\User::banned()->count(),
        'expiredUsersCount'          => \App\Models\User::expired()->count(),
        'emailUnverifiedUsersCount' => \App\Models\User::emailUnverified()->count(),
        'mobileUnverifiedUsersCount'   => \App\Models\User::mobileUnverified()->count(),
        'kycUnverifiedUsersCount'   => \App\Models\User::kycUnverified()->count(),
        'kycPendingUsersCount'   => \App\Models\User::kycPending()->count(),
        'pendingTicketCount'         => \App\Models\SupportTicket::whereIN('status', [\App\Constants\Status::TICKET_OPEN, \App\Constants\Status::TICKET_REPLY])->count(),
        'pendingDepositsCount'    => \App\Models\Deposit::pending()->count(),
        'pendingWithdrawCount'    => \App\Models\Withdrawal::pending()->count(),
        'updateAvailable'    => false,
    ]);
});

view()->composer('admin.partials.topnav', function ($view) {
    $view->with([
        'adminNotifications' => \App\Models\AdminNotification::where('is_read', \App\Constants\Status::NO)->with('user')->orderBy('id', 'desc')->take(10)->get(),
        'adminNotificationCount' => \App\Models\AdminNotification::where('is_read', \App\Constants\Status::NO)->count(),
    ]);
});

try {
    $controller = new App\Http\Controllers\Admin\ManageUsersController();
    $response = $controller->activeUsers();
    if ($response instanceof \Illuminate\View\View) {
        $html = $response->render();
        echo "SUCCESS: Rendered HTML length: " . strlen($html) . " bytes\n";
    } else {
        var_dump($response);
    }
} catch (\Throwable $e) {
    echo "EXCEPTION THROWN:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
