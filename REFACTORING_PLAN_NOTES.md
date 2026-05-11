# План рефакторинга Note Components

## 📋 Обзор

Анализ кода выявил значительные проблемы с дублированием, смешением ответственности и неполным использованием современных возможностей Livewire 4. Компоненты CreateNote и EditNote не следуют паттернам, уже реализованным в CreateChecklist и EditChecklist.

---

## 🔴 Критические проблемы

### 1. Дублирование кода между CreateNote и EditNote

**Дублирующиеся методы:**

- `normalizeContent()` - идентичная логика (строки 324-349 в CreateNote, 350-375 в EditNote)
- `updateTitle()`, `updateContent()`, `updateLocation()` - идентичная логика
- `getLocationName()` - идентичная логика (строки 410-428 в CreateNote, 434-452 в EditNote)
- `isTitleExists()` - идентичная логика (строки 433-445 в CreateNote, 457-469 в EditNote)
- `isSafeSelected()`, `isArchiveSelected()` - идентичная логика (строки 387-405 в CreateNote, 413-429 в EditNote)
- `extractImagePathsFromContent()`, `traverseContent()` - идентичная логика (строки 271-306 в CreateNote)
- `deleteImagesFromStorage()` - идентичная логика (строки 308-322 в CreateNote)
- `autoSave()` - ~80% дублирования

### 2. Прямые запросы к БД в компонентах

```php
// Плохо - в компоненте (CreateNote.php:413-427)
if ($note->folder_id !== null) {
    $folder = Folder::find($note->folder_id);
    return $folder?->title ?? 'Папка';
}
```

### 3. Хранение Eloquent моделей в приватных свойствах

```php
// Плохо - нарушает принципы Livewire (CreateNote.php:28, EditNote.php:27)
private ?Note $cachedNote = null;
```

### 4. Отсутствие использования атрибутов Livewire 4

**В EditNote.php используется старый синтаксис:**

```php
// Плохо - старый синтаксис (EditNote.php:30-40)
protected $listeners = [
    'updateFolderId' => 'setFolderId',
    'updateSafeId' => 'setSafeId',
    'updateArchiveId' => 'setArchiveId',
    'noteUpdated' => 'onNoteUpdated',
    'saveNote' => 'triggerSave',
    'editorContent' => 'setContent',
    'openNote' => 'openNote',
    'navigateTo' => 'handleNavigateTo',
    'noteLoaded' => 'handleNoteLoaded',
];
```

**Должно быть:**

```php
// Хорошо - атрибуты Livewire 4
#[On('updateFolderId')]
public function setFolderId(int $id): void { /* ... */ }
```

### 5. Логика работы с изображениями в компонентах

Методы для работы с изображениями дублируются в обоих компонентах:

- `extractImagePathsFromContent()` - парсинг JSON контента для извлечения путей изображений
- `traverseContent()` - рекурсивный обход контента
- `deleteImagesFromStorage()` - удаление файлов из хранилища

Эта логика должна быть в отдельном сервисе `ImageService`.

### 6. CreateNote и EditNote не используют BaseNoteEditor

Хотя `BaseNoteEditor` уже существует и содержит общую логику, компоненты Note не наследуются от него и дублируют эту логику.

### 7. Перегруженность модели Note

Модель содержит 620 строк с логикой, которая должна быть в сервисах:

- Парсинг контента (`extractTextFromContent`, `collectTextFromContent`, `extractTextFromNode`) - **УЖЕ ЕСТЬ в ContentService**
- Извлечение путей изображений (`getImagePaths`, `extractImagePaths`) - **НУЖНО в ImageService**
- Удаление изображений (`deleteImages`) - **НУЖНО в ImageService**
- Подсчёт прогресса чеклиста (`getChecklistProgress`, `countChecklistItems`) - **УЖЕ ЕСТЬ в ChecklistService**

---

## 🎯 План рефакторинга

### Этап 1: Создание ImageService

**Ответственность:** Работа с изображениями в контенте заметок

