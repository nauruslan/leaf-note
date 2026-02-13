<svg {{ $attributes->merge(['class' => '']) }} width="100" height="100" viewBox="0 0 100 100"
    xmlns="http://www.w3.org/2000/svg">
    <defs>
        <!-- Градиент обложки - индиго/пурпурный -->
        <linearGradient id="bookGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color: #4F46E5; stop-opacity: 1" />
            <stop offset="100%" style="stop-color: #7C3AED; stop-opacity: 1" />
        </linearGradient>


        <!-- Градиент страниц -->
        <linearGradient id="pagesGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color: #ffffff; stop-opacity: 1" />
            <stop offset="100%" style="stop-color: #f8fafc; stop-opacity: 1" />
        </linearGradient>

        <!-- Тень -->
        <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
            <feDropShadow dx="0.5" dy="1" stdDeviation="1" flood-opacity="0.25" />
        </filter>
    </defs>

    <g filter="url(#shadow)">
        <!-- Обложка блокнота -->
        <rect x="15" y="10" width="70" height="80" rx="3" fill="url(#bookGradient)" stroke="#3730A3"
            stroke-width="1.5" />

        <!-- Акцентная линия на обложке -->
        <rect x="18" y="18" width="64" height="3" rx="1" fill="url(#accentGradient)" />

        <!-- Страницы блокнота -->
        <rect x="20" y="15" width="60" height="70" rx="1.5" fill="url(#pagesGradient)" stroke="#E2E8F0"
            stroke-width="0.5" />

        <!-- Линии на страницах -->
        <line x1="25" y1="30" x2="75" y2="30" stroke="#CBD5E1" stroke-width="0.8" />
        <line x1="25" y1="38" x2="75" y2="38" stroke="#CBD5E1" stroke-width="0.8" />
        <line x1="25" y1="46" x2="75" y2="46" stroke="#CBD5E1" stroke-width="0.8" />
        <line x1="25" y1="54" x2="70" y2="54" stroke="#CBD5E1" stroke-width="0.8" />
        <line x1="25" y1="62" x2="65" y2="62" stroke="#CBD5E1" stroke-width="0.8" />
        <line x1="25" y1="70" x2="80" y2="70" stroke="#CBD5E1" stroke-width="0.8" />
        <line x1="25" y1="78" x2="73" y2="78" stroke="#CBD5E1" stroke-width="0.8" />

        <!-- Лист, выходящий из блокнота -->
        <path d="M70,52 Q80,44 90,52 Q96,58 93,68 Q90,78 80,82 Q72,78 70,68 Q68,60 70,52 Z" fill="#A78BFA"
            stroke="#7C3AED" stroke-width="1.2" />

        <!-- Прожилки на листе -->
        <path d="M76,56 L82,62" stroke="#7C3AED" stroke-width="0.8" stroke-linecap="round" />
        <path d="M74,62 L80,67" stroke="#7C3AED" stroke-width="0.8" stroke-linecap="round" />
        <path d="M72,67 L77,71" stroke="#7C3AED" stroke-width="0.8" stroke-linecap="round" />
    </g>
</svg>
