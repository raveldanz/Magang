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
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
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
