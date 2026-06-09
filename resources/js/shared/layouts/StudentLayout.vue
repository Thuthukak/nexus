<script setup>
import { usePage, router } from '@inertiajs/vue3'

const page    = usePage()
const appName = page.props.app?.name ?? 'Learning Portal'
const logoUrl = page.props.app?.logo_url ?? null

function logout() {
  router.post('/logout')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
    <!-- Top bar -->
    <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-30">
      <div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <img v-if="logoUrl" :src="logoUrl" class="h-7 w-auto object-contain" />
          <span v-else class="font-bold text-gray-900 dark:text-white text-base">{{ appName }}</span>
        </div>
        <div class="flex items-center gap-3">
          <a href="/student/dashboard"
             class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            My Courses
          </a>
          <button @click="logout"
                  class="text-sm text-red-500 hover:text-red-600">
            Sign out
          </button>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="max-w-4xl mx-auto px-4 py-6">
      <slot />
    </main>
  </div>
</template>
