import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
    ],

    safelist: [
        // Общие UI-классы, которые могут не попадать в content scan
        'bg-white',
        'bg-black',
        'bg-red-100',
        'bg-red-500',
        'bg-orange-100',
        'bg-orange-500',
        'bg-yellow-100',
        'bg-yellow-500',
        'bg-green-100',
        'bg-green-500',
        'bg-blue-100',
        'bg-blue-500',
        'bg-indigo-100',
        'bg-indigo-500',
        'bg-purple-100',
        'bg-purple-500',
        'bg-pink-100',
        'bg-pink-500',
        'bg-gray-500',
        'border-gray-300',
        'border-white',
        'ring-offset-2',
        'ring-indigo-500',
        'stroke-gray-200',
        'stroke-indigo-600',
        'checklist-progress-svg',
        'checklist-progress-bg',
        'checklist-progress-fg',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
