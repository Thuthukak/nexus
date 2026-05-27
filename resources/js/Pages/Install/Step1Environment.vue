<script setup>
import { useForm } from '@inertiajs/vue3'
import WizardLayout from '@shared/layouts/WizardLayout.vue'

defineOptions({ layout: WizardLayout })

const props = defineProps({
  checks:     { type: Array,   required: true },
  allPassed:  { type: Boolean, required: true },
  currentStep:{ type: Number,  default: 1 },
})

const form = useForm({})

function proceed() {
  form.post('/install/step/1')
}

const statusIcon = {
  pass: { icon: 'M5 13l4 4L19 7', color: 'text-green-500' },
  warn: { icon: 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z', color: 'text-yellow-500' },
  fail: { icon: 'M6 18L18 6M6 6l12 12', color: 'text-red-500' },
}
</script>

<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Environment Check</h1>
      <p class="text-gray-500 mt-1">Verifying your server meets all requirements.</p>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
      <div class="divide-y divide-gray-100 dark:divide-gray-800">
        <div v-for="check in checks" :key="check.label"
             class="flex items-center justify-between px-5 py-3.5">
          <div class="flex items-center gap-3">
            <svg class="w-4 h-4 flex-shrink-0"
                 :class="statusIcon[check.status]?.color"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    :d="statusIcon[check.status]?.icon" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ check.label }}
            </span>
          </div>
          <span class="text-xs font-mono"
                :class="{
                  'text-green-600': check.status === 'pass',
                  'text-yellow-600': check.status === 'warn',
                  'text-red-600': check.status === 'fail',
                }">
            {{ check.value }}
          </span>
        </div>
      </div>
    </div>

    <div v-if="!allPassed"
         class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
      <p class="text-sm font-semibold text-red-700 dark:text-red-400 mb-1">
        Requirements not met
      </p>
      <p class="text-xs text-red-600 dark:text-red-500">
        Fix all failed checks above before continuing. Install missing PHP extensions
        and ensure storage folders are writable.
      </p>
    </div>

    <div class="flex justify-end">
      <button @click="proceed"
              :disabled="!allPassed || form.processing"
              class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity disabled:opacity-40"
              style="background-color: var(--color-primary, #1E3A5F);">
        Continue →
      </button>
    </div>
  </div>
</template>
