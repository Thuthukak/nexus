<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page      = usePage()
const collapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true')

function toggle() {
  collapsed.value = !collapsed.value
  localStorage.setItem('sidebarCollapsed', String(collapsed.value))
}

const navItems = [
  {
    label: 'Dashboard',
    href:  '/dashboard',
    icon:  'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  },
  {
    label: 'Financial',
    href:  '/financial/dashboard',
    icon:  'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
  },
]

function isActive(href) {
  return page.url.startsWith(href)
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
      <span v-if="!collapsed" class="text-white font-bold text-lg tracking-tight truncate">
        {{ page.props.app?.name ?? 'Nexus' }}
      </span>
      <span v-else class="text-white font-bold text-lg mx-auto">N</span>
    </div>

    <!-- Zone 2: Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-2">
      <template v-for="item in navItems" :key="item.href">
        <a :href="item.href"
           :title="collapsed ? item.label : undefined"
           class="flex items-center rounded-lg transition-colors duration-150"
           :class="[
             collapsed ? 'px-2 py-2 justify-center' : 'px-3 py-2 gap-3',
             isActive(item.href)
               ? 'bg-white/15 text-white border-l-2 border-white'
               : 'text-white/60 hover:bg-white/10 hover:text-white'
           ]">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
          </svg>
          <span v-if="!collapsed" class="text-sm font-medium truncate">{{ item.label }}</span>
        </a>
      </template>
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
          <p class="text-white text-sm font-medium truncate">{{ page.props.auth?.user?.name ?? 'User' }}</p>
          <p class="text-white/40 text-xs truncate">{{ page.props.auth?.user?.email ?? '' }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>
