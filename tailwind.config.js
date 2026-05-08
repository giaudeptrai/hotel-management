// tailwind.config.js
export default {
    darkMode: 'class',

    // QUAN TRỌNG: Phải có đống này Tailwind mới quét được class của ông
    content: [
        './resources/js/**/*.vue',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                // Dasher Colors
                'primary': {
                    50: '#f0f4ff',
                    500: '#3e63e2',
                    600: '#3251c9',
                },
                'surface': '#f4f7fe',
                'main-text': '#2b3674',
                'muted-text': '#a3aed0',

                // Dark Mode Colors
                'dark-bg': '#0b111e',
                'dark-card': '#151c2c',
                'dark-border': '#222d45',
                'dark-text': '#F1F5F9', // <--- Class text-dark-text lấy từ đây
            },
            boxShadow: {
                'app': '14px 17px 40px 4px rgba(112, 144, 176, 0.08)',
                'app-dark': '0px 10px 30px rgba(0, 0, 0, 0.3)',
                'glow': '0 0 15px -3px rgba(62, 99, 226, 0.3)',
            },
            borderRadius: {
                'layout': '0.75rem',
                'card': '1.25rem',
            }
        },
    },
    plugins: [require('@tailwindcss/forms')],
};