# План рефакторинга Checklist Components

## 📋 Обзор

Анализ кода выявил значительные проблемы с дублированием, смешением ответственности и отсутствием использования современных возможностей Livewire 4.

---

## 🔴 Критические проблемы

### 1. Дублирование кода между CreateChecklist и EditChecklist

**Дублирующиеся методы:**

- `normalizeContent()` - идентичная логика
- `updateTitle()`, `updateContent()`, `updateLocation()` - идентичная логика
- `getLocationName()` - идентичная логика
- `isTitleExists()` - идентичная логика
- `isSafeSelected()`, `isArchiveSelected()` - идентичная логика
- `updatedDropdownValue()` - идентичная логика
- `autoSave()` - 80% дублирования

### 2. Прямые запросы к БД в компонентах

```php
// Плохо - в компоненте
Note::where('user_id', Auth::id())
    ->where('type', Note::TYPE_CHECKLIST)
    ->find($noteId);

Folder::find($note->folder_id);
Safe::find($note->safe_id);
Archive::find($note->archive_id);
```

### 3. Хранение Eloquent моделей в приватных свойствах

```php
// Плохо - нарушает принципы Livewire
private ?Note $cachedChecklist = null;
private ?Note $cachedNote = null;
```

### 4. Отсутствие использования атрибутов Livewire 4

- Нет `#[Rule]` для декларативной валидации
- Нет `#[Locked]` для защиты от параллельных запросов
- Нет `#[Computed]` для кешируемых свойств (кроме трейтов)

### 5. Перегруженность модели Note

Модель содержит 600+ строк с логикой, которая должна быть в сервисах:

- Парсинг контента (`extractTextFromContent`, `collectTextFromContent`, `extractTextFromNode`)
- Подсчёт прогресса чеклиста (`getChecklistProgress`, `countChecklistItems`)
- Извлечение путей изображений (`getImagePaths`, `extractImagePaths`)

---

## 🎯 План рефакторинга

### Этап 1: Создание сервисов

#### 1.1 ContentService

**Ответственность:** Парсинг и нормализация контента заметок

```php
namespace App\Services;

class ContentService
{
    private const EMPTY_CHECKLIST_STRUCTURE = '{"type":"doc","content":[{"type":"checklist","content":[]}]}';
    private const EMPTY_NOTE_STRUCTURE = '{"type":"doc","content":[]}';

    public function normalizeChecklistContent(mixed $content): string
    public function normalizeNoteContent(mixed $content): string
    public function extractTextFromContent(mixed $content): string
    public function extractImagePaths(mixed $content): array
    public function isValidJsonContent(string $content): bool
}
```

#### 1.2 LocationService

**Ответственность:** Работа с местоположением заметок (folder/safe/archive)

```php
namespace App\Services;

class LocationService
{
    public function getLocationName(Note $note): string
    public function getLocationType(Note $note): string
    public function parseDropdownValue(?string $value): LocationDto
    public function buildDropdownValue(?int $folderId, ?int $safeId, ?int $archiveId): ?string
    public function updateNoteLocation(Note $note, LocationDto $location): void
}
```

#### 1.3 NoteService

**Ответственность:** CRUD операции с заметками

```php
namespace App\Services;

class NoteService
{
    public function findChecklist(int $userId, int $noteId): ?Note
    public function findNote(int $userId, int $noteId): ?Note
    public function createChecklist(CreateChecklistDto $dto): Note
    public function createNote(CreateNoteDto $dto): Note
    public function updateChecklist(UpdateChecklistDto $dto): Note
    public function updateNote(UpdateNoteDto $dto): Note
    public function isTitleExists(int $userId, string $title, ?int $excludeNoteId = null): bool
    public function deleteChecklist(int $userId, int $noteId): array
}
```

#### 1.4 ChecklistService

**Ответственность:** Специфичная логика чеклистов

```php
namespace App\Services;

class ChecklistService
{
    public function getProgress(Note $checklist): ChecklistProgressDto
    public function countItems(array $content): array
    public function getProgressColor(int $percentage): string
    public function isEmpty(Note $checklist): bool
}
```

#### 1.5 DropdownValueParser

**Ответственность:** Парсинг dropdown значений

