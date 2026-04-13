<?php

use App\Console\Commands\DeleteExpiredDemoUsers;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Планировщик: удаление истёкших демо-пользователей каждую минуту
Schedule::command(DeleteExpiredDemoUsers::class)->everyMinute();
