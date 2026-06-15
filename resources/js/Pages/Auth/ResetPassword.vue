<script setup>
import { ref, computed } from 'vue'
import { useForm }       from '@inertiajs/vue3'
import PasswordInput     from '@shared/components/form/PasswordInput.vue'

const props = defineProps({
  token: { type: String, required: true },
  email: { type: String, default: '' },
})

const form = useForm({
  token:                 props.token,
  email:                 props.email,
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
  form.post('/reset-password')
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
              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reset Password</h1>
        <p class="text-sm text-gray-500 mt-1">Choose a new password for <strong>{{ email }}</strong></p>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm">
        <form @submit.prevent="submit" class="space-y-5" novalidate>

          <p v-if="form.errors.email" class="text-sm text-red-500 bg-red-50 dark:bg-red-900/20
             border border-red-200 dark:border-red-800 rounded-lg px-4 py-3">
            {{ form.errors.email }}
          </p>

          <PasswordInput
            v-model="form.password"
            label="New Password"
            autocomplete="new-password"
            :show-strength="true"
            :error="form.errors.password"
          />

          <div class="flex flex-col gap-1">
            <PasswordInput
              v-model="form.password_confirmation"
              label="Confirm New Password"
              autocomplete="new-password"
              :show-strength="false"
              :error="confirmError"
              @blur="confirmTouched = true"
            />
          </div>

          <button type="submit"
                  :disabled="form.processing"
                  class="w-full py-2.5 rounded-xl font-semibold text-sm text-white
                         disabled:opacity-60 transition-opacity"
                  style="background-color: var(--color-primary, #1E3A5F);">
            {{ form.processing ? 'Resetting…' : 'Reset Password' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
