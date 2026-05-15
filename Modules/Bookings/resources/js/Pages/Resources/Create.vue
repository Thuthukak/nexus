<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Input from '@shared/components/form/Input.vue'
import Button from '@shared/components/buttons/Button.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })

const form = useForm({
  name:     '',
  type:     'room',
  capacity: 1,
  location: '',
  colour:   '#3B82F6',
})

function submit() {
  form.post('/bookings/resources')
}
</script>

<template>
  <div class="max-w-xl">
    <div class="mb-6">
      <a href="/bookings/resources" class="text-sm text-primary hover:underline">← Resources</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Add Resource</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <Input v-model="form.name" label="Resource Name" required :error="form.errors.name" />

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Type</label>
          <select v-model="form.type"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="room">Room</option>
            <option value="equipment">Equipment</option>
            <option value="staff">Staff</option>
            <option value="vehicle">Vehicle</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <Input v-model.number="form.capacity" label="Capacity" type="number" min="1" :error="form.errors.capacity" />
          <Input v-model="form.location" label="Location" :error="form.errors.location" />
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Calendar Colour</label>
          <input v-model="form.colour" type="color"
                 class="h-10 w-full rounded-lg border border-gray-300 cursor-pointer p-1" />
        </div>
      </div>

      <div class="flex items-center justify-end gap-3">
        <a href="/bookings/resources" class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Add Resource</Button>
      </div>
    </form>
  </div>
</template>
