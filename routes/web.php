<?php

use App\Livewire\SignDocument;
use Illuminate\Support\Facades\Route;
use App\Livewire\SubscriptionManager;
use App\Http\Controllers\WebhookController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;


// Route::get('/sign/document', SignDocument::class)->name('sign.document');
Route::middleware(['auth'])->group(function () {
    // Route::get('/subscription', SubscriptionManager::class)->name('subscription.show');

    Route::get('/subscription/success', function () {
        return redirect()->route('filament.app.pages.billing')
            ->with('message', 'Subscription created successfully!');
    })->name('subscription.success');

    Route::get('/subscription/cancel', function () {
        return redirect()->route('filament.app.pages.billing')
            ->with('message', 'Subscription cancelled.');
    })->name('subscription.cancel');
});

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/app');
})->middleware(['auth', 'signed'])->name('verification.verify');


Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/', function () {
    return view('welcome');
});
