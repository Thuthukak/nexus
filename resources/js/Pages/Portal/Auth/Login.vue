<script setup>
import { useForm } from '@inertiajs/vue3'

defineProps({
  app: { type: Object, required: true },
})

const form = useForm({
  email:    '',
  password: '',
  remember: false,
})

function submit() {
  form.post('/portal/login')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col justify-center items-center px-4"
       style="font-family: Arial, sans-serif;">
    <div class="w-full max-w-sm">
      <!-- Logo -->
      <div class="text-center mb-8">
        <img v-if="app.logo_url" :src="app.logo_url"
             class="h-10 w-auto object-contain mx-auto mb-3" />
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ app.name }}</h1>
        <p class="text-sm text-gray-500 mt-1">Client Portal</p>
      </div>

      <!-- Form -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Sign in to your account</h2>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email address</label>
            <input v-model="form.email" type="email" required autofocus
                   class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                   style="--tw-ring-color: var(--color-primary);" />
            <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
            <input v-model="form.password" type="password" required
                   class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
            <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <input v-model="form.remember" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              Remember me
            </label>
          </div>

          <button type="submit" :disabled="form.processing"
                  class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity disabled:opacity-60"
                  style="background-color: var(--color-primary, #1E3A5F);">
            {{ form.processing ? 'Signing in…' : 'Sign In' }}
          </button>
        </form>
      </div>

      <p class="text-center text-xs text-gray-400 mt-6">
        Having trouble? Contact your account manager.
      </p>
    </div>
  </div>
</template>
