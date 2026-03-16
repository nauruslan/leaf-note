<div>
    <!-- Header HeaderPage -->
    <header class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 ">
        <div class="bg-white rounded-b-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        {{ $folder->title }}
                    </h1>
                    <div class="flex items-center gap-3 mt-1">
                        <button class="text-gray-500 hover:text-indigo-600 focus:outline-none" title="Редактировать папку"
                            wire:click="openEditFolder({{ $folder->id }})">
                            Редактировать
                        </button>|
                        <button class="text-gray-500 hover:text-red-600 focus:outline-none" title="Удалить папку"
                            wire:click="confirmDeletion">Удалить
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" placeholder="Поиск..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64 transition-all">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ControlPanel -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left Block: Create Buttons -->
                <div class="flex flex-wrap items-center gap-3">
                    <button
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium py-2.5 px-5 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Новая заметка
                    </button>
                    <button
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                        <i data-lucide="list" class="w-4 h-4"></i>
                        Новый список
                    </button>
                </div>

                <!-- Right Block: Filters -->
                <div class="flex flex-wrap items-center gap-4 justify-end">
                    <!-- Фильтр Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Фильтр:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[100px]">
                                <option>Все</option>
                                <option>Заметки</option>
                                <option>Списки</option>
                                <option>Важные</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Сортировка Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">Сортировка:</span>
                        <div class="relative">
                            <select
                                class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm min-w-[140px]">
                                <option>По дате изменения</option>
                                <option>По дате создания</option>
                                <option>По названию</option>
                                <option>По приоритету</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content ContentPage -->
    <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 mb-6 flex flex-wrap gap-5">
        <!-- Card 1: Рабочие задачи (с прогрессом) -->
        <div
            class="min-w-[320px] basis-[320px] h-[340px] flex grow flex-col p-4 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all">
            <div class="flex items-start justify-between mb-6">
                <div class="w-8 flex-shrink-0 self-stretch">
                    <!-- Иконка папки (оставлена как есть, так как это кастомный SVG) -->
                    <svg class="fill-black-500" height="45px" width="32px" version="1.1" id="Layer_1"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="0 0 512.001 512.001" xml:space="preserve">
                        <!-- SVG content remains unchanged -->
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <!-- Full SVG path data remains here -->
                            <g>
                                <g>
                                    <path
                                        d="M412.253,269.541c-5.722-5.72-11.812-8.898-18.233-9.534c16.285-26.353,18.927-48.994,6.323-61.598 c-5.663-5.662-11.715-8.668-18.122-9.019c10.358-20.115,10.877-36.979,0.832-47.024c-5.809-5.808-10.614-10.233-16.146-11.618 c0.201-0.76,0.375-1.515,0.523-2.265c1.875-9.616-0.91-18.473-8.056-25.62c-4.799-4.798-10.053-7.205-15.727-7.085 c-0.756,0.013-1.505,0.07-2.246,0.168c3.884-10.339,4.371-20.223-2.467-27.06c-4.032-4.031-8.506-6.073-13.304-6.073 c-0.083,0-0.168,0.001-0.253,0.002c-4.961,0.074-9.396,2.283-13.718,5.677c-12.087-38.205-49.414-65.752-51.178-67.035 c-2.67-1.944-6.288-1.944-8.958,0c-1.738,1.266-38.583,28.456-50.924,66.269c-4.065-3.102-8.369-5.176-13.199-5.265 c-5.111-0.077-9.887,2.068-14.245,6.426c-2.601,2.601-5.676,7.295-5.552,14.879c0.061,3.679,0.849,7.579,2.199,11.58 c-0.145-0.006-0.29-0.011-0.436-0.015c-5.88-0.094-11.495,2.4-16.629,7.532c-7.131,7.132-8.31,17.802-3.758,30.668 c-6.176-0.327-12.952,1.892-19.897,8.837c-10.094,10.095-9.853,26.559,0.245,46.478c-6.093,0.805-11.946,3.995-17.516,9.565 c-12.688,12.689-10.403,34.877,5.667,61.112c-6.095,1.078-11.962,4.419-17.563,10.019c-7.314,7.315-13.753,21.539-2.892,47.762 c10.443,25.21,33.817,53.819,62.527,76.527c25.235,19.961,51.545,32.952,75.975,37.765l-4.653,72.305 c-0.135,2.099,0.605,4.161,2.044,5.694c1.439,1.535,3.447,2.405,5.552,2.405h30.014c2.087,0,4.081-0.857,5.519-2.37 c1.437-1.512,2.19-3.549,2.083-5.633l-3.689-71.656c25.607-4.102,53.502-17.493,80.188-38.735 c28.613-22.773,51.937-51.441,62.392-76.682C425.786,290.825,419.463,276.752,412.253,269.541z M246.584,496.776l4.07-63.24 c1.825,0.103,3.636,0.156,5.429,0.156c0.377,0,0.758-0.012,1.137-0.017l3.249,63.101H246.584z M400.911,311.099 c-9.37,22.623-31.522,49.674-57.808,70.596c-26.424,21.032-53.717,33.674-77.917,36.267c-0.252-0.025-0.506-0.039-0.763-0.039 h-17.846c-24.117-2.674-51.279-15.229-77.583-36.034c-26.366-20.855-48.555-47.836-57.907-70.413 c-3.454-8.34-8.1-23.479-0.407-31.171c4.258-4.259,7.87-5.933,11.338-5.933c2.887,0,5.675,1.16,8.652,2.952 c3.14,1.89,7.183,1.273,9.618-1.467c2.434-2.741,2.568-6.829,0.318-9.723c-19.613-25.24-26.859-48.131-18.03-56.961 c6.379-6.381,11.305-6.888,18.815-1.94c3.06,2.015,7.116,1.567,9.66-1.067c2.545-2.633,2.855-6.703,0.738-9.691 c-13.717-19.352-18.515-36.772-11.943-43.345c5.309-5.307,9.772-7.845,22.919,4.728c2.849,2.724,7.307,2.821,10.272,0.227 c2.967-2.596,3.461-7.027,1.139-10.212c-11.151-15.298-15.54-29.381-10.675-34.247c3.131-3.13,4.924-3.093,5.519-3.077 c5.068,0.115,13.075,8.888,20.82,17.371c0.064,0.07,0.129,0.141,0.193,0.212c4.214,4.934,8.619,9.505,12.831,13.45 c0.012,0.011,0.023,0.021,0.036,0.032c2.078,2.009,4.241,3.995,6.49,5.906c3.191,2.712,7.968,2.339,10.696-0.83 c2.731-3.169,2.391-7.949-0.76-10.702c-2.134-1.863-3.004-2.646-6.041-5.506c-0.005-0.005-0.009-0.009-0.015-0.014 c-4.056-3.937-7.845-8.058-11.542-12.107c-11.178-13.129-18.768-26.734-18.902-34.85c-0.045-2.725,0.814-3.582,1.096-3.865 c0.739-0.74,2.127-1.971,3.174-1.971c0.008,0,0.016,0,0.023,0.001c3.152,0.059,9.748,7.162,12.231,9.835 c1.992,2.145,5.035,2.959,7.837,2.091c2.797-0.869,4.846-3.265,5.271-6.162c4.519-30.77,32.496-56.87,43.546-66.107 c11.152,9.321,39.526,35.798,43.665,66.916c0.388,2.911,2.415,5.34,5.211,6.24c2.798,0.901,5.86,0.113,7.874-2.026 c2.928-3.108,9.784-10.386,12.85-10.432c0.005,0,0.01,0,0.015,0c0.814,0,1.945,1.01,2.551,1.616 c1.715,1.715,1.079,8.211-4.37,18.422c-5.511,10.325-14.891,22.656-25.757,33.866c-1.31,1.207-2.645,2.391-4.006,3.538 c-0.481,0.393-1.026,0.84-1.69,1.395c-3.205,2.682-3.649,7.447-0.994,10.675c2.657,3.227,7.42,3.707,10.666,1.08 c0.591-0.478,1.173-0.959,1.752-1.446c0.047-0.038,0.092-0.074,0.138-0.112c1.452-1.186,2.505-2.047,4.67-4.135 c5.397-4.989,10.254-10.192,14.686-14.939c7.763-8.315,15.79-16.914,20.644-16.997c0.505-0.016,2.036-0.037,4.701,2.629 c3.055,3.054,11.166,11.162-9.798,34.028c-0.006,0.007-0.011,0.012-0.017,0.019c-2.842,3.098-2.634,7.913,0.465,10.754 c3.097,2.841,7.912,2.634,10.754-0.465c4.101-4.471,10.942-11.931,13.19-12.44c1.246,0.263,4.302,2.821,9.087,7.607 c6.918,6.917,0.748,26.276-15.003,47.074c-2.324,3.068-1.992,7.387,0.773,10.064c2.765,2.676,7.093,2.869,10.085,0.447 c10.735-8.692,15.641-7.337,21.437-1.543c9.067,9.066-0.026,34.713-21.619,60.984c-2.46,2.993-2.279,7.356,0.419,10.135 c2.7,2.78,7.054,3.087,10.118,0.719c11.231-8.686,16.746-6.952,22.99-0.706C408.981,287.797,404.343,302.815,400.911,311.099z">
                                    </path>
                                </g>
                            </g>
                            <g>
                                <g>
                                    <path
                                        d="M349.013,299.526c-3.051-2.897-7.866-2.776-10.764,0.27c-19.568,20.574-42.996,42.432-75.352,45.164v-62.037 c31.671-2.436,54.837-21.488,71.729-38.232c2.985-2.959,3.006-7.779,0.047-10.763c-2.961-2.987-7.778-3.007-10.763-0.047 c-15.661,15.525-35.021,31.359-61.011,33.762V217.91c27.382-2.373,47.35-18.66,61.927-32.999 c2.996-2.947,3.036-7.767,0.088-10.764c-2.949-2.998-7.768-3.035-10.763-0.088c-12.438,12.236-29.237,26.111-51.252,28.555V71.27 c0-4.205-3.407-7.611-7.611-7.611c-4.205,0-7.611,3.407-7.611,7.611v131.144c-20.829-2.858-36.469-16.007-49.204-28.911 c-2.952-2.991-7.771-3.023-10.763-0.07c-2.992,2.952-3.023,7.772-0.07,10.763c14.176,14.364,33.514,30.654,60.039,33.561v49.727 c-18.272-2.113-35.133-11.31-53.619-29.048c-3.03-2.91-7.849-2.812-10.761,0.222c-2.911,3.032-2.812,7.851,0.222,10.761 c13.811,13.254,35.026,30.489,64.158,33.35v62.017c-28.369-3.094-49.718-21.453-67.053-39.456 c-2.915-3.028-7.733-3.119-10.761-0.203c-3.027,2.916-3.119,7.734-0.203,10.761c19.639,20.396,44.116,41.198,78.019,44.2v24.95 c0,4.205,3.407,7.611,7.611,7.611c4.205,0,7.611-3.407,7.611-7.611v-24.813c36.416-2.558,63.024-25.38,86.382-49.939 C352.179,307.242,352.058,302.423,349.013,299.526z">
                                    </path>
                                </g>
                            </g>
                        </g>
                    </svg>
                </div>

                <div class="flex flex-1 ml-3 flex-col">
                    <h3 class="font-bold text-lg text-gray-900">Рабочие задачи</h3>
                    <p class="text-xs text-gray-500">Создано: 15 февраля 2026</p>
                </div>

                <div class="self-stretch flex flex-end items-baseline">
                    <button class="text-gray-400 hover:text-yellow-400 p-1 mb-1" aria-label="Добавить в избранное">
                        <i data-lucide="star" class="w-6 h-6"></i>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600 p-1">
                        <i data-lucide="more-vertical" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            <div class="flex-grow flex justify-center">
                <div class="text-center">
                    <h4 class="mb-4 text-center font-medium text-gray-700">Прогресс выполнения списка задач</h4>
                    <div class="flex items-center gap-4 justify-center">
                        <!-- Progress Circle Container -->
                        <div class="relative w-[100px] h-[100px]">
                            <svg viewBox="0 0 100 100" class="absolute top-0 left-0 w-full h-full"
                                style="transform: rotate(-90deg);">
                                <!-- Background Circle -->
                                <circle cx="50" cy="50" r="45" class="fill-none stroke-gray-200"
                                    stroke-width="8" stroke-linecap="round" />
                                <!-- Progress Circle -->
                                <circle cx="50" cy="50" r="45"
                                    class="fill-none stroke-indigo-600 transition-all duration-500" stroke-width="8"
                                    stroke-linecap="round" stroke-dasharray="283" stroke-dashoffset="198.1" />
                            </svg>
                            <div
                                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center">
                                <span class="font-bold text-xl text-gray-900">30%</span>
                            </div>
                        </div>
                        <div class="max-w-[120px] text-center">
                            <p class="text-sm text-gray-600">3 из 10 задач выполнено</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between border-t border-gray-200 pt-5 mt-auto">
                <div
                    class="px-3 py-1.5 rounded-lg text-md font-bold bg-indigo-100 text-indigo-800 flex items-center gap-1.5">

                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                    <span>Работа</span>
                </div>
                <button
                    class="text-indigo-600 hover:text-indigo-800 font-bold text-md flex items-center gap-1.5 px-3 py-1.5 rounded-lg">
                    <span>Открыть</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
        <!-- Card 2: Идеи для проекта (без прогресса) -->
        <div
            class="min-w-[320px] basis-[320px] h-[340px] flex grow flex-col p-4 bg-white rounded-xl shadow-md border border-gray-200 hover:shadow-lg transition-all">
            <div class="flex items-start justify-between mb-6">
                <div class="w-8 flex-shrink-0 self-stretch">
                    <!-- Иконка папки (оставлена как есть, так как это кастомный SVG) -->
                    <svg class="fill-black-500" height="45px" width="32px" version="1.1" id="Layer_1"
                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        viewBox="0 0 512.001 512.001" xml:space="preserve">
                        <!-- SVG content remains unchanged -->
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <!-- Full SVG path data remains here -->
                            <g>
                                <g>
                                    <path
                                        d="M412.253,269.541c-5.722-5.72-11.812-8.898-18.233-9.534c16.285-26.353,18.927-48.994,6.323-61.598 c-5.663-5.662-11.715-8.668-18.122-9.019c10.358-20.115,10.877-36.979,0.832-47.024c-5.809-5.808-10.614-10.233-16.146-11.618 c0.201-0.76,0.375-1.515,0.523-2.265c1.875-9.616-0.91-18.473-8.056-25.62c-4.799-4.798-10.053-7.205-15.727-7.085 c-0.756,0.013-1.505,0.07-2.246,0.168c3.884-10.339,4.371-20.223-2.467-27.06c-4.032-4.031-8.506-6.073-13.304-6.073 c-0.083,0-0.168,0.001-0.253,0.002c-4.961,0.074-9.396,2.283-13.718,5.677c-12.087-38.205-49.414-65.752-51.178-67.035 c-2.67-1.944-6.288-1.944-8.958,0c-1.738,1.266-38.583,28.456-50.924,66.269c-4.065-3.102-8.369-5.176-13.199-5.265 c-5.111-0.077-9.887,2.068-14.245,6.426c-2.601,2.601-5.676,7.295-5.552,14.879c0.061,3.679,0.849,7.579,2.199,11.58 c-0.145-0.006-0.29-0.011-0.436-0.015c-5.88-0.094-11.495,2.4-16.629,7.532c-7.131,7.132-8.31,17.802-3.758,30.668 c-6.176-0.327-12.952,1.892-19.897,8.837c-10.094,10.095-9.853,26.559,0.245,46.478c-6.093,0.805-11.946,3.995-17.516,9.565 c-12.688,12.689-10.403,34.877,5.667,61.112c-6.095,1.078-11.962,4.419-17.563,10.019c-7.314,7.315-13.753,21.539-2.892,47.762 c10.443,25.21,33.817,53.819,62.527,76.527c25.235,19.961,51.545,32.952,75.975,37.765l-4.653,72.305 c-0.135,2.099,0.605,4.161,2.044,5.694c1.439,1.535,3.447,2.405,5.552,2.405h30.014c2.087,0,4.081-0.857,5.519-2.37 c1.437-1.512,2.19-3.549,2.083-5.633l-3.689-71.656c25.607-4.102,53.502-17.493,80.188-38.735 c28.613-22.773,51.937-51.441,62.392-76.682C425.786,290.825,419.463,276.752,412.253,269.541z M246.584,496.776l4.07-63.24 c1.825,0.103,3.636,0.156,5.429,0.156c0.377,0,0.758-0.012,1.137-0.017l3.249,63.101H246.584z M400.911,311.099 c-9.37,22.623-31.522,49.674-57.808,70.596c-26.424,21.032-53.717,33.674-77.917,36.267c-0.252-0.025-0.506-0.039-0.763-0.039 h-17.846c-24.117-2.674-51.279-15.229-77.583-36.034c-26.366-20.855-48.555-47.836-57.907-70.413 c-3.454-8.34-8.1-23.479-0.407-31.171c4.258-4.259,7.87-5.933,11.338-5.933c2.887,0,5.675,1.16,8.652,2.952 c3.14,1.89,7.183,1.273,9.618-1.467c2.434-2.741,2.568-6.829,0.318-9.723c-19.613-25.24-26.859-48.131-18.03-56.961 c6.379-6.381,11.305-6.888,18.815-1.94c3.06,2.015,7.116,1.567,9.66-1.067c2.545-2.633,2.855-6.703,0.738-9.691 c-13.717-19.352-18.515-36.772-11.943-43.345c5.309-5.307,9.772-7.845,22.919,4.728c2.849,2.724,7.307,2.821,10.272,0.227 c2.967-2.596,3.461-7.027,1.139-10.212c-11.151-15.298-15.54-29.381-10.675-34.247c3.131-3.13,4.924-3.093,5.519-3.077 c5.068,0.115,13.075,8.888,20.82,17.371c0.064,0.07,0.129,0.141,0.193,0.212c4.214,4.934,8.619,9.505,12.831,13.45 c0.012,0.011,0.023,0.021,0.036,0.032c2.078,2.009,4.241,3.995,6.49,5.906c3.191,2.712,7.968,2.339,10.696-0.83 c2.731-3.169,2.391-7.949-0.76-10.702c-2.134-1.863-3.004-2.646-6.041-5.506c-0.005-0.005-0.009-0.009-0.015-0.014 c-4.056-3.937-7.845-8.058-11.542-12.107c-11.178-13.129-18.768-26.734-18.902-34.85c-0.045-2.725,0.814-3.582,1.096-3.865 c0.739-0.74,2.127-1.971,3.174-1.971c0.008,0,0.016,0,0.023,0.001c3.152,0.059,9.748,7.162,12.231,9.835 c1.992,2.145,5.035,2.959,7.837,2.091c2.797-0.869,4.846-3.265,5.271-6.162c4.519-30.77,32.496-56.87,43.546-66.107 c11.152,9.321,39.526,35.798,43.665,66.916c0.388,2.911,2.415,5.34,5.211,6.24c2.798,0.901,5.86,0.113,7.874-2.026 c2.928-3.108,9.784-10.386,12.85-10.432c0.005,0,0.01,0,0.015,0c0.814,0,1.945,1.01,2.551,1.616 c1.715,1.715,1.079,8.211-4.37,18.422c-5.511,10.325-14.891,22.656-25.757,33.866c-1.31,1.207-2.645,2.391-4.006,3.538 c-0.481,0.393-1.026,0.84-1.69,1.395c-3.205,2.682-3.649,7.447-0.994,10.675c2.657,3.227,7.42,3.707,10.666,1.08 c0.591-0.478,1.173-0.959,1.752-1.446c0.047-0.038,0.092-0.074,0.138-0.112c1.452-1.186,2.505-2.047,4.67-4.135 c5.397-4.989,10.254-10.192,14.686-14.939c7.763-8.315,15.79-16.914,20.644-16.997c0.505-0.016,2.036-0.037,4.701,2.629 c3.055,3.054,11.166,11.162-9.798,34.028c-0.006,0.007-0.011,0.012-0.017,0.019c-2.842,3.098-2.634,7.913,0.465,10.754 c3.097,2.841,7.912,2.634,10.754-0.465c4.101-4.471,10.942-11.931,13.19-12.44c1.246,0.263,4.302,2.821,9.087,7.607 c6.918,6.917,0.748,26.276-15.003,47.074c-2.324,3.068-1.992,7.387,0.773,10.064c2.765,2.676,7.093,2.869,10.085,0.447 c10.735-8.692,15.641-7.337,21.437-1.543c9.067,9.066-0.026,34.713-21.619,60.984c-2.46,2.993-2.279,7.356,0.419,10.135 c2.7,2.78,7.054,3.087,10.118,0.719c11.231-8.686,16.746-6.952,22.99-0.706C408.981,287.797,404.343,302.815,400.911,311.099z">
                                    </path>
                                </g>
                            </g>
                            <g>
                                <g>
                                    <path
                                        d="M349.013,299.526c-3.051-2.897-7.866-2.776-10.764,0.27c-19.568,20.574-42.996,42.432-75.352,45.164v-62.037 c31.671-2.436,54.837-21.488,71.729-38.232c2.985-2.959,3.006-7.779,0.047-10.763c-2.961-2.987-7.778-3.007-10.763-0.047 c-15.661,15.525-35.021,31.359-61.011,33.762V217.91c27.382-2.373,47.35-18.66,61.927-32.999 c2.996-2.947,3.036-7.767,0.088-10.764c-2.949-2.998-7.768-3.035-10.763-0.088c-12.438,12.236-29.237,26.111-51.252,28.555V71.27 c0-4.205-3.407-7.611-7.611-7.611c-4.205,0-7.611,3.407-7.611,7.611v131.144c-20.829-2.858-36.469-16.007-49.204-28.911 c-2.952-2.991-7.771-3.023-10.763-0.07c-2.992,2.952-3.023,7.772-0.07,10.763c14.176,14.364,33.514,30.654,60.039,33.561v49.727 c-18.272-2.113-35.133-11.31-53.619-29.048c-3.03-2.91-7.849-2.812-10.761,0.222c-2.911,3.032-2.812,7.851,0.222,10.761 c13.811,13.254,35.026,30.489,64.158,33.35v62.017c-28.369-3.094-49.718-21.453-67.053-39.456 c-2.915-3.028-7.733-3.119-10.761-0.203c-3.027,2.916-3.119,7.734-0.203,10.761c19.639,20.396,44.116,41.198,78.019,44.2v24.95 c0,4.205,3.407,7.611,7.611,7.611c4.205,0,7.611-3.407,7.611-7.611v-24.813c36.416-2.558,63.024-25.38,86.382-49.939 C352.179,307.242,352.058,302.423,349.013,299.526z">
                                    </path>
                                </g>
                            </g>
                        </g>
                    </svg>
                </div>

                <div class="flex flex-1 ml-3 flex-col">
                    <h3 class="font-bold text-lg text-gray-900">Идеи для проекта</h3>
                    <p class="text-xs text-gray-500">Создано: 12 января 2026</p>
                </div>

                <div class="self-stretch flex flex-end items-baseline">
                    <button class="text-gray-400 hover:text-yellow-400 p-1 mb-1" aria-label="Добавить в избранное">
                        <i data-lucide="star" class="w-6 h-6"></i>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600 p-1">
                        <i data-lucide="more-vertical" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            <div class="flex-grow flex">
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Nisi nam eaque laudantium, autem sint
                    sit ut placeat laborum aut, ex consequuntur error itaque saepe sapiente!</p>
            </div>

            <div class="flex justify-between border-t border-gray-200 pt-5 mt-auto">
                <div
                    class="px-3 py-1.5 rounded-lg text-md font-bold bg-indigo-100 text-indigo-800 flex items-center gap-1.5">
                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                    <span>Личное</span>
                </div>
                <button
                    class="text-indigo-600 hover:text-indigo-800 font-bold text-md flex items-center gap-1.5 px-3 py-1.5 rounded-lg">
                    <span>Открыть</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

    </div>

    <!-- Delete Confirmation Modal -->
    @if ($confirmingDeletion)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900">Удалить папку?</h3>
                <p class="text-gray-600 mt-2">Папка будет перемещена в корзину. Вы сможете восстановить её позже.
                </p>
                <div class="flex justify-end gap-4 mt-6">
                    <button type="button"
                        class="px-5 py-2.5 text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
                        wire:click="closeModal">
                        Отменить
                    </button>
                    <button type="button"
                        class="px-5 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors"
                        wire:click="deleteFolder({{ $folder->id }})">
                        Удалить
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
