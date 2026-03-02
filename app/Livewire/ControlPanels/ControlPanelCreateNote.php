<?php

namespace App\Livewire\ControlPanels;

use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ControlPanelCreateNote extends Component
{
    public const COLORS = [
        'black'   => ['label' => 'Черный',   'bg' => 'bg-black',        'border' => 'border-gray-700', 'ring' => 'focus:ring-gray-700',  'hex' => '#000000'],
        'gray'    => ['label' => 'Серый',    'bg' => 'bg-gray-500',     'border' => 'border-gray-600', 'ring' => 'focus:ring-gray-600',  'hex' => '#6b7280'],
        'red'     => ['label' => 'Красный',  'bg' => 'bg-red-500',      'border' => 'border-red-600',  'ring' => 'focus:ring-red-600',   'hex' => '#ef4444'],
        'orange'  => ['label' => 'Оранжевый','bg' => 'bg-orange-500',   'border' => 'border-orange-600','ring' => 'focus:ring-orange-600','hex' => '#f97316'],
        'yellow'  => ['label' => 'Желтый',   'bg' => 'bg-yellow-500',   'border' => 'border-yellow-600','ring' => 'focus:ring-yellow-600','hex' => '#eab308'],
        'green'   => ['label' => 'Зеленый',  'bg' => 'bg-green-500',    'border' => 'border-green-600', 'ring' => 'focus:ring-green-600', 'hex' => '#22c55e'],
        'blue'    => ['label' => 'Синий',    'bg' => 'bg-blue-500',     'border' => 'border-blue-600',  'ring' => 'focus:ring-blue-600',  'hex' => '#3b82f6'],
        'indigo'  => ['label' => 'Индиго',   'bg' => 'bg-indigo-500',   'border' => 'border-indigo-600', 'ring' => 'focus:ring-indigo-600','hex' => '#6366f1'],
        'purple'  => ['label' => 'Фиолетовый','bg' => 'bg-purple-500',  'border' => 'border-purple-600', 'ring' => 'focus:ring-purple-600','hex' => '#8b5cf6'],
        'pink'    => ['label' => 'Розовый',  'bg' => 'bg-pink-500',     'border' => 'border-pink-600',  'ring' => 'focus:ring-pink-600',  'hex' => '#ec4899'],
        'white'   => ['label' => 'Белый',    'bg' => 'bg-white',        'border' => 'border-gray-300', 'ring' => 'focus:ring-gray-400',  'hex' => '#ffffff'],
    ];

    public $folderId = null;
    public $color = 'white';
    public $folders = [];

    protected $listeners = [
        'updateFolderId' => 'setFolderId',
        'noteSaved' => 'onNoteSaved',
    ];

    public function mount()
    {
        $this->folders = Folder::forUser(Auth::user())
            ->active()
            ->orderBy('title')
            ->get();
    }

    public function setFolderId($id)
    {
        $this->folderId = $id;
    }

    public function save()
    {
        $this->dispatch('saveNote',
            folderId: $this->folderId,
            color: $this->color
        );
    }

    public function onNoteSaved()
    {
        $this->dispatch('navigateTo', 'dashboard');
    }

    public function cancel()
    {
        $this->dispatch('navigateTo', 'archive');
    }

    public function getColorsProperty()
    {
        return self::COLORS;
    }

    public function render()
    {
        return view('livewire.control-panels.create-note');
    }
}