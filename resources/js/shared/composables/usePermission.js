import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function usePermission() {
    const page = usePage()

    const permissions = computed(() =>
        page.props.auth?.permissions ?? []
    )

    function can(permission) {
        return permissions.value.includes(permission)
    }

    function canAny(permissionList) {
        return permissionList.some(p => can(p))
    }

    function canAll(permissionList) {
        return permissionList.every(p => can(p))
    }

    return { can, canAny, canAll, permissions }
}
