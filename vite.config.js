import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                'resources/views/**',
                'resources/css/**',
                'resources/js/**',
                'routes/**',
            ],
        }),
    ],
    server: {
        watch: {
            ignored: [
                '**/storage/**',
                '**/bootstrap/cache/**',
                '**/node_modules/**',
                '**/vendor/**',
                '**/.git/**',
            ],
        },
    },
});