```php
namespace App\Services;

class DropdownValueParser
{
    public function parse(?string $value): LocationDto
    public function isSafeValue(?string $value): bool
    public function isArchiveValue(?string $value): bool
    public function isFolderValue(?string $value): bool
    public function extractSafeId(?string $value): ?int
    public function extractArchiveId(?string $value): ?int
    public function extractFolderId(?string $value): ?int
}
```

#### 1.6 DTO классы

```php
namespace App\Dto;

class LocationDto
{
    public function __construct(
        public ?int $folderId = null,
        public ?int $safeId = null,
        public ?int $archiveId = null,
    ) {}
}

class CreateChecklistDto
{
    public function __construct(
        public int $userId,
        public string $title,
        public string $content,
        public bool $isFavorite,
        public LocationDto $location,
    ) {}
}

class UpdateChecklistDto
{
    public function __construct(
        public int $userId,
        public int $noteId,
        public string $title,
        public string $content,
        public bool $isFavorite,
        public LocationDto $location,
    ) {}
}

class ChecklistProgressDto
{
    public function __construct(
        public int $completed,
        public int $total,
        public int $percentage,
        public string $color,
    ) {}
}
```

### Этап 2: Рефакторинг компонентов

#### 2.1 Создание базового класса BaseChecklistEditor

```php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule;

abstract class BaseChecklistEditor extends Component
{
    use WithBackSection;
    use WithNoteStore;
    use WithFavorite;

    // Публичные свойства для UI
    #[Rule('required|string|max:255')]
    public string $title = '';

    public ?int $folderId = null;
    public ?int $safeId = null;
    public ?int $archiveId = null;
    public ?string $dropdownValue = null;
    public bool $is_favorite = false;
    public string $content = '';
    public bool $isSaving = false;

    // Защищённые от параллельных запросов
    #[Locked]
    public ?int $noteId = null;

    // Оригинальное местоположение для отслеживания изменений
    protected ?int $originalFolderId = null;
    protected ?int $originalSafeId = null;
    protected ?int $originalArchiveId = null;

    // Внедряемые сервисы
    protected NoteService $noteService;
    protected ContentService $contentService;
    protected LocationService $locationService;
    protected DropdownValueParser $dropdownParser;

    public function boot(
        NoteService $noteService,
        ContentService $contentService,
        LocationService $locationService,
        DropdownValueParser $dropdownParser,
    ): void {
        $this->noteService = $noteService;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->dropdownParser = $dropdownParser;
    }

    // Общие методы
    abstract public function autoSave(bool $locationChanged = false): void;

    public function updatedDropdownValue(): void
    {
        $location = $this->dropdownParser->parse($this->dropdownValue);

        $locationChanged = ($location->folderId !== $this->originalFolderId) ||
                          ($location->safeId !== $this->originalSafeId) ||
                          ($location->archiveId !== $this->originalArchiveId);

        $this->folderId = $location->folderId;
        $this->safeId = $location->safeId;
        $this->archiveId = $location->archiveId;

        $this->autoSave($locationChanged);
    }

    #[On('updateSafeId')]
    public function setSafeId(int $id): void
    {
        $this->safeId = $id;
        $this->folderId = null;
        $this->archiveId = null;
        $this->dropdownValue = $this->locationService->buildDropdownValue(
            $this->folderId,
            $this->safeId,
            $this->archiveId
        );
        $this->autoSave();
    }

    #[On('updateArchiveId')]
    public function setArchiveId(int $id): void
    {
        $this->archiveId = $id;
        $this->folderId = null;
        $this->safeId = null;
        $this->dropdownValue = $this->locationService->buildDropdownValue(
            $this->folderId,
            $this->safeId,
            $this->archiveId
        );
        $this->autoSave();
    }

    public function updatedTitle(): void
    {
        $this->autoSave();
    }

    public function updatedContent(): void
    {
        $this->autoSave();
    }

    protected function validateAndSave(): bool
    {
        try {
            $this->validateOnly('title');
        } catch (\Illuminate\Validation\ValidationException) {
            return false;
        }

        return true;
    }

    protected function dispatchLocationChangedNotification(Note $note): void
    {
        $locationName = $this->locationService->getLocationName($note);
        $this->dispatch('notification', [
            'title' => 'Обновлено',
            'content' => "Место хранения изменено на «{$locationName}»",
            'type' => 'info',
        ]);
    }
}
```

