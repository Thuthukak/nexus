<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout   from '@shared/layouts/AppLayout.vue'
import Input       from '@shared/components/form/Input.vue'
import Button      from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })
const props = defineProps({ roles: { type: Array, default: () => [] } })

const form = useForm({
  name:     '',
  email:    '',
  role:     '',
  password: '',
})

function submit() {
  form.post('/users')
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <a href="/users" class="text-sm text-primary hover:underline">← Users</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Add User</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Account Details</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input v-model="form.name"  label="Full Name"  required :error="form.errors.name" />
          <Input v-model="form.email" label="Email"      required type="email" :error="form.errors.email" />
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Role <span class="text-red-500">*</span></label>
          <select v-model="form.role"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                  :class="form.errors.role ? 'border-red-400' : ''">
            <option value="">Select a role…</option>
            <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
          </select>
          <p v-if="form.errors.role" class="text-xs text-red-500">{{ form.errors.role }}</p>
        </div>

        <Input v-model="form.password" label="Password"
               type="password" hint="Leave blank to auto-generate a secure password"
               :error="form.errors.password" 
               class="hidden"
        />
      </div>

      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4">
        <p class="text-sm text-blue-700 dark:text-blue-400">
          The user will be created with a verified email.
          If no password is set, a random password will be generated —
          use the Reset Password action to send them their credentials.
        </p>
      </div>

      <div class="flex items-center justify-end gap-3">
        <a href="/users" class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Create User</Button>
      </div>
    </form>
  </div>
</template>
