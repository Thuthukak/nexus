<script setup>
import { useForm }  from '@inertiajs/vue3'
import AppLayout    from '@shared/layouts/AppLayout.vue'
import Input        from '@shared/components/form/Input.vue'
import Button       from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

// Kept the assigned version so 'props.settings' is accessible below
const props = defineProps({ settings: { type: Object, required: true } })

const form = useForm({
  app_name: props.settings.app_name,
  timezone: props.settings.timezone,
})

function submit() {
  form.patch('/settings/general')
}

const timezones = [
  'Africa/Johannesburg',
  'Africa/Cairo',
  'Africa/Lagos',
  'Europe/London',
  'Europe/Paris',
  'America/New_York',
  'America/Chicago',
  'America/Denver',
  'America/Los_Angeles',
  'Asia/Dubai',
  'Asia/Singapore',
  'Asia/Tokyo',
  'Australia/Sydney',
  'UTC',
]
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">General Settings</h1>
      <p class="text-sm text-app-text/60 mt-1">Platform-wide configuration</p>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">
          Application
        </h2>

        <Input
          v-model="form.app_name"
          label="Application Name"
          hint="Displayed in the browser tab and sidebar header"
          :error="form.errors.app_name"
        />

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Timezone</label>
          <select
            v-model="form.timezone"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
          >
            <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
          </select>
          <p v-if="form.errors.timezone" class="text-xs text-red-500">
            {{ form.errors.timezone }}
          </p>
        </div>
      </div>

      <div class="flex justify-end">
        <Button type="submit" :loading="form.processing">Save Settings</Button>
      </div>
    </form>
  </div>
</template>