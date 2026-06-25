<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout   from '@shared/layouts/AppLayout.vue'
import Input       from '@shared/components/form/Input.vue'
import Button      from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  user:  { type: Object, required: true },
  roles: { type: Array,  default: () => [] },
})

const form = useForm({
  name:          props.user.name,
  email:         props.user.email,
  role:          props.user.roles?.[0] ?? '',
  portal_access: props.user.portal_access,
})

function submit() {
  form.patch(`/users/${props.user.id}`)
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <a :href="`/users/${user.id}`" class="text-sm text-primary hover:underline">← User</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Edit {{ user.name }}</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input v-model="form.name"  label="Full Name"  required :error="form.errors.name" />
          <Input v-model="form.email" label="Email"      required type="email" :error="form.errors.email" />
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Role</label>
          <select v-model="form.role"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="">No role</option>
            <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
          </select>
        </div>

        <div class="flex items-center gap-2">
          <input v-model="form.portal_access" type="checkbox" id="portal_access"
                 class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
          <label for="portal_access" class="text-sm font-medium text-app-text">
            Portal access
          </label>
          <p class="text-xs text-app-text/40">(allows login to customer portal)</p>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3">
        <a :href="`/users/${user.id}`"
           class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Save Changes</Button>
      </div>
    </form>
  </div>
</template>
