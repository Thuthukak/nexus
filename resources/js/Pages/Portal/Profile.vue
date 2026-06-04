<script setup>
import { useForm }  from '@inertiajs/vue3'
import PortalLayout from '@shared/layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

const props = defineProps({
  user:     { type: Object, required: true },
  customer: { type: Object, default: null },
})

const profileForm = useForm({
  name:  props.user.name,
  phone: props.customer?.phone ?? '',
})

const passwordForm = useForm({
  current_password:      '',
  password:              '',
  password_confirmation: '',
})

function updateProfile() {
  profileForm.patch('/portal/profile')
}

function updatePassword() {
  passwordForm.patch('/portal/profile/password', {
    onSuccess: () => passwordForm.reset(),
  })
}
</script>

<template>
  <div class="max-w-xl">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Profile</h1>
      <p class="text-sm text-gray-500 mt-1">Manage your contact details and password</p>
    </div>

    <div class="space-y-6">
      <!-- Profile details -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Contact Details</h2>

        <form @submit.prevent="updateProfile" class="space-y-4">
          <div v-if="customer" class="text-sm text-gray-500 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2">
            Company: <strong class="text-gray-900 dark:text-white">{{ customer.company_name }}</strong>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Your Name</label>
            <input v-model="profileForm.name" type="text" required
                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
            <p v-if="profileForm.errors.name" class="text-xs text-red-500">{{ profileForm.errors.name }}</p>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
            <input :value="user.email" type="email" disabled
                   class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-400 text-sm cursor-not-allowed" />
            <p class="text-xs text-gray-400">Email cannot be changed. Contact us if you need to update it.</p>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
            <input v-model="profileForm.phone" type="text"
                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
          </div>

          <button type="submit" :disabled="profileForm.processing"
                  class="px-6 py-2 rounded-lg text-sm font-semibold text-white disabled:opacity-60"
                  style="background-color: var(--color-primary);">
            Save Changes
          </button>
        </form>
      </div>

      <!-- Change password -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Change Password</h2>

        <form @submit.prevent="updatePassword" class="space-y-4">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
            <input v-model="passwordForm.current_password" type="password" required
                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
            <p v-if="passwordForm.errors.current_password" class="text-xs text-red-500">{{ passwordForm.errors.current_password }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
              <input v-model="passwordForm.password" type="password" required minlength="8"
                     class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
              <p v-if="passwordForm.errors.password" class="text-xs text-red-500">{{ passwordForm.errors.password }}</p>
            </div>
            <div class="flex flex-col gap-1">
              <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
              <input v-model="passwordForm.password_confirmation" type="password" required
                     class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
            </div>
          </div>

          <button type="submit" :disabled="passwordForm.processing"
                  class="px-6 py-2 rounded-lg text-sm font-semibold text-white disabled:opacity-60"
                  style="background-color: var(--color-primary);">
            Update Password
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
