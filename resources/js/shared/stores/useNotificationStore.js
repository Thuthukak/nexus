import { defineStore } from 'pinia'
import { ref }         from 'vue'
import axios           from 'axios'
import { router }      from '@inertiajs/vue3'

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref([])
    const unreadCount   = ref(0)
    const isOpen        = ref(false)
    const loading       = ref(false)
    let   pollInterval  = null

    async function fetch() {
        try {
            const { data } = await axios.get('/notifications')
            notifications.value = data.notifications ?? []
            unreadCount.value   = data.unread_count  ?? 0
        } catch {}
    }

    async function markRead(id) {
        await axios.patch(`/notifications/${id}/read`)
        const n = notifications.value.find(n => n.id === id)
        if (n) n.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
    }

    async function markAllRead() {
        await axios.patch('/notifications/read-all')
        notifications.value.forEach(n => n.read_at = new Date().toISOString())
        unreadCount.value = 0
    }

    async function dismiss(id) {
        await axios.delete(`/notifications/${id}`)
        notifications.value = notifications.value.filter(n => n.id !== id)
    }

    function navigateTo(notification) {
        markRead(notification.id)
        isOpen.value = false
        if (notification.action?.url) {
            router.visit(notification.action.url)
        }
    }

    function startPolling(ms = 30000) {
        fetch()
        pollInterval = setInterval(fetch, ms)
    }

    function stopPolling() {
        if (pollInterval) clearInterval(pollInterval)
    }

    return {
        notifications, unreadCount, isOpen, loading,
        fetch, markRead, markAllRead, dismiss, navigateTo,
        startPolling, stopPolling,
    }
})
