<script setup>
import { computed } from 'vue'
import { usePage }  from '@inertiajs/vue3'

defineProps({
  currentStep: { type: Number, default: 1 },
  totalSteps:  { type: Number, default: 7 },
})

const steps = [
  { number: 1, label: 'Environment' },
  { number: 2, label: 'Database' },
  { number: 3, label: 'Migrations' },
  { number: 4, label: 'Licence' },
  { number: 5, label: 'Modules' },
  { number: 6, label: 'Admin' },
  { number: 7, label: 'Complete' },
]
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950" style="font-family: Arial, sans-serif;">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-4">
      <div class="max-w-3xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center"
               style="background-color: var(--color-primary, #1E3A5F);">
            <span class="text-white font-bold text-sm">N</span>
          </div>
          <span class="font-bold text-gray-900 dark:text-white">Nexus</span>
          <span class="text-gray-400 text-sm">— Installation</span>
        </div>
        <span class="text-xs text-gray-400">Step {{ currentStep }} of {{ totalSteps }}</span>
      </div>
    </div>

    <!-- Step indicator -->
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-3">
      <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-0">
          <template v-for="(step, i) in steps" :key="step.number">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 transition-colors"
                   :class="{
                     'text-white':                          step.number <= currentStep,
                     'text-gray-400 bg-gray-100 dark:bg-gray-800': step.number > currentStep,
                   }"
                   :style="step.number <= currentStep
                     ? { backgroundColor: 'var(--color-primary, #1E3A5F)' }
                     : {}">
                <svg v-if="step.number < currentStep" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
                <span v-else>{{ step.number }}</span>
              </div>
              <span class="text-xs font-medium hidden sm:block transition-colors"
                    :class="step.number <= currentStep
                      ? 'text-gray-900 dark:text-white'
                      : 'text-gray-400'">
                {{ step.label }}
              </span>
            </div>
            <div v-if="i < steps.length - 1"
                 class="flex-1 h-px mx-2 transition-colors"
                 :class="step.number < currentStep
                   ? 'bg-primary'
                   : 'bg-gray-200 dark:bg-gray-700'" />
          </template>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-3xl mx-auto px-4 py-8">
      <slot />
    </div>
  </div>
</template>
