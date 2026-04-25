<button type="button" wire:click="{{ $wireClick }}"
    {{ $disabled ? 'disabled' : '' }}
    class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
    {{ $text }}
</button>
