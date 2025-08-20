import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '@assets': '/resources/js/assets',
            '@components': '/resources/js/components',
            '@layouts': '/resources/js/layouts',
            '@pages': '/resources/js/pages',
            '@stores': '/resources/js/stores',
            '@utils': '/resources/js/utils',
            '@types': '/resources/js/types',
            '@composables': '/resources/js/composables',
        },
    },
});
