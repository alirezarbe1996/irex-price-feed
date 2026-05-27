<?php

use App\Models\Exchange;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


app(Schedule::class)->command('update-currencies')->dailyAt('03:00');


/****************** 24 samples a day for Daily Table ***********************/
app(Schedule::class)->command('app:arzplus', ['timeFrame' => 'daily'])->hourly()->when(function () {
    return Exchange::where('name', 'Arzplus')->exists();
});
app(Schedule::class)->command('app:mellichange', ['timeFrame' => 'daily'])->hourly()->when(function () {
    return Exchange::where('name', 'Mellichange')->exists();
});
app(Schedule::class)->command('app:iranicard', ['timeFrame' => 'daily'])->hourly()->when(function () {
    return Exchange::where('name', 'Iranicard')->exists();
});
app(Schedule::class)->command('app:nobitex', ['timeFrame' => 'daily'])->hourly()->when(function () {
    return Exchange::where('name', 'Nobitex')->exists();
});
app(Schedule::class)->command('app:exir', ['timeFrame' => 'daily'])->hourly()->when(function () {
    return Exchange::where('name', 'Exir')->exists();
});
app(Schedule::class)->command('app:ompfinex', ['timeFrame' => 'daily'])->hourly()->when(function () {
    return Exchange::where('name', 'Ompfinex')->exists();
});
app(Schedule::class)->command('app:efex', ['timeFrame' => 'daily'])->hourly()->when(function () {
    return Exchange::where('name', 'Efex')->exists();
});
app(Schedule::class)->command('app:pooleno', ['timeFrame' => 'daily'])->hourly()->when(function () {
    return Exchange::where('name', 'Pooleno')->exists();
});
app(Schedule::class)->command('app:ramzinex', ['timeFrame' => 'daily'])->hourly()->when(function () {
    return Exchange::where('name', 'Ramzinex')->exists();
});


/****************** 28 samples a Week for Weekly Table ***********************/
app(Schedule::class)->command('app:arzplus', ['timeFrame' => 'weekly'])
    ->when(function () {
        return Exchange::where('name', 'Arzplus')->exists();
    })
    ->days([1, 2, 3, 4, 5, 6, 7])
    ->cron('0 0,6,12,18 * * *');

app(Schedule::class)->command('app:mellichange', ['timeFrame' => 'weekly'])
    ->when(function () {
        return Exchange::where('name', 'Mellichange')->exists();
    })
    ->days([1, 2, 3, 4, 5, 6, 7])
    ->cron('0 0,6,12,18 * * *');

app(Schedule::class)->command('app:iranicard', ['timeFrame' => 'weekly'])
    ->when(function () {
        return Exchange::where('name', 'Iranicard')->exists();
    })
    ->days([1, 2, 3, 4, 5, 6, 7])
    ->cron('0 0,6,12,18 * * *');

app(Schedule::class)->command('app:nobitex', ['timeFrame' => 'weekly'])
    ->when(function () {
        return Exchange::where('name', 'Nobitex')->exists();
    })
    ->days([1, 2, 3, 4, 5, 6, 7])
    ->cron('0 0,6,12,18 * * *');

app(Schedule::class)->command('app:exir', ['timeFrame' => 'weekly'])
    ->when(function () {
        return Exchange::where('name', 'Exir')->exists();
    })
    ->days([1, 2, 3, 4, 5, 6, 7])
    ->cron('0 0,6,12,18 * * *');

app(Schedule::class)->command('app:ompfinex', ['timeFrame' => 'weekly'])
    ->when(function () {
        return Exchange::where('name', 'Ompfinex')->exists();
    })
    ->days([1, 2, 3, 4, 5, 6, 7])
    ->cron('0 0,6,12,18 * * *');

app(Schedule::class)->command('app:efex', ['timeFrame' => 'weekly'])
    ->when(function () {
        return Exchange::where('name', 'Efex')->exists();
    })
    ->days([1, 2, 3, 4, 5, 6, 7])
    ->cron('0 0,6,12,18 * * *');

app(Schedule::class)->command('app:pooleno', ['timeFrame' => 'weekly'])
    ->when(function () {
        return Exchange::where('name', 'Pooleno')->exists();
    })
    ->days([1, 2, 3, 4, 5, 6, 7])
    ->cron('0 0,6,12,18 * * *');

app(Schedule::class)->command('app:ramzinex', ['timeFrame' => 'weekly'])
    ->when(function () {
        return Exchange::where('name', 'Ramzinex')->exists();
    })
    ->days([1, 2, 3, 4, 5, 6, 7])
    ->cron('0 0,6,12,18 * * *');



/****************** 30 samples a Month for Monthly Table ***********************/
app(Schedule::class)->command('app:arzplus', ['timeFrame' => 'monthly'])
    ->when(function () {
        return Exchange::where('name', 'Arzplus')->exists();
    })
    ->dailyAt('02:00');

app(Schedule::class)->command('app:mellichange', ['timeFrame' => 'monthly'])
    ->when(function () {
        return Exchange::where('name', 'Mellichange')->exists();
    })
    ->dailyAt('02:00');

app(Schedule::class)->command('app:iranicard', ['timeFrame' => 'monthly'])
    ->when(function () {
        return Exchange::where('name', 'Iranicard')->exists();
    })
    ->dailyAt('02:00');

app(Schedule::class)->command('app:nobitex', ['timeFrame' => 'monthly'])
    ->when(function () {
        return Exchange::where('name', 'Nobitex')->exists();
    })
    ->dailyAt('02:00');

app(Schedule::class)->command('app:exir', ['timeFrame' => 'monthly'])
    ->when(function () {
        return Exchange::where('name', 'Exir')->exists();
    })
    ->dailyAt('02:00');

app(Schedule::class)->command('app:ompfinex', ['timeFrame' => 'monthly'])
    ->when(function () {
        return Exchange::where('name', 'Ompfinex')->exists();
    })
    ->dailyAt('02:00');

app(Schedule::class)->command('app:efex', ['timeFrame' => 'monthly'])
    ->when(function () {
        return Exchange::where('name', 'Efex')->exists();
    })
    ->dailyAt('02:00');

app(Schedule::class)->command('app:pooleno', ['timeFrame' => 'monthly'])
    ->when(function () {
        return Exchange::where('name', 'Pooleno')->exists();
    })
    ->dailyAt('02:00');

app(Schedule::class)->command('app:ramzinex', ['timeFrame' => 'monthly'])
    ->when(function () {
        return Exchange::where('name', 'Ramzinex')->exists();
    })
    ->dailyAt('02:00');
