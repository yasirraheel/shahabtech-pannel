<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear', function(){
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});


Route::get('cron', 'CronController@cron')->name('cron');

// Extension API Routes (Session-based via normal auth)
Route::prefix('api/extension')->name('api.extension.')->namespace('Api')->middleware('auth')->group(function () {
    Route::get('me', 'ExtensionController@me')->name('me');
    Route::get('platforms', 'ExtensionController@platforms')->name('platforms');
    Route::get('cookies/{platformId}', 'ExtensionController@getCookies')->name('cookies');
});


// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{id}', 'replyTicket')->name('reply');
    Route::post('close/{id}', 'closeTicket')->name('close');
    Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
});


Route::controller('WebController')->group(function () {
    // Account Listing
    Route::get('/account-listing', 'accountListing')->name('account.listing');
    Route::get('/account-listing/{slug}/{id}', 'accountListingDetails')->name('account.listing.details');
});

Route::controller('SiteController')->group(function () {
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactSubmit');
    Route::get('/change/{lang?}', 'changeLanguage')->name('lang');
    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');
    Route::get('/cookie/accept', 'cookieAccept')->name('cookie.accept');
    
    Route::get('extension/download/{filename?}', 'downloadExtension')->name('extension.download');

    Route::post('subscribe', 'subscribe')->name('subscribe');
    Route::get('blog/{slug}', 'blogDetails')->name('blog.details');
    Route::get('blogs', 'blogs')->name('blogs');
    Route::get('buy-accounts', 'buyAccounts')->name('buy.account');
    Route::get('plans', 'plans')->name('plans');



    Route::get('policy/{slug}', 'policyPages')->name('policy.pages');

    Route::get('placeholder-image/{size}', 'placeholderImage')->withoutMiddleware('maintenance')->name('placeholder.image');
    Route::get('maintenance-mode','maintenance')->withoutMiddleware('maintenance')->name('maintenance');
    Route::get('/{slug}', 'pages')->name('pages');
    Route::get('/', 'index')->name('home');
});
