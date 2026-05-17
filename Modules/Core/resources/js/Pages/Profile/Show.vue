<script setup>
import { ref }       from 'vue'
import { useForm }   from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import Input         from '@shared/components/form/Input.vue'
import Button        from '@shared/components/buttons/Button.vue'
import Badge         from '@shared/components/display/Badge.vue'

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

function updateProfile() {
  profileForm.patch('/profile')
}

function updatePassword() {
  passwordForm.patch('/profile/password', {
    onSuccess: () => passwordForm.reset(),
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
        <form @submit.prevent="updatePassword" class="space-y-4">
          <Input
            v-model="passwordForm.current_password"
            label="Current Password"
            type="password"
            required
            :error="passwordForm.errors.current_password"
          />
          <Input
            v-model="passwordForm.password"
            label="New Password"
            type="password"
            required
            hint="Minimum 8 characters"
            :error="passwordForm.errors.password"
          />
          <Input
            v-model="passwordForm.password_confirmation"
            label="Confirm New Password"
            type="password"
            required
            :error="passwordForm.errors.password_confirmation"
          />
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
