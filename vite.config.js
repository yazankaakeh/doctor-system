import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'Modules/Theme/resources/js/app.js',
                'Modules/Theme/resources/js/bootstrap.js',
            ],
            refresh: true,
        }),
    ],
});