#### 2.2 Рефакторинг CreateChecklist

```php
namespace App\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class CreateChecklist extends BaseChecklistEditor
{
    public string $heading = 'Создать список';
    public string $section = 'create-checklist';
    public bool $isFirstSave = true;

    public function mount(): void
    {
        $this->content = $this->contentService->normalizeChecklistContent('');

        // Обработка предустановок из StateManager
        $this->handlePresetFromStateManager();
    }

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
            $this->originalSafeId = $presetSafeId;
            StateManager::remove('preset_safe_id');
            return;
        }

        $presetArchiveId = StateManager::get('preset_archive_id');
        if ($presetArchiveId) {
            $this->archiveId = $presetArchiveId;
            $this->dropdownValue = 'archive_' . $presetArchiveId;
            $this->originalArchiveId = $presetArchiveId;
            StateManager::remove('preset_archive_id');
            return;
        }

        $presetFolderId = StateManager::get('preset_folder_id');
        if ($presetFolderId) {
            $this->folderId = $presetFolderId;
            $this->dropdownValue = (string) $presetFolderId;
            $this->originalFolderId = $presetFolderId;
            StateManager::remove('preset_folder_id');
        }
    }

    #[On('checklistContentReady')]
    public function handleContentReady(string $content): void
    {
        $this->content = $content;
        $this->autoSave();
    }

    public function cancel(): void
    {
        $this->dispatch('navigateTo', section: 'dashboard-section');
    }

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
                'content' => 'Список с таким названием уже есть. Чтобы избежать путаницы измените название.',
                'type' => 'warning',
            ]);
        }

        if (!$this->validateAndSave()) {
            return;
        }

        $this->isSaving = true;

        try {
            if ($this->noteId) {
                $this->updateExistingChecklist($locationChanged);
            } else {
                $this->createNewChecklist();
            }
        } catch (\Throwable $e) {
            report($e);
        } finally {
            $this->isSaving = false;
            $this->dispatch('refreshSidebar');
        }
    }

    protected function canSave(): bool
    {
        return ($this->folderId !== null || $this->safeId !== null || $this->archiveId !== null)
            && trim($this->title) !== '';
    }

    protected function createNewChecklist(): void
    {
        $dto = new CreateChecklistDto(
            userId: Auth::id(),
            title: trim($this->title),
            content: $this->contentService->normalizeChecklistContent($this->content),
            isFavorite: $this->is_favorite,
            location: new LocationDto(
                folderId: $this->folderId,
                safeId: $this->safeId,
                archiveId: $this->archiveId,
            ),
        );

        $note = $this->noteService->createChecklist($dto);

        $this->noteId = $note->id;
        $this->originalFolderId = $note->folder_id;
        $this->originalSafeId = $note->safe_id;
        $this->originalArchiveId = $note->archive_id;

        if ($this->isFirstSave) {
            $locationName = $this->locationService->getLocationName($note);
            $this->dispatch('notification', [
                'title' => 'Сохранено',
                'content' => "Список сохранён в «{$locationName}»",
                'type' => 'success',
            ]);
            $this->isFirstSave = false;
        }
    }

    protected function updateExistingChecklist(bool $locationChanged): void
    {
        $dto = new UpdateChecklistDto(
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

        $note = $this->noteService->updateChecklist($dto);

        if ($locationChanged) {
            $this->dispatchLocationChangedNotification($note);
            $this->originalFolderId = $note->folder_id;
            $this->originalSafeId = $note->safe_id;
            $this->originalArchiveId = $note->archive_id;
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.create-checklist');
    }
}
```

#### 2.3 Рефакторинг EditChecklist