```php
namespace App\Services;

class ImageService
{
    /**
     * Извлечь пути к изображениям из контента
     */
    public function extractImagePaths(mixed $content): array
    {
        if (empty($content)) {
            return [];
        }

        $data = is_string($content)
            ? json_decode($content, true)
            : $content;

        if (!is_array($data) || !isset($data['content'])) {
            return [];
        }

        return $this->extractImagePathsFromNodes($data['content']);
    }

    /**
     * Рекурсивно извлечь пути изображений из узлов
     */
    private function extractImagePathsFromNodes(array $content): array
    {
        $paths = [];

        foreach ($content as $node) {
            if (!is_array($node)) {
                continue;
            }

            if (isset($node['type']) && $node['type'] === 'image') {
                if (isset($node['attrs']['path'])) {
                    $paths[] = $node['attrs']['path'];
                } elseif (isset($node['attrs']['src'])) {
                    $src = $node['attrs']['src'];
                    if (str_starts_with($src, '/storage/')) {
                        $paths[] = substr($src, strlen('/storage/'));
                    } elseif (str_starts_with($src, 'notes/')) {
                        $paths[] = $src;
                    }
                }
            }

            if (isset($node['content']) && is_array($node['content'])) {
                $paths = array_merge($paths, $this->extractImagePathsFromNodes($node['content']));
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * Удалить изображения из хранилища
     */
    public function deleteImagesFromStorage(array $paths): void
    {
        foreach ($paths as $path) {
            try {
                $cleanPath = str_replace('..', '', $path);

                if (str_starts_with($cleanPath, 'notes/') &&
                    \Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanPath);
                }
            } catch (\Exception $e) {
                report($e);
            }
        }
    }

    /**
     * Удалить все изображения заметки
     */
    public function deleteNoteImages(\App\Models\Note $note): void
    {
        $paths = $this->extractImagePaths($note->content);
        $this->deleteImagesFromStorage($paths);
    }
}
```

### Этап 2: Рефакторинг модели Note

**Вынести следующие методы в сервисы:**

**В ImageService:**

- `getImagePaths()` → `ImageService::extractImagePaths()`
- `extractImagePaths()` → `ImageService::extractImagePathsFromNodes()` (private)
- `deleteImages()` → `ImageService::deleteNoteImages()`

**Уже вынесено (проверить):**

- `extractTextFromContent()` → `ContentService::extractTextFromContent()`
- `collectTextFromContent()` → `ContentService::collectTextFromContent()` (private)
- `extractTextFromNode()` → `ContentService::extractTextFromNode()` (private)
- `collectInlineTextFromContent()` → `ContentService::collectInlineTextFromContent()` (private)
- `getChecklistProgress()` → `ChecklistService::getProgress()`
- `countChecklistItems()` → `ChecklistService::countItems()` (private)
- `getProgressColor()` → `ChecklistService::getProgressColor()`

**Оставить в модели Note:**

- Отношения (relationships)
- Скоупы (scopes)
- Методы перемещения (`moveToTrash`, `restoreFromTrash`, `moveToArchive`, `moveToSafe`, `moveToFolder`)
- Методы статуса (`isNote`, `isChecklist`, `isInTrash`, `isInArchive`, `isInSafe`, `isInFolder`, `isActive`, `isFavorite`)
- Accessors (`getLocation`, `getIcon`, `getColor`, `getPreview`, `getColorHex`, `getIconColorClass`, `getTypeIcon`)

### Этап 3: Рефакторинг BaseNoteEditor

**Текущий BaseNoteEditor уже хорошо структурирован, но нужно добавить:**

1. Методы для работы с изображениями (через ImageService)
2. Общие методы для автосохранения с учётом изображений

