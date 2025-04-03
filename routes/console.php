<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:uptage')
    // ->at()
    ->timezone('America/Guatemala')
    ->everyMinute();
Schedule::command('app:atrasos')
    // ->at()
    ->timezone('America/Guatemala')
    ->everyMinute();
