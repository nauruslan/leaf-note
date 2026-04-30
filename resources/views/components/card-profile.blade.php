@props([
    'icon' => 'file-text',
    'title' => '',
    'content' => '',
    'accentColor' => '#4f46e5',
])

<div class="card-profile" style="--accent-color: {{ $accentColor }}">
    <div class="circle"></div>
    <div class="icon">
        <i data-lucide="{{ $icon }}"></i>
    </div>
    <div class="title">{{ $title }}</div>
    <div class="content">{{ $content }}</div>
</div>

<style>
    .card-profile {
        display: flex;
        position: relative;
        flex-direction: column;
        width: 100%;
        max-width: 400px;
        padding: 3rem 1rem 2rem 1rem;
        border-radius: 1rem;
        font-family: system-ui, -apple-system, sans-serif;
        color: #333;
        background-color: #eef1fd;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        transition: box-shadow 0.2s;
        overflow: hidden;
    }

    .card-profile:hover {
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }

    .card-profile .circle {
        position: absolute;
        top: -100px;
        left: -100px;
        width: 200px;
        height: 200px;
        background-color: var(--accent-color);
        border-radius: 50%;
    }

    .card-profile .icon {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 40px;
        aspect-ratio: 1;
        display: grid;
        place-items: center;
        color: #fff;
        background-color: transparent;
        border-radius: 50%;
    }

    .card-profile .icon i {
        width: 24px;
        height: 24px;
        stroke: #fff;
    }

    .card-profile .title {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--accent-color);
        text-align: center;
        text-transform: uppercase;
        margin-top: 1rem;
    }

    .card-profile .content {
        font-size: 2.5rem;
        font-weight: 700;
        text-align: center;
        color: #333;
    }
</style>