```php
namespace App\Livewire;

use App\Services\ImageService;
use App\Services\TemporaryImageService;

abstract class BaseNoteEditor extends Component
{
    // ... существующий код ...

    protected ImageService $imageService;
    protected TemporaryImageService $temporaryImageService;

    public function boot(
        NoteService $noteService,
        ContentService $contentService,
        LocationService $locationService,
        DropdownValueParser $dropdownParser,
        ImageService $imageService,
        TemporaryImageService $temporaryImageService,
    ): void {
        $this->noteService = $noteService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->dropdownParser = $dropdownParser;
        $this->imageService = $imageService;
        $this->temporaryImageService = $temporaryImageService;
    }

    /**
     * Извлечь пути изображений из контента
     */
    protected function extractImagePathsFromContent(mixed $content): array
    {
        return $this->imageService->extractImagePaths($content);
    }

    /**
     * Удалить изображения из хранилища
     */
    protected function deleteImagesFromStorage(array $paths): void
    {
        $this->imageService->deleteImagesFromStorage($paths);
    }

    /**
     * Выполнить отложенное удаление изображений
     */
    protected function executePendingImageDeletion(): void
    {
        $this->temporaryImageService->executePendingDeletion();
    }

    /**
     * Очистить временные изображения
     */
    protected function clearTemporaryImages(): void
    {
        $this->temporaryImageService->clear();
    }

    /**
     * Удалить несохранённые изображения
     */
    protected function deleteUnsavedImages(): void
    {
        $this->temporaryImageService->deleteUnsavedImages();
    }

    // ... существующий код ...
}
```

### Этап 4: Рефакторинг CreateNote