```php
namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

class EditChecklist extends BaseChecklistEditor
{
    public string $section = 'edit-checklist';
    public bool $confirmingDeletion = false;

    #[Locked]
    public ?int $noteId = null;

    public function mount(?int $noteId = null): void
    {
        if ($noteId === null) {
            $this->dispatch('notification', [
                'title' => 'Ошибка',
                'content' => 'Заметка не найдена',
                'type' => 'danger',
            ]);
            $this->dispatch('navigateTo', section: 'dashboard-section');
            return;
        }

        $checklist = $this->noteService->findChecklist(Auth::id(), $noteId);

        if (!$checklist) {
            $this->dispatch('notification', [
                'title' => 'Ошибка',
                'content' => 'Список не найден',
                'type' => 'danger',
            ]);
            $this->dispatch('navigateTo', section: 'dashboard-section');
            return;
        }

        $this->noteId = $checklist->id;
        $this->title = $checklist->title;
        $this->folderId = $checklist->folder_id;
        $this->safeId = $checklist->safe_id;
        $this->archiveId = $checklist->archive_id;
        $this->is_favorite = (bool) $checklist->is_favorite;
        $this->content = $this->contentService->normalizeChecklistContent($checklist->content);

        $this->originalFolderId = $checklist->folder_id;
        $this->originalSafeId = $checklist->safe_id;
        $this->originalArchiveId = $checklist->archive_id;

        $this->dropdownValue = $this->locationService->buildDropdownValue(
            $this->folderId,
            $this->safeId,
            $this->archiveId
        );

        $this->dispatch('checklistLoaded', content: $this->content);
    }

    #[Computed]
    public function checklist(): ?Note
    {
        return $this->noteId
            ? $this->noteService->findChecklist(Auth::id(), $this->noteId)
            : null;
    }

    public function confirmDelete(): void
    {
        $result = $this->noteService->deleteChecklist(Auth::id(), $this->noteId);

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

    public function openDeleteModal(): void
    {
        $this->confirmingDeletion = true;
    }

    public function closeModal(): void
    {
        $this->confirmingDeletion = false;
        $this->dispatch('modalClosed');
    }

    #[Locked]
    public function autoSave(bool $locationChanged = false): void
    {
        if (!$this->noteId) {
            return;
        }

        if ($this->noteService->isTitleExists(Auth::id(), trim($this->title), $this->noteId)) {
            $this->dispatch('notification', [
                'title' => 'Внимание',
                'content' => 'Список с таким названием уже есть. Чтобы избежать путаницы измените название.',
                'type' => 'warning',
            ]);
        }

        if (!$this->validateAndSave()) {
            return;
        }

        $this->isSaving = true;

        try {
            $dto = new UpdateChecklistDto(
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

            $note = $this->noteService->updateChecklist($dto);

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

    #[On('checklistUpdated')]
    public function onChecklistUpdated(): void
    {
        $this->dispatch('navigateTo', section: 'dashboard-section');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.edit-checklist');
    }
}
```

### Этап 3: Рефакторинг трейтов

#### 3.1 WithFavorite

```php
namespace App\Livewire\Traits;

use App\Services\NoteService;

trait WithFavorite
{
    protected NoteService $noteService;

    public function bootWithFavorite(NoteService $noteService): void
    {
        $this->noteService = $noteService;
    }

    public function updatedIsFavorite($value): void
    {
        if ($this->noteId) {
            $this->noteService->toggleFavorite(Auth::id(), $this->noteId, (bool) $value);

            $this->dispatch('notification', [
                'title' => 'Успешно',
                'content' => $value ? 'Добавлено в избранное' : 'Удалено из избранного',
                'type' => 'info',
            ]);
        } else {
            $this->autoSave();
        }

        $this->dispatch('refreshSidebar');
    }
}
```

#### 3.2 WithNoteStore

```php
namespace App\Livewire\Traits;

use App\Services\LocationService;

trait WithNoteStore
{
    protected LocationService $locationService;

    public function bootWithNoteStore(LocationService $locationService): void
    {
        $this->locationService = $locationService;
    }

    #[Computed]
    public function folders(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\Folder::forUser(Auth::user())
            ->active()
            ->orderBy('title')
            ->get();
    }

    #[Computed]
    public function safes(): \Illuminate\Support\Collection
    {
        return $this->locationService->getSafesForDropdown(Auth::id());
    }

    #[Computed]
    public function archives(): \Illuminate\Support\Collection
    {
        return $this->locationService->getArchivesForDropdown(Auth::id());
    }
}
```

