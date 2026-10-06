import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const developmentPort = Number(process.env.VITE_PORT ?? 5173);

export default defineConfig({
    server: {
        host: process.env.VITE_HOST ?? 'localhost',
        port: developmentPort,
        strictPort: true,
        hmr: {
            host: process.env.VITE_HMR_HOST ?? 'localhost',
            clientPort: Number(process.env.VITE_HMR_PORT ?? developmentPort),
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.ts',
                'resources/js/barcode-scanner/index.ts',
                'resources/js/drawer/index.ts',
                'resources/js/scanner-page.ts',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
