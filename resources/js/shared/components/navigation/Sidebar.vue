<script setup>
import { ref, computed } from 'vue'
import { usePage }       from '@inertiajs/vue3'
import { usePermission } from '@shared/composables/usePermission.js'

// Module nav configs — import all, filter by activeModules at runtime
import financialNav from '../../../../../Modules/Financial/resources/js/nav.js'
import hrNav        from '../../../../../Modules/HR/resources/js/nav.js'
import bookingsNav  from '../../../../../Modules/Bookings/resources/js/nav.js'
import eventsNav    from '../../../../../Modules/Events/resources/js/nav.js'

const page         = usePage()
const { can }      = usePermission()
const collapsed    = ref(localStorage.getItem('sidebarCollapsed') === 'true')

const activeModules = computed(() => page.props.activeModules ?? [])
function toggle() {
  collapsed.value = !collapsed.value
  localStorage.setItem('sidebarCollapsed', String(collapsed.value))
}

// All module nav configs ordered
const allModuleNavs = [financialNav, hrNav, bookingsNav, eventsNav]
    .sort((a, b) => a.order - b.order)

// Filter to only active modules, then filter items by permission
const visibleModules = computed(() =>
  allModuleNavs
    .filter(nav => activeModules.value.includes(nav.module))
    .map(nav => ({
      ...nav,
      items: nav.items.filter(item =>
        !item.permission || can(item.permission)
      ),
    }))
    .filter(nav => nav.items.length > 0)
)

// Collapse state per module group
const expanded = ref(
  Object.fromEntries(
    allModuleNavs.map(nav => [
      nav.module,
      localStorage.getItem(`nav_${nav.module}`) !== 'false',
    ])
  )
)

function toggleGroup(moduleName) {
  expanded.value[moduleName] = !expanded.value[moduleName]
  localStorage.setItem(`nav_${moduleName}`, String(expanded.value[moduleName]))
}

function isActive(href) {
  return page.url === href || page.url.startsWith(href + '/')
}

function groupIsActive(nav) {
  return nav.items.some(item => isActive(item.href))
}
</script>

