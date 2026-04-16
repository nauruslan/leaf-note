<?php

namespace App\Console\Commands;

use App\Models\Trash;
use App\Models\Note;
use App\Models\Folder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Команда для автоматической очистки корзины.
 * Удаляет заметки и папки, которые находятся в корзине дольше указанного количества дней.
 * Запускается через планировщик или вручную: php artisan trash:auto-cleanup
 */
class AutoCleanupTrash extends Command
{
    protected $signature = 'trash:auto-cleanup
                            {--dry-run : Показать что будет удалено без фактического удаления}';

    protected $description = 'Автоматически удалить устаревшие элементы из корзины пользователей';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Режим dry-run: элементы не будут удалены.');
        }

        $this->info('Запуск автоматической очистки корзины...');

        // Получаем все корзины с включенной автоочисткой
        $trashes = Trash::whereNotNull('auto_delete_days')->get();

        if ($trashes->isEmpty()) {
            $this->info('Нет корзин с включенной автоочисткой.');
            return self::SUCCESS;
        }

        $totalNotesDeleted = 0;
        $totalFoldersDeleted = 0;

        foreach ($trashes as $trash) {
            // Проверяем, что корзина принадлежит существующему пользователю
            if (!$trash->user_id) {
                $this->warn("Корзина ID {$trash->id} не имеет пользователя, пропускаем.");
                continue;
            }

            $autoDeleteValue = $trash->auto_delete_days;

            // Проверяем, является ли значение тестовым (1min)
            if ($autoDeleteValue === '1min') {
                $cutoffDate = now()->subMinute();
                $this->info("Обработка корзины пользователя ID {$trash->user_id} (удаление старше 1 минуты, тестовый режим)");
            } else {
                $days = (int) $autoDeleteValue;
                if ($days <= 0) {
                    $this->warn("Некорректное значение auto_delete_days для пользователя ID {$trash->user_id}, пропускаем.");
                    continue;
                }
                $cutoffDate = now()->subDays($days);
                $this->info("Обработка корзины пользователя ID {$trash->user_id} (удаление старше {$days} дней)");
            }

            // Удаляем заметки без папки
            $notesQuery = Note::where('user_id', $trash->user_id)
                ->whereNotNull('trash_id')
                ->whereNull('folder_id')
                ->where('moved_to_trash_at', '<=', $cutoffDate);

            $notesCount = $notesQuery->count();

            if ($notesCount > 0) {
                if ($dryRun) {
                    $this->line("  - Будет удалено заметок: {$notesCount}");
                } else {
                    $notesQuery->delete();
                    $this->line("  - Удалено заметок: {$notesCount}");
                    $totalNotesDeleted += $notesCount;
                }
            }

            // Удаляем папки (их заметки удалятся через каскад или нужно удалить вручную)
            $foldersQuery = Folder::where('user_id', $trash->user_id)
                ->whereNotNull('trash_id')
                ->where('moved_to_trash_at', '<=', $cutoffDate);

            $foldersCount = $foldersQuery->count();

            if ($foldersCount > 0) {
                if ($dryRun) {
                    // Подсчитываем заметки в папках
                    $folderIds = (clone $foldersQuery)->pluck('id');
                    $notesInFolders = Note::whereIn('folder_id', $folderIds)
                        ->whereNotNull('trash_id')
                        ->count();
                    $this->line("  - Будет удалено папок: {$foldersCount} (с {$notesInFolders} заметками внутри)");
                } else {
                    // Получаем папки для удаления
                    $folders = $foldersQuery->get();

                    foreach ($folders as $folder) {
                        // Удаляем заметки внутри папки
                        $notesInFolder = Note::where('folder_id', $folder->id)
                            ->whereNotNull('trash_id')
                            ->delete();

                        // Удаляем саму папку
                        $folder->delete();

                        $totalNotesDeleted += $notesInFolder;
                    }

                    $this->line("  - Удалено папок: {$foldersCount}");
                    $totalFoldersDeleted += $foldersCount;
                }
            }

            // Обновляем счётчик корзины
            if (!$dryRun) {
                $actualCount = Note::where('user_id', $trash->user_id)
                    ->whereNotNull('trash_id')
                    ->count()
                    + Folder::where('user_id', $trash->user_id)
                    ->whereNotNull('trash_id')
                    ->count();
                $trash->current_quantity = $actualCount;
                $trash->save();
            }

            if ($notesCount === 0 && $foldersCount === 0) {
                $this->line('  - Нет устаревших элементов.');
            }
        }

        if (!$dryRun) {
            $this->info("Автоматическая очистка завершена.");
            $this->info("Всего удалено: {$totalNotesDeleted} заметок, {$totalFoldersDeleted} папок.");

            if ($totalNotesDeleted > 0 || $totalFoldersDeleted > 0) {
                Log::info('Автоматическая очистка корзины завершена', [
                    'notes_deleted' => $totalNotesDeleted,
                    'folders_deleted' => $totalFoldersDeleted,
                ]);
            }
        }

        return self::SUCCESS;
    }
}
