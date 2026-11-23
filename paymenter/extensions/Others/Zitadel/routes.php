<?php

use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Others\Zitadel\Zitadel;

// Routes registered outside routes/web.php do not inherit the web middleware
// group automatically. Without StartSession the login would never persist.
Route::middleware('web')->group(function () {
    Route::get('/auth/zitadel/redirect', [Zitadel::class, 'redirect'])->name('zitadel.redirect');
    Route::get('/auth/zitadel/callback', [Zitadel::class, 'callback'])->name('zitadel.callback');
    Route::get('/auth/zitadel/logout', [Zitadel::class, 'logout'])->name('zitadel.logout');
});
