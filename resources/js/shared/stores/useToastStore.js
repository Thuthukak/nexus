import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([])
    const TTL    = 5000 // ms

    function add({ type = 'info', title, message = '' }) {
        const id = `toast-${Date.now()}-${Math.random().toString(36).slice(2)}`
        toasts.value.push({ id, type, title, message })

        // Cap at 3 visible toasts
        if (toasts.value.length > 3) {
            toasts.value.shift()
        }

        setTimeout(() => dismiss(id), TTL)
    }

    function dismiss(id) {
        toasts.value = toasts.value.filter(t => t.id !== id)
    }

    function success(title, message = '') { add({ type: 'success', title, message }) }
    function error(title, message = '')   { add({ type: 'error',   title, message }) }
    function warning(title, message = '') { add({ type: 'warning', title, message }) }
    function info(title, message = '')    { add({ type: 'info',    title, message }) }

    return { toasts, add, dismiss, success, error, warning, info }
})
