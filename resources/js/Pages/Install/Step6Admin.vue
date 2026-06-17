<script setup>
import { ref, computed } from 'vue'
import { useForm }        from '@inertiajs/vue3'
import WizardLayout       from '@shared/layouts/WizardLayout.vue'
import PasswordInput      from '@shared/components/form/PasswordInput.vue'

defineOptions({ layout: WizardLayout })

defineProps({
  currentStep: { type: Number, default: 6 },
})

const form = useForm({
  name:                  '',
  email:                 '',
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
  form.post('/install/step/6')
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h2 class="text-xl font-bold text-app-text">Create Admin Account</h2>
      <p class="text-sm text-app-text/60 mt-1">
        This will be your Super Admin account. Keep these credentials safe.
      </p>
    </div>

    <form @submit.prevent="submit" class="space-y-5" novalidate>

      <!-- Name -->
      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Full Name</label>
        <input v-model="form.name" type="text" required autofocus
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600
                      bg-background text-app-text text-sm focus:outline-none
                      focus:ring-2 focus:ring-primary/50"
               :class="form.errors.name ? 'border-red-400' : ''" />
        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
      </div>

      <!-- Email -->
      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Email Address</label>
        <input v-model="form.email" type="email" required
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600
                      bg-background text-app-text text-sm focus:outline-none
                      focus:ring-2 focus:ring-primary/50"
               :class="form.errors.email ? 'border-red-400' : ''" />
        <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
      </div>

      <!-- Password with strength meter -->
      <PasswordInput
        v-model="form.password"
        label="Password"
        autocomplete="new-password"
        :show-strength="true"
        :error="form.errors.password"
      />

      <!-- Confirm password -->
      <div class="flex flex-col gap-1">
        <PasswordInput
          v-model="form.password_confirmation"
          label="Confirm Password"
          autocomplete="new-password"
          :show-strength="false"
          :error="confirmError"
          @blur="confirmTouched = true"
        />
      </div>

      <!-- Security note -->
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800
                  rounded-xl px-4 py-3">
        <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">
          🔒 <strong>Super Admin</strong> has full access to all modules and settings.
          Store these credentials in a secure password manager.
        </p>
      </div>

      <button type="submit"
              :disabled="form.processing"
              class="w-full py-3 rounded-xl font-semibold text-sm text-primary-text
                     disabled:opacity-60 transition-opacity"
              style="background-color: var(--color-primary, #1E3A5F);">
        {{ form.processing ? 'Creating account…' : 'Create Admin Account →' }}
      </button>
    </form>
  </div>
</template>
