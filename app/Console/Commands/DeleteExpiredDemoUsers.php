<?php

namespace App\Console\Commands;

use App\Services\DemoUserService;
use Illuminate\Console\Command;

/**
 * Команда для удаления истёкших демо-пользователей.
 * Запускается через планировщик каждую минуту.
 * Также может быть запущена вручную: php artisan demo:delete-expired
 */
class DeleteExpiredDemoUsers extends Command
{
    protected $signature = 'demo:delete-expired';

    protected $description = 'Удалить истёкших демо-пользователей и все их данные';

    public function handle(DemoUserService $demoUserService): int
    {
        $count = $demoUserService->deleteExpiredDemoUsers();

        if ($count > 0) {
            $this->info("Удалено истёкших демо-пользователей: {$count}");
        } else {
            $this->info('Нет истёкших демо-пользователей для удаления.');
        }

        return self::SUCCESS;
    }
}