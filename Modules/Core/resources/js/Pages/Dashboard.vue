<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import { usePage } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })

const page = usePage()
const user = page.props.auth?.user
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">Dashboard</h1>
      <p class="text-sm text-app-text/60 mt-1">
        Welcome back, {{ user?.name ?? 'there' }}
      </p>
    </div>

    <div class="bg-surface rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
      <h2 class="text-lg font-semibold text-app-text mb-2">Platform ready</h2>
      <p class="text-sm text-app-text/60 leading-relaxed">
        Nexus is up and running. Modules can be activated from the Module Manager.
      </p>
      <div class="mt-4">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
          <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
          Core active
        </span>
      </div>
    </div>
  </div>
</template>
