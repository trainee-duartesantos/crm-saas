import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';

/**
 * Controla se o Wayfinder deve ser executado pelo Vite
 * - true  → Vite executa wayfinder:generate
 * - false → geração manual apenas
 */
export default defineConfig(({ mode }) => {
    // 🔑 Carrega as variáveis do .env
    const env = loadEnv(mode, process.cwd(), '');

    /**
     * Controla se o Wayfinder deve ser executado pelo Vite
     * WAYFINDER_GENERATE=false → NÃO executa
     */
    const enableWayfinder = env.WAYFINDER_GENERATE !== 'false';

    return {
        plugins: [
            laravel({
                input: ['resources/js/app.ts'],
                ssr: 'resources/js/ssr.ts',
                refresh: true,
            }),

            tailwindcss(),

            // ⚠️ Só entra se permitido
            enableWayfinder &&
                wayfinder({
                    formVariants: true,
                }),

            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ].filter(Boolean),
    };
});
