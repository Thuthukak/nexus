import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useThemeStore = defineStore('theme', () => {
    const darkMode = ref(localStorage.getItem('darkMode') === 'true')

    function applyDark(val) {
        document.documentElement.classList.toggle('dark', val)
        localStorage.setItem('darkMode', String(val))
    }

    function toggleDark() {
        darkMode.value = !darkMode.value
    }

    watch(darkMode, applyDark, { immediate: true })

    return { darkMode, toggleDark }
})
