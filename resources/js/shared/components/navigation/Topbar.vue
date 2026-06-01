<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { usePage, router }             from '@inertiajs/vue3'
import { useThemeStore }               from '@shared/stores/useThemeStore.js'
import { useNotificationStore }        from '@shared/stores/useNotificationStore.js'
import NotificationPanel               from '@shared/components/feedback/NotificationPanel.vue'

const theme         = useThemeStore()
const notifications = useNotificationStore()
const page          = usePage()
const menuOpen      = ref(false)

// Start polling when topbar mounts
onMounted(() => notifications.startPolling(30000))
onUnmounted(() => notifications.stopPolling())

function logout() {
  router.post('/logout')
}

function toggleNotifications() {
  notifications.isOpen = !notifications.isOpen
  if (notifications.isOpen) {
    notifications.fetch()
  }
}
</script>

<template>
  <header class="h-14 bg-surface border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 flex-shrink-0 z-40">
    <!-- Left slot -->
    <div class="flex items-center gap-4">
      <slot name="left" />
    </div>

    <!-- Right actions -->
    <div class="flex items-center gap-1">

      <!-- Dark mode toggle -->
      <button
        @click="theme.toggleDark()"
        class="p-2 rounded-lg text-app-text/50 hover:text-app-text hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        title="Toggle dark mode"
      >
        <svg v-if="theme.darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
        </svg>
        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
      </button>

      <!-- Bell icon -->
      <button
        @click="toggleNotifications"
        class="relative p-2 rounded-lg text-app-text/50 hover:text-app-text hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        title="Notifications"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Unread badge -->
        <span
          v-if="notifications.unreadCount > 0"
          class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 text-white text-[10px] font-bold rounded-full"
          style="background-color: var(--color-primary);"
        >
          {{ notifications.unreadCount > 9 ? '9+' : notifications.unreadCount }}
        </span>
      </button>

      <!-- User menu -->
      <div class="relative">
        <button
          @click="menuOpen = !menuOpen"
          class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        >
          <div class="w-7 h-7 rounded-full bg-primary flex items-center justify-center">
            <span class="text-xs font-semibold text-primary-text">
              {{ page.props.auth?.user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
            </span>
          </div>
          <span class="text-sm font-medium text-app-text hidden sm:block">
            {{ page.props.auth?.user?.name ?? 'User' }}
          </span>
          <svg class="w-3.5 h-3.5 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Dropdown -->
        <Transition
          enter-active-class="transition-all duration-150 ease-out"
          enter-from-class="opacity-0 scale-95 -translate-y-1"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition-all duration-100 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 -translate-y-1"
        >
          <div
            v-if="menuOpen"
            v-click-outside="() => menuOpen = false"
            class="absolute right-0 top-full mt-1 w-52 bg-surface rounded-xl shadow-lg border border-gray-100 dark:border-gray-800 py-1 z-50"
          >
            <a href="/profile"
               @click="menuOpen = false"
               class="flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
              <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              My Profile
            </a>
            <a href="/profile/notifications"
               @click="menuOpen = false"
               class="flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
              <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              Notifications
            </a>
            <a href="/settings/appearance"
               @click="menuOpen = false"
               class="flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
              <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Appearance
            </a>
            <a href="/settings/general"
               @click="menuOpen = false"
               class="flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
              <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
              </svg>
              General Settings
            </a>
            <div class="my-1 border-t border-gray-100 dark:border-gray-800" />
            <button
              @click="logout"
              class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Sign out
            </button>
          </div>
        </Transition>
      </div>
    </div>
  </header>

  <!-- Notification panel (Teleported to body) -->
  <NotificationPanel />
</template>
