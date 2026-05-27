<script setup>
import { useForm }  from '@inertiajs/vue3'
import WizardLayout from '@shared/layouts/WizardLayout.vue'

defineOptions({ layout: WizardLayout })
defineProps({ currentStep: { type: Number, default: 6 } })

const form = useForm({
  name:                  '',
  email:                 '',
  password:              '',
  password_confirmation: '',
})

function submit() {
  form.post('/install/step/6')
}
</script>

<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Admin Account</h1>
      <p class="text-gray-500 mt-1">
        This will be the Super Admin account for your Nexus installation.
      </p>
    </div>

    <form @submit.prevent="submit"
          class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 space-y-4 mb-6">
      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
        <input v-model="form.name" type="text" required
               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
      </div>

      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
        <input v-model="form.email" type="email" required
               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
        <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
          <input v-model="form.password" type="password" required
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
          <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" required
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
        </div>
      </div>

      <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg px-4 py-3 text-xs text-blue-700 dark:text-blue-400">
        Use a strong password. This account has full access to all platform settings.
      </div>
    </form>

    <div class="flex justify-between">
      <a href="/install/step/5"
         class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors">
        ← Back
      </a>
      <button @click="submit" :disabled="form.processing"
              class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-40"
              style="background-color: var(--color-primary, #1E3A5F);">
        Create Account & Finish →
      </button>
    </div>
  </div>
</template>
