<?php

namespace App\Providers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Support\ServiceProvider;

class FilamentJalaliProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Global: every date picker in the panel uses the Jalali calendar.
        // Table columns keep Gregorian dates unless explicitly changed later.
        DatePicker::configureUsing(fn (DatePicker $component) => $component->jalali());
        DateTimePicker::configureUsing(fn (DateTimePicker $component) => $component->jalali());
    }
}