```php
namespace App\Livewire;

use App\Dto\CreateNoteDto;
use App\Dto\LocationDto;
use App\Dto\UpdateNoteDto;
use App\Services\StateManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class CreateNote extends BaseNoteEditor
{
    public string $heading = 'Создать заметку';
    public string $section = 'create-note';
    public bool $isFirstSave = true;

    private const EMPTY_NOTE_STRUCTURE = '{"type":"doc","content":[{"type":"paragraph"}]}';

    public function mount(): void
    {
        $this->content = self::EMPTY_NOTE_STRUCTURE;

        // Очищаем список временных изображений при входе на страницу создания заметки
        $this->clearTemporaryImages();

        // Обработка предустановок из StateManager
        $this->handlePresetFromStateManager();
    }

    /**
     * Обработка предустановок из StateManager
     */
    protected function handlePresetFromStateManager(): void
    {
        $presetIsFavorite = StateManager::get('preset_is_favorite', false);
        if ($presetIsFavorite) {
            $this->is_favorite = true;
            StateManager::remove('preset_is_favorite');
        }

        $presetSafeId = StateManager::get('preset_safe_id');
        if ($presetSafeId) {
            $this->safeId = $presetSafeId;
            $this->dropdownValue = 'safe_' . $presetSafeId;
            $this->originalFolderId = null;
            $this->originalSafeId = $presetSafeId;
            $this->originalArchiveId = null;
            StateManager::remove('preset_safe_id');
            return;
        }

        $presetArchiveId = StateManager::get('preset_archive_id');
        if ($presetArchiveId) {
            $this->archiveId = $presetArchiveId;
            $this->dropdownValue = 'archive_' . $presetArchiveId;
            $this->originalFolderId = null;
            $this->originalSafeId = null;
            $this->originalArchiveId = $presetArchiveId;
            StateManager::remove('preset_archive_id');
            return;
        }

        $presetFolderId = StateManager::get('preset_folder_id');
        if ($presetFolderId) {
            $this->folderId = $presetFolderId;
            $this->dropdownValue = (string) $presetFolderId;
            $this->originalFolderId = $presetFolderId;
            $this->originalSafeId = null;
            $this->originalArchiveId = null;
            StateManager::remove('preset_folder_id');
        }
    }

    /**
     * Отмена создания заметки
     */
    public function cancel(): void
    {
        // Если заметка была создана через автосохранение, удаляем её
        if ($this->noteId) {
            $note = $this->noteService->findNote(Auth::id(), $this->noteId);

            if ($note) {
                // Удаляем изображения из хранилища
                $imagePaths = $this->extractImagePathsFromContent($note->content);
                $this->deleteImagesFromStorage($imagePaths);
                // Удаляем заметку
                $note->delete();
            }
        }

        // Выполняем отложенное удаление изображений и очищаем временные
        $this->executePendingImageDeletion();
        $this->deleteUnsavedImages();

        $this->dispatch('navigateTo', section: 'dashboard-section');
        $this->dispatch('refreshSidebar');
    }

    /**
     * Обработка готового контента
     */
    #[On('noteContentReady')]
    public function handleContentReady($data): void
    {
        // Извлекаем контент из массива, если он обернут
        if (is_array($data) && isset($data['content'])) {
            $content = $data['content'];
        } else {
            $content = $data;
        }

        $this->content = $content;
        $this->autoSave();
    }

    /**
     * Автосохранение
     */
    #[Locked]
    public function autoSave(bool $locationChanged = false): void
    {
        // Проверка условий для сохранения
        if (!$this->canSave()) {
            return;
        }

        // Проверка уникальности названия
        if ($this->noteService->isTitleExists(Auth::id(), trim($this->title), $this->noteId)) {
            $this->dispatch('notification', [
                'title' => 'Внимание',
                'content' => 'Заметка с таким названием уже есть. Чтобы избежать путаницы измените название.',
                'type' => 'warning',
            ]);
        }

        if (!$this->validateAndSave()) {
            return;
        }

        $this->isSaving = true;

        try {
            if ($this->noteId) {
                $this->updateExistingNote($locationChanged);
            } else {
                $this->createNewNote();
            }
        } catch (\Throwable $e) {
            report($e);
        } finally {
            $this->isSaving = false;
            $this->dispatch('refreshSidebar');
        }
    }

    /**
     * Проверить, можно ли сохранить
     */
    protected function canSave(): bool
    {
        return ($this->folderId !== null || $this->safeId !== null || $this->archiveId !== null)
            && trim($this->title) !== '';
    }

    /**
     * Создать новую заметку
     */
    protected function createNewNote(): void
    {
        $dto = new CreateNoteDto(
            userId: Auth::id(),
            title: trim($this->title),
            content: $this->contentService->normalizeNoteContent($this->content),
            isFavorite: $this->is_favorite,
            location: new LocationDto(
                folderId: $this->folderId,
                safeId: $this->safeId,
                archiveId: $this->archiveId,
            ),
        );

        $note = $this->noteService->createNote($dto);

        $this->noteId = $note->id;
        $this->originalFolderId = $note->folder_id;
        $this->originalSafeId = $note->safe_id;
        $this->originalArchiveId = $note->archive_id;

        // Очищаем временные изображения при успешном сохранении
        $this->clearTemporaryImages();

        if ($this->isFirstSave) {
            $locationName = $this->locationService->getLocationName($note);
            $this->dispatch('notification', [
                'title' => 'Сохранено',
                'content' => "Заметка сохранена в «{$locationName}»",
                'type' => 'success',
            ]);
            $this->isFirstSave = false;
        }
    }

    /**
     * Обновить существующую заметку
     */
    protected function updateExistingNote(bool $locationChanged): void
    {
        // Выполняем отложенное удаление изображений
        $this->executePendingImageDeletion();

        $dto = new UpdateNoteDto(
            userId: Auth::id(),
            noteId: $this->noteId,
            title: trim($this->title),
            content: $this->content,
            isFavorite: $this->is_favorite,
            location: new LocationDto(
                folderId: $this->folderId,
                safeId: $this->safeId,
                archiveId: $this->archiveId,
            ),
        );

        $note = $this->noteService->updateNote($dto);

        if ($locationChanged) {
            $this->dispatchLocationChangedNotification($note);
            $this->originalFolderId = $note->folder_id;
            $this->originalSafeId = $note->safe_id;
            $this->originalArchiveId = $note->archive_id;
        }

        // Очищаем временные изображения при успешном обновлении
        $this->clearTemporaryImages();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.create-note');
    }
}
```

### Этап 5: Рефакторинг EditNote

