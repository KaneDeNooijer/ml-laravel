import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['favicon.ico'],
            manifest: {
                name: 'LiveList',
                short_name: 'LiveList',
                description: 'Scan supermarket products and see their impact on your meal.',
                theme_color: '#0f766e',
                background_color: '#f8fafc',
                display: 'standalone',
                start_url: '/',
                icons: [
                    {
                        src: '/favicon.ico',
                        sizes: 'any',
                        type: 'image/x-icon',
                    },
                ],
            },
            workbox: {
                navigateFallback: '/',
                runtimeCaching: [
                    {
                        urlPattern: ({ request }) => request.destination === 'document',
                        handler: 'NetworkFirst',
                    },
                ],
            },
            devOptions: {
                enabled: true,
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
