import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
        host: 'kaleaquinielas.test',
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: 'kaleaquinielas.test',
        },
    },
    plugins: [
        laravel({
            input: ['resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
});
