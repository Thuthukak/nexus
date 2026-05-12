import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref([])
    const unreadCount   = ref(0)
    const isOpen        = ref(false)
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

    function startPolling(ms = 30000) {
        fetch()
        pollInterval = setInterval(fetch, ms)
    }

    function stopPolling() {
        clearInterval(pollInterval)
    }

    return {
        notifications, unreadCount, isOpen,
        fetch, markRead, markAllRead,
        startPolling, stopPolling,
    }
})
