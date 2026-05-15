<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Input from '@shared/components/form/Input.vue'
import Button from '@shared/components/buttons/Button.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })
defineProps({
  employees:   { type: Array, default: () => [] },
  leave_types: { type: Array, default: () => [] },
})

const form = useForm({
  employee_id:   '',
  leave_type_id: '',
  start_date:    '',
  end_date:      '',
  reason:        '',
})

function submit() {
  form.post('/hr/leave')
}
</script>

<template>
  <div class="max-w-xl">
    <div class="mb-6">
      <a href="/hr/leave" class="text-sm text-primary hover:underline">← Leave</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Apply for Leave</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Employee <span class="text-red-500">*</span></label>
          <select v-model="form.employee_id"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="">Select employee…</option>
            <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name }}</option>
          </select>
          <p v-if="form.errors.employee_id" class="text-xs text-red-500">{{ form.errors.employee_id }}</p>
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Leave Type <span class="text-red-500">*</span></label>
          <select v-model="form.leave_type_id"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="">Select type…</option>
            <option v-for="t in leave_types" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
          <p v-if="form.errors.leave_type_id" class="text-xs text-red-500">{{ form.errors.leave_type_id }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <Input v-model="form.start_date" label="Start Date" type="date" required :error="form.errors.start_date" />
          <Input v-model="form.end_date"   label="End Date"   type="date" required :error="form.errors.end_date" />
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Reason</label>
          <textarea v-model="form.reason" rows="3"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none"
                    placeholder="Optional reason for leave…" />
        </div>
      </div>

      <div class="flex items-center justify-end gap-3">
        <a href="/hr/leave" class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Submit Application</Button>
      </div>
    </form>
  </div>
</template>
