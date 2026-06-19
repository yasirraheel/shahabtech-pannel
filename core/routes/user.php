<?php

use Illuminate\Support\Facades\Route;

Route::namespace('User\Auth')->name('user.')->group(function () {

    Route::middleware('guest')->group(function(){
        Route::controller('LoginController')->group(function(){
            Route::get('/login', 'showLoginForm')->name('login');
            Route::post('/login', 'login');
            Route::get('logout', 'logout')->middleware('auth')->withoutMiddleware('guest')->name('logout');
        });

        Route::controller('RegisterController')->middleware(['guest'])->group(function(){
            Route::get('register', 'showRegistrationForm')->name('register');
            Route::post('register', 'register');
            Route::post('check-user', 'checkUser')->name('checkUser')->withoutMiddleware('guest');
        });

        Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function(){
            Route::get('reset', 'showLinkRequestForm')->name('request');
            Route::post('email', 'sendResetCodeEmail')->name('email');
            Route::get('code-verify', 'codeVerify')->name('code.verify');
            Route::post('verify-code', 'verifyCode')->name('verify.code');
        });

        Route::controller('ResetPasswordController')->group(function(){
            Route::post('password/reset', 'reset')->name('password.update');
            Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
        });

        Route::controller('SocialiteController')->group(function () {
            Route::get('social-login/{provider}', 'socialLogin')->name('social.login');
            Route::get('social-login/callback/{provider}', 'callback')->name('social.login.callback');
        });
    });

});

Route::middleware('auth')->name('user.')->group(function () {

    Route::get('user-data', 'User\UserController@userData')->name('data');
    Route::post('user-data-submit', 'User\UserController@userDataSubmit')->name('data.submit');

    //authorization
    Route::middleware('registration.complete')->namespace('User')->controller('AuthorizationController')->group(function(){
        Route::get('authorization', 'authorizeForm')->name('authorization');
        Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
        Route::post('verify-email', 'emailVerification')->name('verify.email');
        Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
        Route::post('verify-g2fa', 'g2faVerification')->name('2fa.verify');
    });

    Route::middleware(['check.status','registration.complete'])->group(function () {

        Route::namespace('User')->group(function () {

            Route::controller('UserController')->group(function(){
                Route::get('dashboard', 'home')->name('home');
                Route::post('plan/subscribe/{id}', 'subscribePlan')->name('plan.subscribe');
                Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');

                //2FA
                Route::get('twofactor', 'show2faForm')->name('twofactor');
                Route::post('twofactor/enable', 'create2fa')->name('twofactor.enable');
                Route::post('twofactor/disable', 'disable2fa')->name('twofactor.disable');

                //KYC
                Route::get('kyc-form','kycForm')->name('kyc.form');
                Route::get('kyc-data','kycData')->name('kyc.data');
                Route::post('kyc-submit','kycSubmit')->name('kyc.submit');

                //Report
                Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                Route::get('transactions','transactions')->name('transactions');

                Route::post('add-device-token','addDeviceToken')->name('add.device.token');

                 //accounts payment deposit
                 Route::post('direct-payment','directPayment')->name('direct.payment');
                 Route::post('/report', 'report')->name('report');
            });

            //Profile setting
            Route::controller('ProfileController')->group(function(){
                Route::get('general-profile', 'generalProfile')->name('general.profile');
                Route::get('profile-setting', 'profile')->name('profile.setting');
                Route::post('profile-setting', 'submitProfile');
                Route::get('change-password', 'changePassword')->name('change.password');
                Route::post('change-password', 'submitPassword');

                Route::get('social-profile','socialProfile')->name('social.profile');
                Route::post('social-profile','socialProfilePost')->name('social.profile');
                Route::get('notification-permission','notificationPermission')->name('notification.permission');
                Route::post('notification-permission','notificationPermissionPost')->name('notification.permission');
            });


            // Withdraw
            Route::controller('WithdrawController')->prefix('withdraw')->name('withdraw')->group(function(){
                Route::middleware('kyc')->group(function(){
                    Route::get('/', 'withdrawMoney');
                    Route::post('/', 'withdrawStore')->name('.money');
                    Route::get('preview', 'withdrawPreview')->name('.preview');
                    Route::post('preview', 'withdrawSubmit')->name('.submit');
                });
                Route::get('history', 'withdrawLog')->name('.history');
            });

             // Account Listing
             Route::controller('AccountListingController')->prefix('account-listing')->name('account.listing.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/purchase', 'purchaseAccount')->name('purchase');
                Route::get('/purchase/{id}', 'purchaseAccountDetails')->name('purchase.details');
                Route::get('/social-media-category/{id?}/{false?}', 'socialMediaCategory')->name('social.media.category');
                Route::post('/social-media-category/{id?}', 'socialMediaCategoryStore')->name('social.media.category.store');
                Route::get('/bidding-information/{id}', 'biddingInfo')->name('bidding.info');
                Route::post('/bidding-info/{id}', 'biddingInfoStore')->name('bidding.info.store');
                Route::get('/url-description/{id}', 'urlDescription')->name('url.description');
                Route::post('/url-description/{id}', 'urlDescriptionStore')->name('url.description.store');
                Route::get('/account-information/{id}', 'accountInfo')->name('account.info');
                Route::post('/account-information/{id}', 'accountInfoStore')->name('account.info.store');
                Route::get('/account-credential/{id}', 'accountCredentials')->name('account.credential');
                Route::post('/account-credential/{id}', 'accountCredentialsStore')->name('account.credential.store');
                Route::get('/thumbnail-image/{id}', 'thumbnailImage')->name('thumbnail.image');
                Route::post('/thumbnail-image/{id}', 'thumbnailImageStore')->name('thumbnail.image.store');
                Route::get('/publish/{id?}', 'publish')->name('publish');
                Route::post('/publish/{id}', 'publishStore')->name('publish.store');
                Route::post('/status/{id}', 'status')->name('status');
                Route::post('/delete/{id}', 'delete')->name('delete');

                Route::get('/bids/{id}', 'bid')->name('bid');
                Route::get('/my-bids', 'myBid')->name('my.bid');
                Route::post('/cancel-bid/{id}', 'cancelBid')->name('cancel.bid');
            });
        });

        // Payment
        Route::prefix('deposit')->name('deposit.')->controller('Gateway\PaymentController')->group(function(){
            Route::any('/', 'deposit')->name('index');
            Route::post('insert', 'depositInsert')->name('insert');
            Route::get('confirm', 'depositConfirm')->name('confirm');
            Route::get('manual', 'manualDepositConfirm')->name('manual.confirm');
            Route::post('manual', 'manualDepositUpdate')->name('manual.update');
        });
    });
});
