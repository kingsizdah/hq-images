<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\FilamentJalaliProvider;
use App\Providers\SettingsProvider;
use SocialiteProviders\Manager\ServiceProvider;

return [
    AppServiceProvider::class,
    SettingsProvider::class,
    AdminPanelProvider::class,
    FilamentJalaliProvider::class,
    ServiceProvider::class,
];
