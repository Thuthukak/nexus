<script setup>
import { watchEffect } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Sidebar from '@shared/components/navigation/Sidebar.vue'
import Topbar from '@shared/components/navigation/Topbar.vue'
import { useThemeStore } from '@shared/stores/useThemeStore.js'
import { useNotificationStore } from '@shared/stores/useNotificationStore.js'

const page          = usePage()
const theme         = useThemeStore()
const notifications = useNotificationStore()

// Apply theme CSS variables whenever shared props update
watchEffect(() => {
  const t = page.props.theme
  if (!t) return
  const r = document.documentElement
  r.style.setProperty('--color-primary',      t.primary)
  r.style.setProperty('--color-primary-text', t.primary_text)
  r.style.setProperty('--color-secondary',    t.secondary)
  r.style.setProperty('--color-accent',       t.accent)
  r.style.setProperty('--color-sidebar-bg',   t.sidebar_bg)
  r.style.setProperty('--color-sidebar-text', t.sidebar_text)
  r.style.setProperty('--color-surface',      t.surface)
  r.style.setProperty('--color-background',   t.background)
  r.style.setProperty('--color-text',         t.text)
})
</script>

<template>
  <div class="flex h-screen bg-background overflow-hidden">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main content area -->
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
      <!-- Topbar -->
      <Topbar />

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-6 py-6">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
