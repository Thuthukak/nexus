<script setup>
import { ref, computed } from 'vue'
import { useForm }       from '@inertiajs/vue3'
import WizardLayout      from '@shared/layouts/WizardLayout.vue'

defineOptions({ layout: WizardLayout })

const props = defineProps({
  currentStep: { type: Number,  default: 5 },
  modules:     { type: Array,   required: true },
  selected:    { type: Array,   default: () => ['Core'] },
})

const form = useForm({
  modules: [...props.selected],
})

function toggleModule(name) {
  if (name === 'Core') return // Core always required
  const idx = form.modules.indexOf(name)
  if (idx === -1) {
    form.modules.push(name)
  } else {
    form.modules.splice(idx, 1)
  }
}

function isSelected(name) {
  return form.modules.includes(name)
}

function submit() {
  form.post('/install/step/5')
}
</script>

<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Select Modules</h1>
      <p class="text-gray-500 mt-1">
        Choose which modules to activate. Only licensed modules are shown.
      </p>
    </div>

    <div class="space-y-3 mb-8">
      <div v-for="mod in modules" :key="mod.name"
           @click="toggleModule(mod.name)"
           class="flex items-start gap-4 p-5 rounded-2xl border-2 transition-all"
           :class="[
             mod.required ? 'cursor-default' : 'cursor-pointer',
             isSelected(mod.name)
               ? 'border-primary bg-primary/5 dark:bg-primary/10'
               : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'
           ]">
        <div class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 mt-0.5 transition-colors"
             :class="isSelected(mod.name)
               ? 'border-primary bg-primary'
               : 'border-gray-300 dark:border-gray-600'">
          <svg v-if="isSelected(mod.name)" class="w-3 h-3 text-white"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <div class="flex-1">
          <div class="flex items-center gap-2">
            <p class="font-semibold text-gray-900 dark:text-white">{{ mod.name }}</p>
            <span v-if="mod.required"
                  class="text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-500 px-2 py-0.5 rounded-full">
              Required
            </span>
            <span v-if="mod.licensed"
                  class="text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-2 py-0.5 rounded-full">
              Licensed
            </span>
          </div>
          <p class="text-sm text-gray-500 mt-0.5">{{ mod.description }}</p>
        </div>
      </div>
    </div>

    <p v-if="form.errors.modules" class="text-sm text-red-500 mb-4">
      {{ form.errors.modules }}
    </p>

    <div class="flex justify-between">
      <a href="/install/step/4"
         class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors">
        ← Back
      </a>
      <button @click="submit" :disabled="form.processing"
              class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-40"
              style="background-color: var(--color-primary, #1E3A5F);">
        Continue →
      </button>
    </div>
  </div>
</template>
