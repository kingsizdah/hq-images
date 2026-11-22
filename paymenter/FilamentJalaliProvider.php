<?php

namespace App\Providers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
use Illuminate\Support\ServiceProvider;

class FilamentJalaliProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Global: date pickers use the Jalali calendar.
        // Table columns keep Gregorian dates unless explicitly changed later.
        DatePicker::configureUsing(fn (DatePicker $component) => $component->jalali());

        // TimePicker extends DateTimePicker, so it inherits the callback above.
        // Giving a time-only field the date-time view breaks the settings form.
        DateTimePicker::configureUsing(function (DateTimePicker $component) {
            if ($component instanceof TimePicker) {
                return;
            }

            $component->jalali();
        });
    }
}
