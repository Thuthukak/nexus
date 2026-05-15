<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Input from '@shared/components/form/Input.vue'
import Button from '@shared/components/buttons/Button.vue'
import { useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

defineOptions({ layout: AppLayout })
const props = defineProps({
  services:  { type: Array, default: () => [] },
  resources: { type: Array, default: () => [] },
})

const form = useForm({
  service_id:     '',
  resource_id:    '',
  customer_name:  '',
  customer_email: '',
  customer_phone: '',
  start_at:       '',
  notes:          '',
})

const selectedService = ref(null)
watch(() => form.service_id, (id) => {
  selectedService.value = props.services.find(s => s.id === id) ?? null
})

function submit() {
  form.post('/bookings/bookings')
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <a href="/bookings/bookings" class="text-sm text-primary hover:underline">← Bookings</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">New Booking</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Service Details</h2>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Service <span class="text-red-500">*</span></label>
          <select v-model="form.service_id"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="">Select a service…</option>
            <option v-for="s in services" :key="s.id" :value="s.id">
              {{ s.name }} ({{ s.duration_minutes }}min — R{{ s.price }})
            </option>
          </select>
          <p v-if="form.errors.service_id" class="text-xs text-red-500">{{ form.errors.service_id }}</p>
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Resource</label>
          <select v-model="form.resource_id"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="">No specific resource</option>
            <option v-for="r in resources" :key="r.id" :value="r.id">
              {{ r.name }} ({{ r.type }})
            </option>
          </select>
        </div>

        <Input v-model="form.start_at" label="Start Date & Time" type="datetime-local"
               required :error="form.errors.start_at" />
      </div>

      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Customer Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input v-model="form.customer_name"  label="Full Name"  required :error="form.errors.customer_name" />
          <Input v-model="form.customer_email" label="Email"      required type="email" :error="form.errors.customer_email" />
          <Input v-model="form.customer_phone" label="Phone"      :error="form.errors.customer_phone" />
        </div>
      </div>

      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Notes</label>
          <textarea v-model="form.notes" rows="3"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none"
                    placeholder="Any additional notes…" />
        </div>
      </div>

      <div class="flex items-center justify-end gap-3">
        <a href="/bookings/bookings" class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Create Booking</Button>
      </div>
    </form>
  </div>
</template>
