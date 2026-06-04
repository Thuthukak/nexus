<script setup>
import { ref }    from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const page    = usePage()
const navOpen = ref(false)

const nav = [
  {
    label: 'Dashboard',
    href:  '/portal/dashboard',
    icon:  'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  },
  {
    label: 'Invoices',
    href:  '/portal/invoices',
    icon:  'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
  },
  {
    label: 'Quotations',
    href:  '/portal/quotations',
    icon:  'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  },
  {
    label: 'Bookings',
    href:  '/portal/bookings',
    icon:  'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
  },
]

function isActive(href) {
  return page.url.startsWith(href)
}

function logout() {
  router.post('/portal/logout')
}

const appName = page.props.app?.name ?? 'Client Portal'
const logoUrl = page.props.app?.logo_url ?? null
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
    <!-- Top navigation -->
    <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-30">
      <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <div class="flex items-center gap-3">
            <img v-if="logoUrl" :src="logoUrl" class="h-8 w-auto object-contain" :alt="appName" />
            <span v-else class="font-bold text-gray-900 dark:text-white text-lg">{{ appName }}</span>
            <span class="text-xs text-gray-400 border border-gray-200 dark:border-gray-700 px-2 py-0.5 rounded-full hidden sm:block">
              Client Portal
            </span>
          </div>

          <!-- Desktop nav -->
          <nav class="hidden sm:flex items-center gap-1">
            <a v-for="item in nav" :key="item.href"
               :href="item.href"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
               :class="isActive(item.href)
                 ? 'bg-primary/10 text-primary'
                 : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800'">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon" />
              </svg>
              {{ item.label }}
            </a>
          </nav>

          <!-- Right: profile + logout -->
          <div class="flex items-center gap-2">
            <a href="/portal/profile"
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors hidden sm:block">
              Profile
            </a>
            <button @click="logout"
                    class="text-sm text-red-500 hover:text-red-600 px-3 py-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
              Sign out
            </button>

            <!-- Mobile menu button -->
            <button @click="navOpen = !navOpen"
                    class="sm:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  :d="navOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile nav -->
        <div v-if="navOpen" class="sm:hidden pb-3 border-t border-gray-100 dark:border-gray-800 pt-2 space-y-1">
          <a v-for="item in nav" :key="item.href"
             :href="item.href"
             @click="navOpen = false"
             class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
             :class="isActive(item.href)
               ? 'bg-primary/10 text-primary'
               : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100'">
            {{ item.label }}
          </a>
          <a href="/portal/profile" @click="navOpen = false"
             class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
            Profile
          </a>
        </div>
      </div>
    </header>

    <!-- Page content -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="text-center text-xs text-gray-400 py-6">
      {{ appName }} client portal
    </footer>
  </div>
</template>
