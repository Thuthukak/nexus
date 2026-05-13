import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import '../css/app.css'

const pages = import.meta.glob([
    './Pages/**/*.vue',
    '../../Modules/*/resources/js/Pages/**/*.vue',
], { eager: true })

if (import.meta.env.DEV) {
    console.log('Discovered pages:', Object.keys(pages))
}

function resolvePage(name) {
    // Core pages: "Auth/Login" → ./Pages/Auth/Login.vue
    const corePath = `./Pages/${name}.vue`
    if (pages[corePath]) return pages[corePath].default

    // Module pages: "Core/Pages/Dashboard"
    // → ../../Modules/Core/resources/js/Pages/Dashboard.vue
    const parts = name.split('/')
    if (parts.length >= 3 && parts[1] === 'Pages') {
        const moduleName = parts[0]
        const pagePath   = parts.slice(2).join('/')
        const key = `../../Modules/${moduleName}/resources/js/Pages/${pagePath}.vue`
        if (pages[key]) return pages[key].default
    }

    console.error(`Page not found: "${name}"\nAvailable:`, Object.keys(pages))
    throw new Error(`Inertia page not found: ${name}`)
}

createInertiaApp({
    resolve: resolvePage,
    setup({ el, App, props, plugin }) {
        const pinia = createPinia()
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .mount(el)
    },
    progress: {
        color: 'var(--color-primary)',
    },
})
