import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        // Warna badge status didefinisikan di enum (badgeColor/dotColor)
        './app/Enums/**/*.php',
        './app/Models/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                '2xs': '0 1px 2px 0 rgb(15 23 42 / 0.04)',
                'xs': '0 1px 3px 0 rgb(15 23 42 / 0.06), 0 1px 2px -1px rgb(15 23 42 / 0.04)',
            },
            backdropBlur: {
                'xs': '2px',
            },
            blur: {
                'xs': '2px',
            },
            scale: {
                '98': '0.98',
            },
        },
    },

    plugins: [forms],
};
