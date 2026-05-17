import { createApp, h }        from 'vue'
import { createInertiaApp }    from '@inertiajs/vue3'
import { createPinia }         from 'pinia'
import '../css/app.css'

const pages = import.meta.glob([
    './Pages/**/*.vue',
    '../../Modules/*/resources/js/Pages/**/*.vue',
], { eager: true })

function resolvePage(name) {
    const corePath = `./Pages/${name}.vue`
    if (pages[corePath]) return pages[corePath].default

    const parts = name.split('/')
    if (parts.length >= 3 && parts[1] === 'Pages') {
        const moduleName = parts[0]
        const pagePath   = parts.slice(2).join('/')
        const key = `../../Modules/${moduleName}/resources/js/Pages/${pagePath}.vue`
        if (pages[key]) return pages[key].default
    }

    console.error(`Page not found: "${name}"`, Object.keys(pages))
    throw new Error(`Inertia page not found: ${name}`)
}

// Click-outside directive — uses nextTick delay to avoid
// same-click closing the element that just opened
const clickOutside = {
    beforeMount(el, binding) {
        el._clickOutsideHandler = (event) => {
            if (!el.contains(event.target)) {
                binding.value(event)
            }
        }
        // Defer attachment so the triggering click doesn't
        // immediately fire the handler
        setTimeout(() => {
            document.addEventListener('click', el._clickOutsideHandler)
        }, 0)
    },
    unmounted(el) {
        document.removeEventListener('click', el._clickOutsideHandler)
    },
}

createInertiaApp({
    resolve: resolvePage,
    setup({ el, App, props, plugin }) {
        const pinia = createPinia()
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .directive('click-outside', clickOutside)
            .mount(el)
    },
    progress: {
        color: 'var(--color-primary)',
    },
})
