<?php

namespace App\Console\Commands;

use App\Services\TemporaryImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Команда для очистки пустых папок с изображениями заметок.
 * Удаляет папки по дате в storage/app/public/notes/images/, если они не содержат файлов.
 * Запускается через планировщик или вручную: php artisan images:cleanup-empty
 */
class CleanupEmptyImageFolders extends Command
{
    protected $signature = 'images:cleanup-empty
                            {--dry-run : Показать какие папки будут удалены без фактического удаления}';

    protected $description = 'Удалить пустые папки с изображениями заметок';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Режим dry-run: папки не будут удалены.');
        }

        $this->info('Запуск очистки пустых папок с изображениями...');

        $storagePath = 'notes/images';
        $totalDeleted = 0;

        // Получаем все папки в директории images
        $directories = Storage::disk('public')->directories($storagePath);

        if (empty($directories)) {
            $this->info('Нет папок с датами для проверки.');
            return self::SUCCESS;
        }

        foreach ($directories as $directory) {
            $files = Storage::disk('public')->files($directory);

            if (empty($files)) {
                // Папка пустая
                if ($dryRun) {
                    $this->line("  - Будет удалена пустая папка: {$directory}");
                } else {
                    Storage::disk('public')->deleteDirectory($directory);
                    $this->line("  - Удалена пустая папка: {$directory}");
                    $totalDeleted++;
                }
            } else {
                $this->line("  - Папка содержит файлы, пропускаем: {$directory} (" . count($files) . " файлов)");
            }
        }

        if (!$dryRun) {
            $this->info("Очистка завершена. Удалено пустых папок: {$totalDeleted}.");

            if ($totalDeleted > 0) {
                Log::info('Очистка пустых папок с изображениями завершена', [
                    'folders_deleted' => $totalDeleted,
                ]);
            }
        }

        return self::SUCCESS;
    }
}