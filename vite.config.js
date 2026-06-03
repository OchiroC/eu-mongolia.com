import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    build: {
        rollupOptions: {
            // Гуравдагч талын сангийн (@vueuse/core г.м) "#__PURE__" annotation
            // байрлалын тухай хор хөнөөлгүй warning-ийг нууна.
            onLog(level, log, handler) {
                if (log.code === 'INVALID_ANNOTATION' && /#__PURE__/.test(log.message ?? '')) {
                    return;
                }
                handler(level, log);
            },
        },
    },
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
