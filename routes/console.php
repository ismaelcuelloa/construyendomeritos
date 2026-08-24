<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Verificar órdenes pendientes automáticamente cada 5 minutos
Schedule::command('orders:verify-pending')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Marcar demos expirados cada hora
Schedule::command('demos:mark-expired')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
