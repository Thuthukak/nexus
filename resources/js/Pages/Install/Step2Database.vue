<script setup>
import { ref }       from 'vue'
import { useForm }   from '@inertiajs/vue3'
import axios         from 'axios'
import WizardLayout  from '@shared/layouts/WizardLayout.vue'

defineOptions({ layout: WizardLayout })

const props = defineProps({
  currentStep: { type: Number, default: 2 },
  saved:       { type: Object, default: () => ({}) },
})

const form = useForm({
  host:     props.saved.host     || '127.0.0.1',
  port:     props.saved.port     || '3306',
  database: props.saved.database || 'nexus',
  username: props.saved.username || 'root',
  password: '',
})

const testResult = ref(null)
const testing    = ref(false)

async function testConnection() {
  testing.value    = true
  testResult.value = null
  try {
    const { data } = await axios.get('/install/check-db', {
      params: {
        host:     form.host,
        port:     form.port,
        database: form.database,
        username: form.username,
        password: form.password,
      },
    })
    testResult.value = data
  } catch {
    testResult.value = { success: false, message: 'Request failed' }
  } finally {
    testing.value = false
  }
}

function submit() {
  form.post('/install/step/2')
}
</script>

<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Database Setup</h1>
      <p class="text-gray-500 mt-1">Enter your database connection details.</p>
    </div>

    <form @submit.prevent="submit"
          class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 space-y-4 mb-6">

      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Host</label>
          <input v-model="form.host" type="text"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                 style="--tw-ring-color: var(--color-primary, #1E3A5F);" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Port</label>
          <input v-model="form.port" type="text"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
        </div>
      </div>

      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Database Name</label>
        <input v-model="form.database" type="text"
               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
          <input v-model="form.username" type="text"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
          <input v-model="form.password" type="password"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                 placeholder="Leave blank if none" />
        </div>
      </div>

      <p v-if="form.errors.connection" class="text-sm text-red-500">
        {{ form.errors.connection }}
      </p>

      <!-- Test result -->
      <div v-if="testResult"
           class="flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-medium"
           :class="testResult.success
             ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400'
             : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400'">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            :d="testResult.success ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'" />
        </svg>
        {{ testResult.message }}
      </div>
    </form>

    <div class="flex justify-between">
      <button @click="testConnection"
              :disabled="testing"
              class="px-5 py-2.5 rounded-xl text-sm font-semibold border-2 transition-colors disabled:opacity-50"
              style="border-color: var(--color-primary, #1E3A5F); color: var(--color-primary, #1E3A5F);">
        {{ testing ? 'Testing…' : 'Test Connection' }}
      </button>
      <button @click="submit"
              :disabled="form.processing"
              class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity disabled:opacity-40"
              style="background-color: var(--color-primary, #1E3A5F);">
        Save & Continue →
      </button>
    </div>
  </div>
</template>
