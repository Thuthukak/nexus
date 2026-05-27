<script setup>
import { ref }      from 'vue'
import { useForm }  from '@inertiajs/vue3'
import WizardLayout from '@shared/layouts/WizardLayout.vue'

defineOptions({ layout: WizardLayout })

const props = defineProps({
  currentStep: { type: Number, default: 4 },
  licenceData: { type: Object, default: null },
  isDev:       { type: Boolean, default: false },
})

const form = useForm({
  licence_key:   '',
  skip_licence:  false,
})

function submit() {
  form.post('/install/step/4')
}

function skipDev() {
  form.skip_licence = true
  form.post('/install/step/4')
}
</script>

<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Licence Key</h1>
      <p class="text-gray-500 mt-1">
        Enter your Nexus licence key to unlock your purchased modules.
      </p>
    </div>

    <!-- Already validated -->
    <div v-if="licenceData?.valid"
         class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl p-6 mb-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <div>
          <p class="font-semibold text-green-800 dark:text-green-400">Licence Valid</p>
          <p class="text-xs text-green-600 dark:text-green-500">
            {{ licenceData.dev ? 'Development licence' : `Licensed to ${licenceData.licensee}` }}
          </p>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3 text-sm">
        <div v-if="!licenceData.dev">
          <span class="text-green-600 text-xs uppercase tracking-wide">Expires</span>
          <p class="font-medium text-green-800 dark:text-green-300">{{ licenceData.expires_at }}</p>
        </div>
        <div>
          <span class="text-green-600 text-xs uppercase tracking-wide">Licensed Modules</span>
          <p class="font-medium text-green-800 dark:text-green-300">
            {{ licenceData.modules?.join(', ') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Dev bypass -->
    <div v-if="isDev && !licenceData?.valid"
         class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 mb-6">
      <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-400 mb-1">
        Development Mode
      </p>
      <p class="text-xs text-yellow-700 dark:text-yellow-500 mb-3">
        Running locally — you can skip licence validation and all modules will be available.
      </p>
      <button @click="skipDev"
              :disabled="form.processing"
              class="px-4 py-2 text-xs font-semibold rounded-lg border-2 border-yellow-400 text-yellow-700 hover:bg-yellow-100 transition-colors">
        Skip — Use Dev Licence
      </button>
    </div>

    <!-- Licence key input -->
    <div v-if="!licenceData?.valid"
         class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
      <div class="flex flex-col gap-2">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
          Licence Key
        </label>
        <textarea
          v-model="form.licence_key"
          rows="4"
          placeholder="Paste your licence key here…"
          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-mono focus:outline-none focus:ring-2 focus:border-transparent resize-none"
        />
        <p v-if="form.errors.licence_key" class="text-xs text-red-500">
          {{ form.errors.licence_key }}
        </p>
        <p class="text-xs text-gray-400">
          Your licence key was emailed to you after purchase.
          It is a long string starting with a base64 encoded payload.
        </p>
      </div>
    </div>

    <div class="flex justify-between">
      <a href="/install/step/3"
         class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors">
        ← Back
      </a>
      <button v-if="licenceData?.valid"
              onclick="window.location='/install/step/5'"
              class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white"
              style="background-color: var(--color-primary, #1E3A5F);">
        Continue →
      </button>
      <button v-else @click="submit"
              :disabled="form.processing || !form.licence_key"
              class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-40"
              style="background-color: var(--color-primary, #1E3A5F);">
        Validate & Continue →
      </button>
    </div>
  </div>
</template>
