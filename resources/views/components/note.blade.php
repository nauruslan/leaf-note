@props(['note'])

<p class="text-gray-700 h-[170px] overflow-hidden break-words">{!! nl2br(e($note->preview)) !!}</p>
