<?php

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use Paymenter\Extensions\Gateways\Zibal\Zibal;

Route::get('/extensions/gateways/zibal/callback/{invoice}', [Zibal::class, 'callback'])
    ->withoutMiddleware([PreventRequestForgery::class])
    ->name('extensions.gateways.zibal.callback');