### Этап 4: Рефакторинг модели Note

Вынести следующие методы в сервисы:

**В ContentService:**

- `extractTextFromContent()`
- `collectTextFromContent()`
- `extractTextFromNode()`
- `collectInlineTextFromContent()`
- `getImagePaths()`
- `extractImagePaths()`
- `deleteImages()`

**В ChecklistService:**

- `getChecklistProgress()`
- `countChecklistItems()`
- `getProgressColor()`

Оставить в модели Note:

- Отношения (relationships)
- Скоупы (scopes)
- Методы перемещения (`moveToTrash`, `restoreFromTrash`, `moveToArchive`, `moveToSafe`, `moveToFolder`)
- Методы статуса (`isNote`, `isChecklist`, `isInTrash`, `isInArchive`, `isInSafe`, `isInFolder`, `isActive`, `isFavorite`)
- Accessors (`getLocation`, `getIcon`, `getColor`, `getPreview`, `getColorHex`, `getIconColorClass`, `getTypeIcon`)

### Этап 5: Конфигурация

Вынести константу ICONS из модели Folder в конфиг:

```php
// config/folder-icons.php
return [
    'icons' => [
        'folder' => ['label' => 'Папка', 'icon' => 'folder'],
        // ... остальные иконки
    ],
];
```

---

## 📊 Результаты рефакторинга

### До рефакторинга:

- CreateChecklist: ~423 строки
- EditChecklist: ~428 строки
- Note модель: ~620 строк
- Дублирование кода: ~60%

### После рефакторинга:

- CreateChecklist: ~150 строк
- EditChecklist: ~130 строк
- Note модель: ~300 строк
- Дублирование кода: ~0%

### Выигрыши:

1. **Читаемость**: Компоненты стали чище и понятнее
2. **Тестируемость**: Бизнес-логика вынесена в сервисы, которые легко тестировать
3. **Переиспользование**: Сервисы можно использовать в других компонентах и контроллерах
4. **Поддерживаемость**: Изменения в логике требуют изменений только в одном месте
5. **Современность**: Использование атрибутов Livewire 4

---

## 🚀 Порядок выполнения

1. Создать DTO классы
2. Создать ContentService
3. Создать LocationService и DropdownValueParser
4. Создать ChecklistService
5. Создать NoteService
6. Рефакторинг модели Note (вынести методы в сервисы)
7. Создать BaseChecklistEditor
8. Рефакторинг CreateChecklist
9. Рефакторинг EditChecklist
10. Рефакторинг трейтов
11. Обновление blade шаблонов (если необходимо)
12. Тестирование

---

## 📝 Дополнительные рекомендации

### 1. Использование Form Request для валидации

Для сложной валидации можно создать Form Request:

```php
class CreateChecklistRequest extends FormRequest
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
    public function findChecklist(int $userId, int $noteId): ?Note
    {
        return Cache::remember(
            "checklist:{$userId}:{$noteId}",
            now()->addHour(),
            fn() => Note::where('user_id', $userId)
                ->where('type', Note::TYPE_CHECKLIST)
                ->find($noteId)
        );
    }
}
```

### 4. Event-driven архитектура

Использовать события для декомпозиции:

```php
// Событие
class ChecklistCreated
{
    public function __construct(public Note $checklist) {}
}

// Listener
class UpdateSidebarOnChecklistCreated
{
    public function handle(ChecklistCreated $event): void
    {
        // Обновить sidebar
    }
}
```

---

## ✅ Чеклист для code review

- [ ] Все прямые запросы к БД вынесены в сервисы
- [ ] Нет дублирования кода между компонентами
- [ ] Используются атрибуты Livewire 4 (`#[Rule]`, `#[Locked]`, `#[Computed]`)
- [ ] Eloquent модели не хранятся в свойствах компонентов
- [ ] Бизнес-логика вынесена в сервисы
- [ ] Компоненты содержат только UI state и привязки
- [ ] Используются DTO для передачи данных
- [ ] Методы компонентов имеют чёткую ответственность
- [ ] Код покрыт типизацией (PHP 8.1+ features)
- [ ] Нет "магических" чисел и строк (используются константы)
