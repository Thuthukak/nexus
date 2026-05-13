<script setup>
import { router } from '@inertiajs/vue3'
import { useThemeStore } from '@shared/stores/useThemeStore.js'

const theme = useThemeStore()

function logout() {
  router.post('/logout')
}
</script>

<template>
  <header class="h-14 bg-surface border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 flex-shrink-0 z-40">
    <!-- Left: Page title slot -->
    <div class="flex items-center gap-4">
      <slot name="left" />
    </div>

    <!-- Right: Actions -->
    <div class="flex items-center gap-3">
      <!-- Dark mode toggle -->
      <button
        @click="theme.toggleDark()"
        class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        title="Toggle dark mode"
      >
        <svg v-if="theme.darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
      </button>

      <!-- Logout -->
      <button
        @click="logout"
        class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors px-3 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
      >
        Sign out
      </button>
    </div>
  </header>
</template>
