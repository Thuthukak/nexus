<script setup>
import { ref, computed } from 'vue'
import { useForm }       from '@inertiajs/vue3'
import AppLayout         from '@shared/layouts/AppLayout.vue'
import Input             from '@shared/components/form/Input.vue'
import Button            from '@shared/components/buttons/Button.vue'
import Badge             from '@shared/components/display/Badge.vue'
import PasswordInput     from '@shared/components/form/PasswordInput.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  user: { type: Object, required: true },
})

const profileForm = useForm({
  name:  props.user.name,
  email: props.user.email,
})

const passwordForm = useForm({
  current_password:      '',
  password:              '',
  password_confirmation: '',
})

const confirmTouched = ref(false)

const confirmError = computed(() => {
  if (! confirmTouched.value || ! passwordForm.password_confirmation) return null
  if (passwordForm.password !== passwordForm.password_confirmation) return 'Passwords do not match.'
  return passwordForm.errors.password_confirmation ?? null
})

function updateProfile() {
  profileForm.patch('/profile')
}

function updatePassword() {
  confirmTouched.value = true
  if (passwordForm.password !== passwordForm.password_confirmation) return

  passwordForm.patch('/profile/password', {
    onSuccess: () => {
      passwordForm.reset()
      confirmTouched.value = false
    },
  })
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">My Profile</h1>
      <p class="text-sm text-app-text/60 mt-1">Manage your account details</p>
    </div>

    <div class="space-y-6">
      <!-- Avatar + roles -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
            <span class="text-2xl font-bold text-primary-text">
              {{ user.name?.charAt(0)?.toUpperCase() }}
            </span>
          </div>
          <div>
            <p class="text-lg font-semibold text-app-text">{{ user.name }}</p>
            <p class="text-sm text-app-text/60 mb-2">{{ user.email }}</p>
            <div class="flex flex-wrap gap-2">
              <Badge
                v-for="role in user.roles"
                :key="role"
                type="info"
              >
                {{ role }}
              </Badge>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile details -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">
          Personal Details
        </h2>
        <form @submit.prevent="updateProfile" class="space-y-4">
          <Input
            v-model="profileForm.name"
            label="Full Name"
            required
            :error="profileForm.errors.name"
          />
          <Input
            v-model="profileForm.email"
            label="Email Address"
            type="email"
            required
            :error="profileForm.errors.email"
          />
          <div class="flex justify-end">
            <Button type="submit" :loading="profileForm.processing">
              Update Profile
            </Button>
          </div>
        </form>
      </div>

      <!-- Password -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">
          Change Password
        </h2>
        <form @submit.prevent="updatePassword" class="space-y-4" novalidate>
          <PasswordInput
            v-model="passwordForm.current_password"
            label="Current Password"
            autocomplete="current-password"
            :error="passwordForm.errors.current_password"
          />

          <PasswordInput
            v-model="passwordForm.password"
            label="New Password"
            autocomplete="new-password"
            :show-strength="true"
            :error="passwordForm.errors.password"
          />

          <div class="flex flex-col gap-1">
            <PasswordInput
              v-model="passwordForm.password_confirmation"
              label="Confirm New Password"
              autocomplete="new-password"
              :show-strength="false"
              :error="confirmError"
              @blur="confirmTouched = true"
            />
          </div>

          <div class="flex justify-end">
            <Button type="submit" :loading="passwordForm.processing">
              Update Password
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
