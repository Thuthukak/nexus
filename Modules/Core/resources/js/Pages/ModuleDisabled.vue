<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })
defineProps({ module: { type: String, required: true } })
</script>

<template>
  <div class="max-w-lg mx-auto text-center py-16">
    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-6">
      <svg class="w-8 h-8 text-app-text/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
      </svg>
    </div>
    <h1 class="text-xl font-bold text-app-text mb-2">Module Not Active</h1>
    <p class="text-sm text-app-text/60 mb-6">
      The <strong>{{ module }}</strong> module is not enabled on this installation.
      Contact your administrator to activate it.
    </p>
    <a href="/dashboard"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
      Back to Dashboard
    </a>
  </div>
</template>
