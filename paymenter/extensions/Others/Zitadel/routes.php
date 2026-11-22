<?php

use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Others\Zitadel\Zitadel;

Route::get('/auth/zitadel/redirect', [Zitadel::class, 'redirect'])->name('zitadel.redirect');
Route::get('/auth/zitadel/callback', [Zitadel::class, 'callback'])->name('zitadel.callback');
Route::get('/auth/zitadel/logout', [Zitadel::class, 'logout'])->name('zitadel.logout');
