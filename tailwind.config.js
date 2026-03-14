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
            colors: {
                luxury: {
                    navy: '#0A1128',
                    charcoal: '#1C1C1C',
                    gold: '#C5A059',
                    champagne: '#F7E7CE',
                }
            },
            fontFamily: {
                // Font nội dung: Nunito Sans cực kỳ uyển chuyển
                sans: ['Nunito Sans', 'ui-sans-serif', 'system-ui'],
                // Font tiêu đề: Sora tròn trịa, đẳng cấp
                display: ['Sora', 'sans-serif'],
            },
        },
    },

    plugins: [forms],
};