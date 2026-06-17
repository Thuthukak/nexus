<script setup>
import WizardLayout from '@shared/layouts/WizardLayout.vue'

defineOptions({ layout: WizardLayout })

defineProps({
  currentStep:   { type: Number, default: 7 },
  adminEmail:    { type: String, required: true },
  activeModules: { default: () => [] },
})

const moduleList = computed(() => {
  if (Array.isArray(props.activeModules)) return props.activeModules
  if (typeof props.activeModules === 'string' && props.activeModules.trim())
    return props.activeModules.split(',').map(s => s.trim())
  return []
})

</script>

<template>
  <div class="text-center">
    <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6"
          style="background-color: var(--color-primary, #1E3A5F);">
      <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">
      Nexus is Ready
    </h1>
    <p class="text-gray-500 mb-8 max-w-md mx-auto">
      Your installation is complete. You can now log in with your admin account
      and start using Nexus.
    </p>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 mb-8 text-left max-w-md mx-auto">
      <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">
        Installation Summary
      </h2>
      <dl class="space-y-3">
        <div class="flex justify-between text-sm">
          <dt class="text-gray-500">Admin Email</dt>
          <dd class="font-medium text-gray-900 dark:text-white font-mono">{{ adminEmail }}</dd>
        </div>
        <div class="flex justify-between text-sm">
          <dt class="text-gray-500">Active Modules</dt>
          <dd class="font-medium text-gray-900 dark:text-white">
            {{ moduleList.join(', ') || '—' }}
          </dd>
        </div>
        <div class="flex justify-between text-sm">
          <dt class="text-gray-500">Status</dt>
          <dd class="font-medium text-green-600">Installed ✓</dd>
        </div>
      </dl>
    </div>

    <a href="/login"
       class="inline-flex items-center gap-2 px-8 py-3 rounded-xl text-white font-semibold text-sm hover:opacity-90 transition-opacity"
       style="background-color: var(--color-primary, #1E3A5F);">
      Go to Login →
    </a>

    <p class="text-xs text-gray-400 mt-6">
      Keep your licence key in a safe place.
      You can update it later from the Module Manager.
    </p>
  </div>
</template>
