<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  user_id: { type: Number, required: true },
  email:   { type: String, required: true },
  name:    { type: String, required: true },
  app:     { type: Object, required: true },
})

const form = useForm({
  password:              '',
  password_confirmation: '',
})

function submit() {
  form.post(window.location.href)
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col justify-center items-center px-4"
       style="font-family: Arial, sans-serif;">
    <div class="w-full max-w-sm">
      <div class="text-center mb-8">
        <img v-if="app.logo_url" :src="app.logo_url" class="h-10 w-auto object-contain mx-auto mb-3" />
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ app.name }}</h1>
        <p class="text-sm text-gray-500 mt-1">Client Portal</p>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm">
        <div class="mb-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Set your password</h2>
          <p class="text-sm text-gray-500 mt-1">
            Welcome, <strong>{{ name }}</strong>! Create a password to access the client portal.
          </p>
          <p class="text-xs text-gray-400 mt-1">Signing in as {{ email }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
            <input v-model="form.password" type="password" required minlength="8" autofocus
                   class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
            <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
            <input v-model="form.password_confirmation" type="password" required
                   class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
          </div>

          <button type="submit" :disabled="form.processing"
                  class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity disabled:opacity-60"
                  style="background-color: var(--color-primary, #1E3A5F);">
            {{ form.processing ? 'Setting up…' : 'Create Account & Sign In' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