```php
namespace App\Livewire;

use App\Dto\LocationDto;
use App\Dto\UpdateNoteDto;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class EditNote extends BaseNoteEditor
{
    public string $section = 'edit-note';
    public bool $confirmingDeletion = false;
    public bool $isLoaded = false;

    #[Locked]
    public ?int $noteId = null;

    public function mount(?int $noteId = null, ?int $folderId = null): void
    {
        $this->noteId = $noteId;
        $this->folderId = $folderId;

        if ($this->noteId) {
            $this->loadNote();
        }
    }

    /**
     * Загрузить заметку
     */
    public function loadNote(): void
    {
        if (!$this->noteId) {
            return;
        }

        $note = $this->noteService->findNote(Auth::id(), $this->noteId);

        if (!$note) {
            return;
        }

        $this->noteId = $note->id;
        $this->title = $note->title;
        $this->folderId = $note->folder_id;
        $this->safeId = $note->safe_id;
        $this->archiveId = $note->archive_id;
        $this->is_favorite = (bool) $note->is_favorite;
        $this->content = $note->content;
        $this->isLoaded = true;

        // Сохраняем оригинальное местоположение для отслеживания изменений
        $this->originalFolderId = $note->folder_id;
        $this->originalSafeId = $note->safe_id;
        $this->originalArchiveId = $note->archive_id;

        // Инициализируем dropdownValue
        $this->dropdownValue = $this->locationService->buildDropdownValue(
            $this->folderId,
            $this->safeId,
            $this->archiveId,
        );

        $this->dispatch('noteLoaded',
            content: $this->content,
            originalImagePaths: $this->extractImagePathsFromContent($note->content)
        );
    }

    /**
     * Получить заметку
     */
    #[Computed]
    public function note(): ?Note
    {
        return $this->noteId
            ? $this->noteService->findNote(Auth::id(), $this->noteId)
            : null;
    }

    /**
     * Отмена редактирования
     */
    public function cancel(): void
    {
        // Очищаем отложенные удаления изображений
        $this->temporaryImageService->clearPendingDeletion();

        $this->js('localStorage.clear()');
        $this->dispatch('restoreNoteOriginalState');
        $this->dispatch('navigateTo', section: 'dashboard-section');
    }

    /**
     * Подтвердить удаление
     */
    public function confirmDelete(): void
    {
        $result = $this->noteService->deleteNote(Auth::id(), $this->noteId);

        if (!$result['success']) {
            $this->dispatch('notification', [
                'title' => 'Ошибка',
                'content' => $result['message'],
                'type' => 'danger',
            ]);
            return;
        }

        $this->dispatch('notification', [
            'title' => 'Удалено',
            'content' => $result['message'],
            'type' => 'danger',
        ]);
        $this->dispatch('navigateTo', section: 'dashboard-section');
        $this->dispatch('refreshSidebar');
    }

    /**
     * Открыть модальное окно удаления
     */
    public function openDeleteModal(): void
    {
        $this->confirmingDeletion = true;
    }

    /**
     * Закрыть модальное окно
     */
    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->dispatch('modalClosed');
    }

    /**
     * Автосохранение
     */
    #[Locked]
    public function autoSave(bool $locationChanged = false): void
    {
        if (!$this->noteId) {
            return;
        }

        if ($this->noteService->isTitleExists(Auth::id(), trim($this->title), $this->noteId)) {
            $this->dispatch('notification', [
                'title' => 'Внимание',
                'content' => 'Заметка с таким названием уже есть. Чтобы избежать путаницы измените название.',
                'type' => 'warning',
            ]);
        }

        if (!$this->validateAndSave()) {
            return;
        }

        $this->isSaving = true;

        try {
            // Выполняем отложенное удаление изображений
            $this->executePendingImageDeletion();

            $dto = new UpdateNoteDto(
                userId: Auth::id(),
                noteId: $this->noteId,
                title: trim($this->title),
                content: $this->content,
                isFavorite: $this->is_favorite,
                location: new LocationDto(
                    folderId: $this->folderId,
                    safeId: $this->safeId,
                    archiveId: $this->archiveId,
                ),
            );

            $note = $this->noteService->updateNote($dto);

            if ($locationChanged) {
                $this->dispatchLocationChangedNotification($note);
                $this->originalFolderId = $note->folder_id;
                $this->originalSafeId = $note->safe_id;
                $this->originalArchiveId = $note->archive_id;
            }
        } catch (\Throwable $e) {
            report($e);
        } finally {
            $this->isSaving = false;
            $this->dispatch('refreshSidebar');
        }
    }

    /**
     * Обработать обновление заметки
     */
    #[On('noteUpdated')]
    public function onNoteUpdated(): void
    {
        $this->dispatch('navigateTo', section: 'dashboard-section');
    }

    /**
     * Обработать навигацию
     */
    #[On('navigateTo')]
    public function handleNavigateTo(string $section, ?int $folderId = null): void
    {
        if ($section === 'edit-note' && $folderId) {
            $this->openNote($folderId);
        }
    }

    /**
     * Открыть заметку
     */
    #[On('openNote')]
    public function openNote($noteId): void
    {
        $this->noteId = $noteId;
        $this->loadNote();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.edit-note');
    }
}
```