<template>
  <aside
    :class="collapsed ? 'w-16' : 'w-64'"
    class="flex flex-col h-full transition-all duration-300 ease-in-out flex-shrink-0"
    style="background-color: var(--color-sidebar-bg);"
  >
    <!-- Zone 1: Logo -->
    <div class="h-14 flex items-center px-4 flex-shrink-0 border-b border-white/10">
      <template v-if="!collapsed">
        <img v-if="page.props.app?.logo_url"
             :src="page.props.app.logo_url"
             :alt="page.props.app?.name ?? 'Nexus'"
             class="h-8 w-auto object-contain max-w-[140px]" />
        <span v-else class="text-white font-bold text-lg tracking-tight truncate">
          {{ page.props.app?.name ?? 'Nexus' }}
        </span>
      </template>
      <div v-else class="mx-auto">
        <img v-if="page.props.app?.logo_url"
             :src="page.props.app.logo_url"
             class="h-7 w-7 object-contain rounded" />
        <span v-else class="text-white font-bold text-lg">N</span>
      </div>
    </div>

    <!-- Zone 2: Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">

      <!-- Dashboard — always visible (Core) -->
      <a href="/dashboard"
         :title="collapsed ? 'Dashboard' : undefined"
         class="flex items-center rounded-lg transition-colors duration-150"
         :class="[
           collapsed ? 'px-2 py-2 justify-center' : 'px-3 py-2 gap-3',
           isActive('/dashboard')
             ? 'bg-white/15 text-white border-l-2 border-white'
             : 'text-white/60 hover:bg-white/10 hover:text-white'
         ]">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span v-if="!collapsed" class="text-sm font-medium truncate">Dashboard</span>
      </a>

      <!-- Dynamic module groups -->
      <template v-for="nav in visibleModules" :key="nav.module">

        <!-- Group header -->
        <button
          @click="collapsed ? null : toggleGroup(nav.module)"
          :title="collapsed ? nav.label : undefined"
          class="w-full flex items-center rounded-lg transition-colors duration-150 mt-2"
          :class="[
            collapsed ? 'px-2 py-2 justify-center' : 'px-3 py-2 gap-3',
            groupIsActive(nav) ? 'text-white' : 'text-white/50 hover:text-white/80'
          ]"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" :d="nav.icon" />
          </svg>
          <span v-if="!collapsed" class="text-xs font-semibold uppercase tracking-wider flex-1 text-left">
            {{ nav.label }}
          </span>
          <svg v-if="!collapsed"
               class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200"
               :class="expanded[nav.module] ? 'rotate-180' : ''"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Sub-items — expanded -->
        <template v-if="!collapsed && expanded[nav.module]">
          <a v-for="item in nav.items" :key="item.href"
             :href="item.href"
             class="flex items-center pl-8 pr-3 py-1.5 rounded-lg text-sm transition-colors duration-150"
             :class="isActive(item.href)
               ? 'bg-white/15 text-white font-medium'
               : 'text-white/50 hover:bg-white/10 hover:text-white'">
            {{ item.label }}
          </a>
        </template>

        <!-- Sub-items — collapsed (dot indicators) -->
        <template v-if="collapsed">
          <a v-for="item in nav.items" :key="item.href"
             :href="item.href"
             :title="item.label"
             class="flex items-center justify-center px-2 py-1.5 rounded-lg transition-colors"
             :class="isActive(item.href)
               ? 'bg-white/15 text-white'
               : 'text-white/30 hover:bg-white/10 hover:text-white/70'">
            <span class="w-1.5 h-1.5 rounded-full bg-current" />
          </a>
        </template>

      </template>

      <!-- Settings — always visible for admins -->
      <div class="mt-4 pt-4 border-t border-white/10 space-y-1">
        <a v-if="!collapsed"
            href="/settings/appearance"
            class="flex items-center px-3 py-2 gap-3 rounded-lg transition-colors text-white/40 hover:text-white hover:bg-white/10">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span class="text-xs font-medium">Settings</span>
        </a>
        <a v-if="!collapsed"
            href="/users"
            class="flex items-center px-3 py-2 gap-3 rounded-lg transition-colors text-white/40 hover:text-white hover:bg-white/10">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <span class="text-xs font-medium">Users</span>
        </a>
        <a v-if="!collapsed"
            href="/roles"
            class="flex items-center px-3 py-2 gap-3 rounded-lg transition-colors text-white/40 hover:text-white hover:bg-white/10">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <span class="text-xs font-medium">Roles</span>
        </a>
        <a v-if="!collapsed"
            href="/admin/modules"
            class="flex items-center px-3 py-2 gap-3 rounded-lg transition-colors text-white/40 hover:text-white hover:bg-white/10">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
          <span class="text-xs font-medium">Modules</span>
        </a>
        <a v-if="!collapsed"
            href="/activity"
            class="flex items-center px-3 py-2 gap-3 rounded-lg transition-colors text-white/40 hover:text-white hover:bg-white/10">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          <span class="text-xs font-medium">Activity Log</span>
        </a>
      </div>
    </nav>

    <!-- Zone 3: User + collapse -->
    <div class="flex-shrink-0 border-t border-white/10">
      <button @click="toggle"
              class="w-full flex items-center justify-center py-2 text-white/40 hover:text-white hover:bg-white/10 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            :d="collapsed ? 'M13 5l7 7-7 7M5 5l7 7-7 7' : 'M11 19l-7-7 7-7m8 14l-7-7 7-7'" />
        </svg>
      </button>

      <div class="flex items-center gap-3 px-3 py-3" :class="collapsed ? 'justify-center' : ''">
        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
          <span class="text-white text-xs font-semibold">
            {{ page.props.auth?.user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
          </span>
        </div>
        <div v-if="!collapsed" class="flex-1 min-w-0">
          <p class="text-white text-sm font-medium truncate">
            {{ page.props.auth?.user?.name ?? 'User' }}
          </p>
          <p class="text-white/40 text-xs truncate">
            {{ page.props.auth?.user?.email ?? '' }}
          </p>
        </div>
      </div>
    </div>
  </aside>
</template>
