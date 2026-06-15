<script setup>
import { ref, computed }      from 'vue'
import { useForm }  from '@inertiajs/vue3'
import PasswordInput from '@shared/components/form/PasswordInput.vue'

const props = defineProps({
  app: { type: Object, default: () => ({ name: 'Nexus' }) },
})

const form = useForm({
  email:    '',
  password: '',
  remember: false,
})

const emailTouched = ref(false)

function submit() {
  emailTouched.value = true
  form.post('/login', {
    onError: () => form.reset('password'),
  })
}

const emailError = computed(() => {
  if (! emailTouched.value) return null
  if (! form.email) return 'Email is required.'
  if (! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) return 'Please enter a valid email address.'
  return form.errors.email ?? null
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col justify-center items-center px-4"
      style="font-family: Arial, sans-serif;">
    <div class="w-full max-w-sm">

      <!-- Logo / app name -->
      <div class="text-center mb-8">
        <img v-if="$page.props.app?.logo_url"
            :src="$page.props.app.logo_url"
            class="h-12 w-auto object-contain mx-auto mb-3" 
        />
        <div v-else
            class="w-12 h-12 rounded-2xl bg-primary flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-primary-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ $page.props.app?.name ?? $page.props.appName ?? '' }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm">
        <form @submit.prevent="submit" class="space-y-5" novalidate>

          <!-- Email -->
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Email address
            </label>
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              autofocus
              @blur="emailTouched = true"
              class="w-full px-3 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2
                     focus:ring-primary/50 bg-white dark:bg-gray-800 text-gray-900 dark:text-white transition-colors"
              :class="emailError
                ? 'border-red-400 dark:border-red-600 bg-red-50 dark:bg-red-900/10'
                : 'border-gray-300 dark:border-gray-600'"
            />
            <p v-if="emailError" class="text-xs text-red-500 flex items-center gap-1">
              <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                  clip-rule="evenodd" />
              </svg>
              {{ emailError }}
            </p>
          </div>

          <!-- Password -->
          <div class="flex flex-col gap-1">
            <div class="flex items-center justify-between">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
              <a href="/forgot-password"
                 class="text-xs text-primary hover:underline font-medium">
                Forgot password?
              </a>
            </div>
            <PasswordInput
              v-model="form.password"
              label=""
              :error="form.errors.password"
              autocomplete="current-password"
            />
            <p v-if="form.errors.email && !emailError"
               class="text-xs text-red-500 flex items-center gap-1">
              <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                  clip-rule="evenodd" />
              </svg>
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Remember me -->
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input v-model="form.remember" type="checkbox"
                   class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
            <span class="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
          </label>

          <button type="submit"
                  :disabled="form.processing"
                  class="w-full py-2.5 rounded-xl font-semibold text-sm text-primary-text
                         disabled:opacity-60 transition-opacity"
                  style="background-color: var(--color-primary, #1E3A5F);">
            {{ form.processing ? 'Signing in…' : 'Sign In' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