---

## 📊 Результаты рефакторинга

### До рефакторинга:

- CreateNote: ~461 строка
- EditNote: ~485 строк
- Note модель: ~620 строк
- Дублирование кода: ~70%

### После рефакторинга:

- CreateNote: ~200 строк
- EditNote: ~180 строк
- Note модель: ~350 строк
- ImageService: ~100 строк
- Дублирование кода: ~0%

### Выигрыши:

1. **Читаемость**: Компоненты стали чище и понятнее
2. **Тестируемость**: Бизнес-логика вынесена в сервисы, которые легко тестировать
3. **Переиспользование**: Сервисы можно использовать в других компонентах и контроллерах
4. **Поддерживаемость**: Изменения в логике требуют изменений только в одном месте
5. **Современность**: Использование атрибутов Livewire 4
6. **Единообразие**: Note компоненты следуют тем же паттернам, что и Checklist компоненты

---

## 🚀 Порядок выполнения

1. Создать ImageService
2. Рефакторинг модели Note (вынести методы в ImageService)
3. Обновить BaseNoteEditor (добавить методы для работы с изображениями)
4. Рефакторинг CreateNote
5. Рефакторинг EditNote
6. Обновить blade шаблоны (если необходимо)
7. Тестирование

---

## 📝 Дополнительные рекомендации

### 1. Использование Form Request для валидации

Для сложной валидации можно создать Form Request:

```php
class CreateNoteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|json',
        ];
    }
}
```

### 2. Использование Repository Pattern

Для более сложных проектов можно добавить Repository слой:

```php
interface NoteRepositoryInterface
{
    public function find(int $id): ?Note;
    public function create(array $data): Note;
    public function update(int $id, array $data): Note;
    public function delete(int $id): bool;
}
```

### 3. Кеширование

Добавить кеширование для часто запрашиваемых данных:

```php
class NoteService
{
    public function findNote(int $userId, int $noteId): ?Note
    {
        return Cache::remember(
            "note:{$userId}:{$noteId}",
            now()->addHour(),
            fn() => Note::where('user_id', $userId)
                ->where('type', Note::TYPE_NOTE)
                ->find($noteId)
        );
    }
}
```

### 4. Event-driven архитектура

Использовать события для декомпозиции:

```php
// Событие
class NoteCreated
{
    public function __construct(public Note $note) {}
}

// Listener
class UpdateSidebarOnNoteCreated
{
    public function handle(NoteCreated $event): void
    {
        // Обновить sidebar
    }
}
```

---

## ✅ Чеклист для code review

- [ ] Все прямые запросы к БД вынесены в сервисы
- [ ] Нет дублирования кода между компонентами
- [ ] Используются атрибуты Livewire 4 (`#[Rule]`, `#[Locked]`, `#[Computed]`, `#[On]`)
- [ ] Eloquent модели не хранятся в свойствах компонентов
- [ ] Бизнес-логика вынесена в сервисы
- [ ] Компоненты содержат только UI state и привязки
- [ ] Используются DTO для передачи данных
- [ ] Методы компонентов имеют чёткую ответственность
- [ ] Код покрыт типизацией (PHP 8.1+ features)
- [ ] Нет "магических" чисел и строк (используются константы)
- [ ] Логика работы с изображениями вынесена в ImageService
- [ ] CreateNote и EditNote следуют тем же паттернам, что и CreateChecklist и EditChecklist
