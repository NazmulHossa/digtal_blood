/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',   // ← সব Vue file scan করবে
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                // Brand রঙ — Tailwind class হিসেবে ব্যবহার করা যাবে
                // e.g., bg-blood-red, text-blood-dark
                'blood-red':   '#BC0000',
                'blood-dark':  '#8B0000',
                'blood-light': '#FEF2F2',
            },
            fontFamily: {
                display: ['Georgia', 'Times New Roman', 'serif'],
                body:    ['system-ui', '-apple-system', 'sans-serif'],
            },
        },
    },
    plugins: [],
};