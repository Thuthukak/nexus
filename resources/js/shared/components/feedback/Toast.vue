<script setup>
import { computed } from 'vue'

const props = defineProps({
  id:      { type: String,  required: true },
  type:    { type: String,  default: 'info' },
  title:   { type: String,  required: true },
  message: { type: String,  default: '' },
})

const emit = defineEmits(['dismiss'])

const config = computed(() => ({
  success: {
    bar:  'bg-green-500',
    icon: 'text-green-500',
    path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  },
  error: {
    bar:  'bg-red-500',
    icon: 'text-red-500',
    path: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
  },
  warning: {
    bar:  'bg-yellow-500',
    icon: 'text-yellow-500',
    path: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
  },
  info: {
    bar:  'bg-blue-500',
    icon: 'text-blue-500',
    path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  },
}[props.type] ?? {
  bar:  'bg-blue-500',
  icon: 'text-blue-500',
  path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
}))
</script>

<template>
  <div class="w-80 bg-surface rounded-xl shadow-lg border border-gray-100 dark:border-gray-800 overflow-hidden pointer-events-auto">
    <!-- Colour bar -->
    <div class="h-1 w-full" :class="config.bar" />

    <div class="flex items-start gap-3 p-4">
      <!-- Icon -->
      <svg class="w-5 h-5 flex-shrink-0 mt-0.5" :class="config.icon"
           fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="config.path" />
      </svg>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-app-text">{{ title }}</p>
        <p v-if="message" class="text-xs text-app-text/60 mt-0.5 leading-relaxed">
          {{ message }}
        </p>
      </div>

      <!-- Dismiss -->
      <button
        @click="$emit('dismiss', id)"
        class="flex-shrink-0 p-1 rounded text-app-text/30 hover:text-app-text/60 transition-colors"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </div>
</template>
