<script setup>
import { usePage } from '@inertiajs/vue3'
import { useThemeStore } from '@shared/stores/useThemeStore.js'

const page = usePage()
useThemeStore() // initialise dark mode on mount
</script>

<template>
  <div class="min-h-screen bg-background flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Logo / App name -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-primary tracking-tight">
          {{ page.props.app.name }}
        </h1>
        <p class="text-sm text-app-text/60 mt-1">Business Platform</p>
      </div>

      <!-- Card -->
      <div class="bg-surface rounded-2xl shadow-lg p-8">
        <slot />
      </div>
    </div>
  </div>
</template>
