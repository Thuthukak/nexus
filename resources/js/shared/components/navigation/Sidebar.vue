<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page      = usePage()
const collapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true')

function toggle() {
  collapsed.value = !collapsed.value
  localStorage.setItem('sidebarCollapsed', String(collapsed.value))
}

const navGroups = [
  {
    items: [
      {
        label: 'Dashboard',
        href:  '/dashboard',
        icon:  'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
      },
    ],
  },
  {
    label: 'Financial',
    icon:  'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
    base:  '/financial',
    items: [
      { label: 'Overview',   href: '/financial/dashboard' },
      { label: 'Invoices',   href: '/financial/invoices' },
      { label: 'Recurring',   href: '/financial/recurring' },
      { label: 'Customers',  href: '/financial/customers' },
      { label: 'Tax Rates',  href: '/financial/tax-rates' },
    ],
  },
  {
    label: 'HR',
    icon:  'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0',
    base:  '/hr',
    items: [
      { label: 'Overview',    href: '/hr/dashboard' },
      { label: 'Employees',   href: '/hr/employees' },
      { label: 'Leave',       href: '/hr/leave' },
      { label: 'Departments', href: '/hr/departments' },
    ],
  },
  {
    label: 'Bookings',
    icon:  'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    base:  '/bookings',
    items: [
      { label: 'Overview',  href: '/bookings/dashboard' },
      { label: 'Bookings',  href: '/bookings/bookings' },
      { label: 'Services',  href: '/bookings/services' },
      { label: 'Resources', href: '/bookings/resources' },
    ],
  },
]

// Track which groups are expanded
const expanded = ref(
  Object.fromEntries(
    navGroups
      .filter(g => g.base)
      .map(g => [g.base, localStorage.getItem(`nav_${g.base}`) !== 'false'])
  )
)

function toggleGroup(base) {
  expanded.value[base] = !expanded.value[base]
  localStorage.setItem(`nav_${base}`, String(expanded.value[base]))
}

function isActive(href) {
  return page.url === href || page.url.startsWith(href + '/')
}

function groupIsActive(base) {
  return base && page.url.startsWith(base)
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
        <img
          v-if="page.props.app?.logo_url"
          :src="page.props.app.logo_url"
          :alt="page.props.app?.name ?? 'Nexus'"
          class="h-8 w-auto object-contain max-w-[140px]"
        />
        <span v-else class="text-white font-bold text-lg tracking-tight truncate">
          {{ page.props.app?.name ?? 'Nexus' }}
        </span>
      </template>
      <div v-else class="mx-auto">
        <img
          v-if="page.props.app?.logo_url"
          :src="page.props.app.logo_url"
          class="h-7 w-7 object-contain rounded"
        />
        <span v-else class="text-white font-bold text-lg">N</span>
      </div>
    </div>

    <!-- Zone 2: Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
      <template v-for="group in navGroups" :key="group.label ?? 'core'">

        <!-- Single items (no group label) -->
        <template v-if="!group.label">
          <template v-for="item in group.items" :key="item.href">
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
        </template>

        <!-- Grouped items with collapsible header -->
        <template v-else>
          <!-- Group header -->
          <button
            @click="collapsed ? null : toggleGroup(group.base)"
            :title="collapsed ? group.label : undefined"
            class="w-full flex items-center rounded-lg transition-colors duration-150 mt-2"
            :class="[
              collapsed ? 'px-2 py-2 justify-center' : 'px-3 py-2 gap-3',
              groupIsActive(group.base)
                ? 'text-white'
                : 'text-white/50 hover:text-white/80'
            ]"
          >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" :d="group.icon" />
            </svg>
            <span v-if="!collapsed" class="text-xs font-semibold uppercase tracking-wider flex-1 text-left">
              {{ group.label }}
            </span>
            <svg v-if="!collapsed" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200"
                 :class="expanded[group.base] ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Sub-items -->
          <template v-if="!collapsed && expanded[group.base]">
            <a v-for="item in group.items" :key="item.href"
               :href="item.href"
               class="flex items-center pl-8 pr-3 py-1.5 rounded-lg text-sm transition-colors duration-150"
               :class="isActive(item.href)
                 ? 'bg-white/15 text-white font-medium'
                 : 'text-white/50 hover:bg-white/10 hover:text-white'">
              {{ item.label }}
            </a>
          </template>

          <!-- Collapsed: show icons for sub-items -->
          <template v-if="collapsed">
            <a v-for="item in group.items" :key="item.href"
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
