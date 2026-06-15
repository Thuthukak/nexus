<script setup>
import { ref, computed } from 'vue'
import { useForm }       from '@inertiajs/vue3'
import PasswordInput     from '@shared/components/form/PasswordInput.vue'

const props = defineProps({
  user_id: { type: Number, required: true },
  name:    { type: String, required: true },
  email:   { type: String, required: true },
  app:     { type: Object, required: true },
})

const form = useForm({
  password:              '',
  password_confirmation: '',
})

const confirmTouched = ref(false)

const confirmError = computed(() => {
  if (! confirmTouched.value || ! form.password_confirmation) return null
  if (form.password !== form.password_confirmation) return 'Passwords do not match.'
  return form.errors.password_confirmation ?? null
})

function submit() {
  confirmTouched.value = true
  if (form.password !== form.password_confirmation) return
  form.post(window.location.href)
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col justify-center items-center px-4"
       style="font-family: Arial, sans-serif;">
    <div class="w-full max-w-sm">

      <div class="text-center mb-8">
        <img v-if="app.logo_url" :src="app.logo_url" class="h-16 w-auto object-contain mx-auto mb-3" />
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3"
             style="background-color: var(--color-primary, #1E3A5F);">
          <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Set Your Password</h1>
        <p class="text-sm text-gray-500 mt-1">Welcome, <strong>{{ name }}</strong>!</p>
        <p class="text-xs text-gray-400 mt-0.5">Signing in as {{ email }}</p>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm">
        <form @submit.prevent="submit" class="space-y-5" novalidate>

          <PasswordInput
            v-model="form.password"
            label="Create Password"
            autocomplete="new-password"
            :show-strength="true"
            :error="form.errors.password"
          />

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Confirm Password
            </label>
            <PasswordInput
              v-model="form.password_confirmation"
              label=""
              autocomplete="new-password"
              :show-strength="false"
              :error="confirmError"
              @blur="confirmTouched = true"
            />
            <p v-if="confirmError" class="text-xs text-red-500">{{ confirmError }}</p>
          </div>

          <button type="submit"
                  :disabled="form.processing"
                  class="w-full py-2.5 rounded-xl font-semibold text-sm text-white
                         disabled:opacity-60 transition-opacity"
                  style="background-color: var(--color-primary, #1E3A5F);">
            {{ form.processing ? 'Setting up…' : 'Create Account & Sign In' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
