<script setup>
defineProps({
  type: {
    type: String,
    default: 'neutral',
    validator: v => ['success', 'warning', 'danger', 'info', 'neutral'].includes(v),
  },
  dot: { type: Boolean, default: false },
})
</script>

<template>
  <span
    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="{
      'bg-green-100  text-green-700  dark:bg-green-900/30  dark:text-green-400':  type === 'success',
      'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': type === 'warning',
      'bg-red-100    text-red-700    dark:bg-red-900/30    dark:text-red-400':    type === 'danger',
      'bg-blue-100   text-blue-700   dark:bg-blue-900/30   dark:text-blue-400':   type === 'info',
      'bg-gray-100   text-gray-600   dark:bg-gray-800      dark:text-gray-400':   type === 'neutral',
    }"
  >
    <span
      v-if="dot"
      class="w-1.5 h-1.5 rounded-full"
      :class="{
        'bg-green-500':  type === 'success',
        'bg-yellow-500': type === 'warning',
        'bg-red-500':    type === 'danger',
        'bg-blue-500':   type === 'info',
        'bg-gray-400':   type === 'neutral',
      }"
    />
    <slot />
  </span>
</template>
