<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout   from '@shared/layouts/AppLayout.vue'
import Button      from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  preferences: { type: Object, required: true },
})

const notificationTypes = [
  { key: 'invoice.approved', label: 'Invoice Approved',       module: 'Financial' },
  { key: 'invoice.paid',     label: 'Invoice Paid',           module: 'Financial' },
  { key: 'invoice.overdue',  label: 'Invoice Overdue',        module: 'Financial' },
  { key: 'leave.submitted',  label: 'Leave Application',      module: 'HR' },
  { key: 'leave.approved',   label: 'Leave Approved',         module: 'HR' },
  { key: 'leave.rejected',   label: 'Leave Rejected',         module: 'HR' },
  { key: 'booking.confirmed',label: 'Booking Confirmed',      module: 'Bookings' },
  { key: 'booking.cancelled',label: 'Booking Cancelled',      module: 'Bookings' },
  { key: 'user.created',     label: 'New User Created',       module: 'Core' },
]

const moduleGroups = ['Financial', 'HR', 'Bookings', 'Core']

// Build form with defaults
const defaultPrefs = {}
notificationTypes.forEach(({ key }) => {
  defaultPrefs[key] = {
    in_app: props.preferences?.[key]?.in_app ?? true,
    email:  props.preferences?.[key]?.email  ?? true,
  }
})

const form = useForm({ preferences: defaultPrefs })

function submit() {
  form.patch('/profile/notification-preferences')
}

function typesForModule(module) {
  return notificationTypes.filter(t => t.module === module)
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
      <div v-for="module in moduleGroups" :key="module"
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
            <tr v-for="type in typesForModule(module)" :key="type.key">
              <td class="px-6 py-3 text-app-text">{{ type.label }}</td>
              <td class="px-6 py-3 text-center">
                <!-- In-app always on — disabled toggle -->
                <div class="inline-flex items-center justify-center">
                  <div class="w-8 h-5 rounded-full bg-primary/30 relative cursor-not-allowed">
                    <div class="absolute right-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow-sm" />
                  </div>
                </div>
              </td>
              <td class="px-6 py-3 text-center">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    v-model="form.preferences[type.key].email"
                    type="checkbox"
                    class="sr-only peer"
                  />
                  <div class="w-8 h-5 bg-gray-200 dark:bg-gray-700 rounded-full peer
                              peer-checked:bg-primary transition-colors
                              peer-focus:ring-2 peer-focus:ring-primary/50" />
                  <span class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full
                               shadow-sm transition-transform peer-checked:translate-x-3" />
                </label>
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
