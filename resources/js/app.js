import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import '../css/app.css'

// Auto-discover all page components across modules and core
const pages = import.meta.glob([
    './Pages/**/*.vue',
    '../../Modules/*/Resources/js/Pages/**/*.vue',
], { eager: true })

function resolvePage(name) {
    // Try module-namespaced path first: "Financial/Pages/Invoices/Index"
    const candidates = [
        `./Pages/${name}.vue`,
        `../../Modules/${name}.vue`,
    ]
    for (const key of Object.keys(pages)) {
        // Match ModuleName/Pages/... pattern
        const moduleMatch = key.match(/Modules\/(\w+)\/Resources\/js\/Pages\/(.+)\.vue$/)
        if (moduleMatch) {
            const resolved = `${moduleMatch[1]}/Pages/${moduleMatch[2]}`
            if (resolved === name) return pages[key].default
        }
        // Match core Pages/... pattern
        const coreMatch = key.match(/\.\/Pages\/(.+)\.vue$/)
        if (coreMatch && coreMatch[1] === name) return pages[key].default
    }
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