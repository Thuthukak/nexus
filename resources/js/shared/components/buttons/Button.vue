<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: v => ['primary', 'secondary', 'danger', 'ghost'].includes(v),
  },
  size: {
    type: String,
    default: 'md',
    validator: v => ['sm', 'md', 'lg'].includes(v),
  },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  type:    { type: String,  default: 'button' },
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center font-medium rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
    :class="{
      // Sizes
      'px-3 py-1.5 text-xs gap-1.5': size === 'sm',
      'px-4 py-2 text-sm gap-2':     size === 'md',
      'px-5 py-2.5 text-base gap-2': size === 'lg',
      // Variants
      'bg-primary text-primary-text hover:opacity-90 focus:ring-primary/50':                       variant === 'primary',
      'bg-secondary/10 text-secondary hover:bg-secondary/20 focus:ring-secondary/50':              variant === 'secondary',
      'bg-red-500 text-white hover:bg-red-600 focus:ring-red-500/50':                              variant === 'danger',
      'bg-transparent text-app-text hover:bg-gray-100 dark:hover:bg-gray-800 focus:ring-gray-300': variant === 'ghost',
    }"
  >
    <!-- Spinner -->
    <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
    </svg>
    <slot />
  </button>
</template>
