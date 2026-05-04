@props([
    'icon' => 'file-text',
    'title' => '',
    'content' => '',
])

<div
    class="relative flex flex-col w-full max-w-[400px] p-[3rem_1rem_2rem_1rem] rounded-xl font-system-ui text-[#333] bg-[#eef1fd] shadow-[0_4px_6px_-1px_rgb(0_0_0/0.1),0_2px_4px_-2px_rgb(0_0_0/0.1)] transition-shadow duration-200 overflow-hidden hover:shadow-[0_10px_15px_-3px_rgb(0_0_0/0.1),0_4px_6px_-4px_rgb(0_0_0/0.1)]">
    <div
        class="absolute top-[-100px] left-[-100px] w-[200px] h-[200px] rounded-full bg-gradient-to-r from-indigo-600 to-purple-600">
    </div>
    <div
        class="absolute top-[10px] left-[10px] w-[40px] aspect-square grid place-items-center text-white bg-transparent rounded-full">
        <i data-lucide="{{ $icon }}" class="w-6 h-6 stroke-white"></i>
    </div>
    <div
        class="text-xl font-bold text-center uppercase mt-4 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
        {{ $title }}</div>
    <div class="text-[2.5rem] font-bold text-center text-[#333]">{{ $content }}</div>
</div>
