import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

/**
 * vite.config.js
 * ───────────────
 * Vite = modern build tool (Webpack-এর বিকল্প)।
 * এটি Vue files compile করে এবং
 * development-এ hot reload দেয়।
 */
export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});