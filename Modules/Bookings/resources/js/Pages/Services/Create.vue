<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Input from '@shared/components/form/Input.vue'
import Button from '@shared/components/buttons/Button.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })

const form = useForm({
  name:             '',
  duration_minutes: 60,
  buffer_minutes:   0,
  price:            0,
  max_participants: 1,
  description:      '',
})

function submit() {
  form.post('/bookings/services')
}
</script>

<template>
  <div class="max-w-xl">
    <div class="mb-6">
      <a href="/bookings/services" class="text-sm text-primary hover:underline">← Services</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Add Service</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <Input v-model="form.name" label="Service Name" required :error="form.errors.name" />

        <div class="grid grid-cols-2 gap-4">
          <Input v-model.number="form.duration_minutes" label="Duration (min)" type="number" min="15" required :error="form.errors.duration_minutes" />
          <Input v-model.number="form.buffer_minutes"   label="Buffer (min)"   type="number" min="0" />
          <Input v-model.number="form.price"            label="Price (R)"      type="number" min="0" step="0.01" required :error="form.errors.price" />
          <Input v-model.number="form.max_participants" label="Max Participants" type="number" min="1" required />
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Description</label>
          <textarea v-model="form.description" rows="3"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>
      </div>

      <div class="flex items-center justify-end gap-3">
        <a href="/bookings/services" class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Add Service</Button>
      </div>
    </form>
  </div>
</template>
