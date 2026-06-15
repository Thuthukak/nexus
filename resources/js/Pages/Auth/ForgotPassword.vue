<script setup>
import { ref }     from 'vue'
import { useForm } from '@inertiajs/vue3'

defineProps({
  status: { type: String, default: null },
})

const form         = useForm({ email: '' })
const emailTouched = ref(false)
const submitted    = ref(false)

function submit() {
  emailTouched.value = true
  form.post('/forgot-password', {
    onSuccess: () => { submitted.value = true },
  })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col justify-center items-center px-4"
       style="font-family: Arial, sans-serif;">
    <div class="w-full max-w-sm">

      <div class="text-center mb-8">
        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-2xl
                    flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Forgot Password?</h1>
        <p class="text-sm text-gray-500 mt-1">
          Enter your email and we'll send you a reset link.
        </p>
      </div>

      <!-- Success state -->
      <div v-if="submitted"
           class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800
                  rounded-2xl p-6 text-center">
        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full
                    flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <p class="font-semibold text-green-800 dark:text-green-400 mb-1">Check your email</p>
        <p class="text-sm text-green-700 dark:text-green-500">
          If <strong>{{ form.email }}</strong> is registered, a password reset
          link has been sent. Check your spam folder if you don't see it.
        </p>
        <a href="/login"
           class="inline-block mt-4 text-sm text-primary hover:underline font-medium">
          ← Back to login
        </a>
      </div>

      <!-- Form -->
      <div v-else
           class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm">
        <form @submit.prevent="submit" class="space-y-5" novalidate>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Email address
            </label>
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              autofocus
              @blur="emailTouched = true"
              class="w-full px-3 py-2.5 rounded-lg border text-sm focus:outline-none
                     focus:ring-2 focus:ring-primary/50 bg-white dark:bg-gray-800
                     text-gray-900 dark:text-white transition-colors"
              :class="(emailTouched && !form.email) || form.errors.email
                ? 'border-red-400 dark:border-red-600 bg-red-50 dark:bg-red-900/10'
                : 'border-gray-300 dark:border-gray-600'"
            />
            <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
            <p v-else-if="emailTouched && !form.email" class="text-xs text-red-500">
              Email is required.
            </p>
          </div>

          <button type="submit"
                  :disabled="form.processing"
                  class="w-full py-2.5 rounded-xl font-semibold text-sm text-white
                         disabled:opacity-60 transition-opacity"
                  style="background-color: var(--color-primary, #1E3A5F);">
            {{ form.processing ? 'Sending…' : 'Send Reset Link' }}
          </button>

          <div class="text-center">
            <a href="/login" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
              ← Back to login
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
