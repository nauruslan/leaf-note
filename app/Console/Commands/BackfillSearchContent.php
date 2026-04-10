<?php

namespace App\Console\Commands;

use App\Models\Note;
use Illuminate\Console\Command;

/**
 * Команда для обратного заполнения поля search_content у существующих заметок.
 * Извлекает текст из JSON-поля payload и сохраняет в search_content.
 */
class BackfillSearchContent extends Command
{

    protected $signature = 'notes:backfill-search-content';

    protected $description = 'Заполнить search_content для всех существующих заметок на основе payload';

    public function handle(): int
    {
        $this->info('Начинаем заполнение search_content...');

        $count = 0;
        $skipped = 0;

        Note::chunk(200, function ($notes) use (&$count, &$skipped) {
            foreach ($notes as $note) {
                $searchContent = $note->extractTextFromPayload();

                if ($note->search_content !== $searchContent) {
                    $note->search_content = $searchContent;
                    $note->saveQuietly();
                    $count++;
                } else {
                    $skipped++;
                }
            }
        });

        $this->info("Обработано: {$count} записей обновлено, {$skipped} пропущено.");
        $this->info('Готово!');

        return self::SUCCESS;
    }
}