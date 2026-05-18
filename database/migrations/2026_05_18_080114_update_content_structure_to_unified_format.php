<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Обновляем существующие записи в базе данных для соответствия новой структуре
        DB::table('notes')->whereNotNull('content')->orderBy('id')->chunk(100, function ($notes) {
            foreach ($notes as $note) {
                $content = $note->content;

                // Если контент это строка, декодируем её
                if (is_string($content)) {
                    try {
                        $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\JsonException $e) {
                        // Если не удалось декодировать, пропускаем
                        continue;
                    }
                }

                // Если контент это массив, обновляем его структуру
                if (is_array($content) && isset($content['type']) && $content['type'] === 'doc') {
                    // Для заметок типа note
                    if ($note->type === 'note') {
                        // Проверяем, есть ли уже обертка note
                        $hasNoteWrapper = false;
                        if (isset($content['content']) && is_array($content['content'])) {
                            foreach ($content['content'] as $node) {
                                if (isset($node['type']) && $node['type'] === 'note') {
                                    $hasNoteWrapper = true;
                                    break;
                                }
                            }
                        }

                        // Если нет обертки note, добавляем её
                        if (!$hasNoteWrapper) {
                            $content = [
                                'type' => 'doc',
                                'content' => [
                                    [
                                        'type' => 'note',
                                        'content' => $content['content'] ?? []
                                    ]
                                ]
                            ];
                        }
                    }
                    // Для заметок типа checklist
                    elseif ($note->type === 'checklist') {
                        // Проверяем, есть ли уже обертка checklist
                        $hasChecklistWrapper = false;
                        if (isset($content['content']) && is_array($content['content'])) {
                            foreach ($content['content'] as $node) {
                                if (isset($node['type']) && $node['type'] === 'checklist') {
                                    $hasChecklistWrapper = true;
                                    break;
                                }
                            }
                        }

                        // Если нет обертки checklist, добавляем её
                        if (!$hasChecklistWrapper) {
                            $content = [
                                'type' => 'doc',
                                'content' => [
                                    [
                                        'type' => 'checklist',
                                        'content' => $content['content'] ?? []
                                    ]
                                ]
                            ];
                        }
                    }

                    // Обновляем запись в базе данных
                    DB::table('notes')
                        ->where('id', $note->id)
                        ->update(['content' => $content]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Откатываем изменения, удаляя обертки note и checklist
        DB::table('notes')->whereNotNull('content')->orderBy('id')->chunk(100, function ($notes) {
            foreach ($notes as $note) {
                $content = $note->content;

                // Если контент это строка, декодируем её
                if (is_string($content)) {
                    try {
                        $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\JsonException $e) {
                        // Если не удалось декодировать, пропускаем
                        continue;
                    }
                }

                // Если контент это массив, удаляем обертки
                if (is_array($content) && isset($content['type']) && $content['type'] === 'doc') {
                    if (isset($content['content']) && is_array($content['content'])) {
                        // Для заметок типа note
                        if ($note->type === 'note') {
                            foreach ($content['content'] as $node) {
                                if (isset($node['type']) && $node['type'] === 'note' && isset($node['content'])) {
                                    // Заменяем обертку note на её содержимое
                                    $content = [
                                        'type' => 'doc',
                                        'content' => $node['content']
                                    ];
                                    break;
                                }
                            }
                        }
                        // Для заметок типа checklist
                        elseif ($note->type === 'checklist') {
                            foreach ($content['content'] as $node) {
                                if (isset($node['type']) && $node['type'] === 'checklist' && isset($node['content'])) {
                                    // Заменяем обертку checklist на её содержимое
                                    $content = [
                                        'type' => 'doc',
                                        'content' => $node['content']
                                    ];
                                    break;
                                }
                            }
                        }

                        // Обновляем запись в базе данных
                        DB::table('notes')
                            ->where('id', $note->id)
                            ->update(['content' => $content]);
                    }
                }
            }
        });
    }
};