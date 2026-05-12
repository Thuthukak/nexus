import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import { readdirSync, existsSync } from 'fs'

// Auto-discover module JS entry points
function getModuleInputs() {
    const modulesPath = path.resolve(__dirname, 'Modules')
    if (!existsSync(modulesPath)) return []
    return readdirSync(modulesPath)
        .map(mod => `Modules/${mod}/Resources/js/app.js`)
        .filter(p => existsSync(path.resolve(__dirname, p)))
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css',
                ...getModuleInputs(),
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
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@shared': path.resolve(__dirname, 'resources/js/shared'),
            '@modules': path.resolve(__dirname, 'Modules'),
        },
    },
})