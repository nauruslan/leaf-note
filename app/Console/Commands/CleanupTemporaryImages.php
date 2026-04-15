<?php

namespace App\Console\Commands;

use App\Services\TemporaryImageService;
use Illuminate\Console\Command;

/**
 * Команда для очистки старых временных изображений (garbage collection).
 * Удаляет изображения старше указанного количества часов, которые не привязаны к заметкам.
 * Запускается через планировщик или вручную: php artisan images:cleanup-temporary
 */
class CleanupTemporaryImages extends Command
{
    protected $signature = 'images:cleanup-temporary
                            {--hours=24 : Удалить изображения старше указанного количества часов}
                            {--force : Принудительно удалить все временные изображения}';

    protected $description = 'Удалить старые временные изображения, не привязанные к заметкам';

    public function handle(TemporaryImageService $temporaryImageService): int
    {
        $hours = (int) $this->option('hours');
        $force = $this->option('force');

        $this->info('Запуск очистки временных изображений...');

        if ($force) {
            $this->warn('Режим принудительной очистки: будут удалены все временные изображения из сессии.');
            $temporaryImageService->deleteAll();
            $this->info('Все временные изображения удалены из сессии.');
        }

        $deletedCount = $temporaryImageService->garbageCollect($hours);

        if ($deletedCount > 0) {
            $this->info("Удалено старых временных изображений: {$deletedCount}");
        } else {
            $this->info('Нет старых временных изображений для удаления.');
        }

        return self::SUCCESS;
    }
}
