<script setup>
import { watchEffect, watch } from 'vue'
import { usePage }            from '@inertiajs/vue3'
import Sidebar                from '@shared/components/navigation/Sidebar.vue'
import Topbar                 from '@shared/components/navigation/Topbar.vue'
import ToastContainer         from '@shared/components/feedback/ToastContainer.vue'
import { useThemeStore }      from '@shared/stores/useThemeStore.js'
import { useToastStore }      from '@shared/stores/useToastStore.js'

const page  = usePage()
const theme = useThemeStore()
const toast = useToastStore()

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

  // RGB channel versions for Tailwind opacity modifiers (bg-primary/10 etc)
  function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
    return result
      ? `${parseInt(result[1], 16)} ${parseInt(result[2], 16)} ${parseInt(result[3], 16)}`
      : '0 0 0'
  }

  r.style.setProperty('--color-primary-rgb',      hexToRgb(t.primary))
  r.style.setProperty('--color-primary-text-rgb', hexToRgb(t.primary_text))
  r.style.setProperty('--color-secondary-rgb',    hexToRgb(t.secondary))
  r.style.setProperty('--color-accent-rgb',       hexToRgb(t.accent))
  r.style.setProperty('--color-sidebar-bg-rgb',   hexToRgb(t.sidebar_bg))
  r.style.setProperty('--color-sidebar-text-rgb', hexToRgb(t.sidebar_text))
  r.style.setProperty('--color-surface-rgb',      hexToRgb(t.surface))
  r.style.setProperty('--color-background-rgb',   hexToRgb(t.background))
  r.style.setProperty('--color-text-rgb',         hexToRgb(t.text))
})

// Watch for flash toasts from server redirects
watch(
  () => page.props.flash?.toast,
  (flashToast) => {
    if (flashToast?.title) {
      toast.add(flashToast)
    }
  },
  { immediate: true }
)
</script>

<template>
  <div class="flex h-screen bg-background overflow-hidden">
    <Sidebar />

    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
      <Topbar />

      <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-6 py-6">
          <slot />
        </div>
      </main>
    </div>

    <!-- Global toast notifications -->
    <ToastContainer />
  </div>
</template>
