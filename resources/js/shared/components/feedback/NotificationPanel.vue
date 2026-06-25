<script setup>
import { computed }              from 'vue'
import { useNotificationStore }  from '@shared/stores/useNotificationStore.js'

const store = useNotificationStore()

const unread = computed(() =>
  store.notifications.filter(n => !n.read_at)
)

const moduleColour = {
  Financial: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  HR:        'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
  Bookings:  'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
  Core:      'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
}

const dotColour = {
  green:  'bg-green-500',
  red:    'bg-red-500',
  blue:   'bg-blue-500',
  orange: 'bg-orange-500',
  yellow: 'bg-yellow-500',
  gray:   'bg-gray-400',
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 translate-x-4"
      enter-to-class="opacity-100 translate-x-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 translate-x-0"
      leave-to-class="opacity-0 translate-x-4"
    >
      <div v-if="store.isOpen"
           class="fixed inset-y-0 right-0 z-50 flex flex-col bg-surface w-96 shadow-2xl border-l border-gray-200 dark:border-gray-800">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-800 flex-shrink-0">
          <div class="flex items-center gap-3">
            <h2 class="font-semibold text-app-text">Notifications</h2>
            <span v-if="store.unreadCount > 0"
                  class="text-xs font-bold bg-primary text-primary-text px-2 py-0.5 rounded-full">
              {{ store.unreadCount }}
            </span>
          </div>
          <div class="flex items-center gap-3">
            <button v-if="store.unreadCount > 0"
                    @click="store.markAllRead()"
                    class="text-xs text-primary hover:underline">
              Mark all read
            </button>
            <button @click="store.isOpen = false"
                    class="p-1 text-app-text/40 hover:text-app-text transition-colors rounded">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Notification list -->
        <div class="flex-1 overflow-y-auto">
          <!-- Empty state -->
          <div v-if="!store.notifications.length"
               class="flex flex-col items-center justify-center h-full text-center px-6">
            <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mb-4">
              <svg class="w-7 h-7 text-app-text/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <p class="text-sm font-medium text-app-text/40">You're all caught up</p>
            <p class="text-xs text-app-text/30 mt-1">No notifications yet</p>
          </div>

          <!-- Notifications -->
          <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
            <div
              v-for="n in store.notifications"
              :key="n.id"
              @click="store.navigateTo(n)"
              class="group flex items-start gap-3 px-5 py-4 transition-colors cursor-pointer"
              :class="n.read_at
                ? 'hover:bg-gray-50 dark:hover:bg-gray-800/50'
                : 'bg-primary/5 dark:bg-primary/10 hover:bg-primary/10 border-l-2 border-primary'"
            >
              <!-- Colour dot -->
              <div class="mt-1.5 flex-shrink-0">
                <span class="block w-2 h-2 rounded-full"
                      :class="n.read_at ? 'bg-gray-200 dark:bg-gray-700' : (dotColour[n.colour] ?? 'bg-blue-500')" />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                  <p class="text-sm font-semibold text-app-text leading-tight">
                    {{ n.title }}
                  </p>
                  <button
                    @click.stop="store.dismiss(n.id)"
                    class="opacity-0 group-hover:opacity-100 flex-shrink-0 p-0.5 text-app-text/30 hover:text-app-text/60 transition-all"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>

                <p class="text-xs text-app-text/60 mt-0.5 leading-relaxed line-clamp-2">
                  {{ n.body }}
                </p>

                <div class="flex items-center gap-2 mt-1.5">
                  <span class="text-xs px-1.5 py-0.5 rounded font-medium"
                        :class="moduleColour[n.module] ?? moduleColour.Core">
                    {{ n.module }}
                  </span>
                  <span class="text-xs text-app-text/30">{{ n.created_at }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-800 flex-shrink-0">
          <a href="/profile/notifications"
             @click="store.isOpen = false"
             class="text-xs text-primary hover:underline">
            Manage notification preferences →
          </a>
        </div>
      </div>
    </Transition>

    <!-- Backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="store.isOpen"
           class="fixed inset-0 bg-black/20 z-40"
           @click="store.isOpen = false" />
    </Transition>
  </Teleport>
</template>
