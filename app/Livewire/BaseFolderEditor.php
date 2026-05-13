<?php

namespace App\Livewire;

use App\Livewire\Traits\WithBackSection;
use App\Services\FolderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule as RuleAttribute;
use Livewire\Component;

/**
 * Базовый класс для редакторов папок
 */
abstract class BaseFolderEditor extends Component
{
    use WithBackSection;

    // Публичные свойства
    #[RuleAttribute('required|string|min:1|max:12')]
    public string $title = '';

    #[RuleAttribute('required|string|regex:/^#[A-Fa-f0-9]{3,6}$/')]
    public string $color = '';

    #[RuleAttribute('required|string')]
    public string $icon = '';

    // Защищённые от параллельных запросов
    #[Locked]
    public ?int $folderId = null;

    // Внедряемый сервис
    protected ?FolderService $folderService = null;

    /**
     * Инициализация сервиса
     */
    public function boot(FolderService $folderService): void
    {
        $this->folderService = $folderService;
    }

    /**
     * Получить список иконок
     */
    #[Computed]
    public function icons(): array
    {
        return $this->folderService->getIcons();
    }

    /**
     * Получить занятые иконки
     */
    #[Computed]
    public function usedIcons(): array
    {
        return $this->folderService->getUsedIcons(Auth::id(), $this->folderId);
    }

    /**
     * Получить правила валидации
     */
    protected function getValidationRules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:1',
                'max:12',
                Rule::unique('folders')
                    ->where('user_id', Auth::id())
                    ->whereNull('trash_id')
                    ->ignore($this->folderId),
            ],
            'color' => [
                'required',
                'string',
                'regex:/^#[A-Fa-f0-9]{3,6}$/',
            ],
            'icon' => [
                'required',
                'string',
                'in:' . implode(',', array_keys($this->folderService->getIcons())),
                Rule::unique('folders')
                    ->where('user_id', Auth::id())
                    ->whereNull('trash_id')
                    ->ignore($this->folderId),
            ],
        ];
    }

    /**
     * Получить сообщения об ошибках валидации
     */
    protected function getValidationMessages(): array
    {
        return [
            'title.required' => 'Название папки обязательно',
            'title.min' => 'Название должно содержать минимум 1 символ',
            'title.max' => 'Название не должно превышать 12 символов',
            'title.unique' => 'Папка с таким названием уже существует',
            'color.required' => 'Выберите цвет папки',
            'color.regex' => 'Цвет должен быть в формате HEX (например, #FF0000)',
            'icon.required' => 'Выберите иконку папки',
            'icon.in' => 'Выберите корректную иконку из списка',
            'icon.unique' => 'Эта иконка уже используется в другой папке',
        ];
    }

    /**
     * Абстрактный метод сохранения
     */
    abstract public function save(): void;
}