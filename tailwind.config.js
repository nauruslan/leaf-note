import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        'bg-black',
        'bg-gray-500',
        'bg-red-500',
        'bg-orange-500',
        'bg-yellow-500',
        'bg-green-500',
        'bg-blue-500',
        'bg-indigo-500',
        'bg-purple-500',
        'bg-pink-500',
        'bg-white',
        'border-gray-700',
        'border-gray-600',
        'border-red-600',
        'border-orange-600',
        'border-yellow-600',
        'border-green-600',
        'border-blue-600',
        'border-indigo-600',
        'border-purple-600',
        'border-pink-600',
        'border-white',
        'ring-gray-700',
        'ring-gray-600',
        'ring-red-600',
        'ring-orange-600',
        'ring-yellow-600',
        'ring-green-600',
        'ring-blue-600',
        'ring-indigo-600',
        'ring-purple-600',
        'ring-pink-600',
        'ring-gray-400',
        'ring-offset-2',
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
