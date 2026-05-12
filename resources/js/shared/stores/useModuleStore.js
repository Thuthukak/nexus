import { defineStore } from 'pinia'
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export const useModuleStore = defineStore('modules', () => {
    const page          = usePage()
    const activeModules = computed(() => page.props.activeModules ?? [])

    function isActive(moduleName) {
        return activeModules.value.includes(moduleName)
    }

    return { activeModules, isActive }
})
