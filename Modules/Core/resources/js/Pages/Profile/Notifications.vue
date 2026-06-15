<script setup>
import { useForm }  from '@inertiajs/vue3'
import AppLayout    from '@shared/layouts/AppLayout.vue'
import Button       from '@shared/components/buttons/Button.vue'
import Checkbox     from '@shared/components/buttons/Checkbox.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  preferences:       { type: Object, required: true },
  notificationTypes: { type: Array,  required: true },
})

const defaultPrefs = {}
props.notificationTypes.forEach(({ types }) => {
  types.forEach(({ key }) => {
    defaultPrefs[key] = {
      in_app: props.preferences?.[key]?.in_app ?? true,
      email:  props.preferences?.[key]?.email  ?? true,
    }
  })
})

const form = useForm({ preferences: defaultPrefs })

function submit() {
  form.patch('/profile/notification-preferences')
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <a href="/profile" class="text-sm text-primary hover:underline">← Profile</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Notification Preferences</h1>
      <p class="text-sm text-app-text/60 mt-1">
        Choose how you receive notifications. In-app notifications cannot be disabled.
      </p>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div v-for="{ module, types } in notificationTypes" :key="module"
          class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">
            {{ module }}
          </h2>
        </div>

        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-50 dark:border-gray-800">
              <th class="px-6 py-2 text-left text-xs text-app-text/40 font-medium">Notification</th>
              <th class="px-6 py-2 text-center text-xs text-app-text/40 font-medium w-24">In-App</th>
              <th class="px-6 py-2 text-center text-xs text-app-text/40 font-medium w-24">Email</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-for="type in types" :key="type.key">
              <td class="px-6 py-3 text-app-text">{{ type.label }}</td>

              <!-- In-app: always on, disabled -->
              <td class="px-6 py-3 text-center">
                <div class="flex justify-center opacity-40 cursor-not-allowed">
                  <Checkbox :model-value="true" @update:model-value="() => {}" />
                </div>
              </td>

              <!-- Email: user-controlled -->
              <td class="px-6 py-3 text-center">
                <div class="flex justify-center">
                  <Checkbox
                    :model-value="form.preferences[type.key].email"
                    @update:model-value="val => form.preferences[type.key].email = val"
                  />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end">
        <Button type="submit" :loading="form.processing">Save Preferences</Button>
      </div>
    </form>
  </div>
</template>