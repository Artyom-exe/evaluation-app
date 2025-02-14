import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import fg from 'fast-glob';

const pages = fg.sync('resources/js/Pages/**/*.vue');

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                ...pages,
            ],
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
    resolve: {
        extensions: ['.js', '.vue', '.json'] // résout correctement les fichiers .vue
    },
});
