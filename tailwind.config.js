import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#fdf3f3',
                    100: '#fbe5e5',
                    200: '#f7cfcf',
                    300: '#f0afaf',
                    400: '#e58283',
                    500: '#d55a5b',
                    600: '#c13c3d',
                    700: '#a32f30',
                    800: '#872a2b',
                    900: '#712728',
                    950: '#3d1011',
                }
            }
        },
    },

    plugins: [forms],
};
