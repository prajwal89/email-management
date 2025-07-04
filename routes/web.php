<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Prajwal89\EmailManagement\Controllers\Http\NewsletterController;
use Prajwal89\EmailManagement\Controllers\Http\TrackEmailOpenedController;
use Prajwal89\EmailManagement\Controllers\Http\TrackEmailVisitController;
use Prajwal89\EmailManagement\Controllers\Http\UnsubscribeEmailController;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::prefix('emails')
    ->name('emails.')
    ->middleware(['web'])
    ->group(function (): void {
        Route::get('/redirect', TrackEmailVisitController::class)->name('redirect')->middleware('signed');
        Route::get('/pixel', TrackEmailOpenedController::class)->name('pixel');

        Route::get('/unsubscribe', UnsubscribeEmailController::class)->middleware('signed')->name('unsubscribe');

        // newsletter routes
        Route::controller(NewsletterController::class)
            ->prefix('newsletter')
            ->name('newsletter.')
            ->group(function (): void {
                Route::post('/subscribe', 'store')
                    ->name('subscribe')
                    ->middleware(['throttle:3,1', ProtectAgainstSpam::class]);

                Route::get('/confirm-subscription/{encrypted_email}', 'confirmSubscription')
                    ->name('confirm-subscription')
                    ->middleware('signed');

                Route::get('/unsubscribe/{encrypted_email}', 'unsubscribe')
                    ->name('unsubscribe')
                    ->middleware('signed');
            });
    });
