<script setup>
import { ref, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import WizardLayout from '@shared/layouts/WizardLayout.vue'
import axios from 'axios'

defineOptions({ layout: WizardLayout })
defineProps({
  currentStep: { type: Number,  default: 3 },
  dbSaved:     { type: Boolean, default: false },
})

const form     = useForm({})
const running  = ref(false)
const done     = ref(false)
const error    = ref(null)
const logs     = ref([])

function addLog(msg) {
  logs.value.push({ text: msg, time: new Date().toLocaleTimeString() })
}

async function runMigrations() {
  running.value = true
  error.value   = null
  logs.value    = []

  addLog('Starting database migrations…')

  try {
    addLog('Running Core migrations…')
    await axios.post('/install/step/3')
    addLog('Migrations complete.')
    addLog('Seeding base data…')
    addLog('Done.')
    done.value = true
  } catch (err) {
    error.value = err.response?.data?.errors?.migration ?? 'Migration failed. Check server logs.'
    addLog('Error: ' + error.value)
  } finally {
    running.value = false
  }
}

function proceed() {
  router.get('/install/step/4')
}
</script>

<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Run Migrations</h1>
      <p class="text-gray-500 mt-1">
        Setting up the database tables. This may take a moment.
      </p>
    </div>

    <div v-if="dbSaved"
         class="flex items-center gap-2 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl mb-4 text-sm text-green-700 dark:text-green-400">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      Database credentials saved successfully. Ready to run migrations.
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
      <!-- Terminal-style log output -->
      <div class="bg-gray-950 px-5 py-4 min-h-40 font-mono text-xs space-y-1">
        <p v-if="!logs.length" class="text-gray-500">
          Ready to run migrations. Click the button below to begin.
        </p>
        <p v-for="(log, i) in logs" :key="i" class="text-green-400">
          <span class="text-gray-500">{{ log.time }}</span>
          &nbsp;{{ log.text }}
        </p>
        <span v-if="running" class="inline-block w-2 h-4 bg-green-400 animate-pulse" />
      </div>

      <div v-if="done"
           class="flex items-center gap-2 px-5 py-3 bg-green-50 dark:bg-green-900/20 border-t border-green-200 dark:border-green-800">
        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span class="text-sm font-medium text-green-700 dark:text-green-400">
          Database ready.
        </span>
      </div>

      <div v-if="error"
           class="flex items-center gap-2 px-5 py-3 bg-red-50 dark:bg-red-900/20 border-t border-red-200">
        <span class="text-sm text-red-600">{{ error }}</span>
      </div>
    </div>

    <div class="flex justify-between">
      <button v-if="!done" @click="runMigrations"
              :disabled="running"
              class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50"
              style="background-color: var(--color-primary, #1E3A5F);">
        {{ running ? 'Running…' : 'Run Migrations' }}
      </button>
      <button v-if="done" @click="proceed"
              class="ml-auto px-6 py-2.5 rounded-xl text-sm font-semibold text-white"
              style="background-color: var(--color-primary, #1E3A5F);">
        Continue →
      </button>
    </div>
  </div>
</template>
