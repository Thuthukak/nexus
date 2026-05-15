<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Input from '@shared/components/form/Input.vue'
import Button from '@shared/components/buttons/Button.vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })
defineProps({
  departments: { type: Array, default: () => [] },
  job_titles:  { type: Array, default: () => [] },
})

const form = useForm({
  name:            '',
  email:           '',
  department_id:   '',
  job_title_id:    '',
  employment_type: 'full_time',
  start_date:      '',
  phone:           '',
})

function submit() {
  form.post('/hr/employees')
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <a href="/hr/employees" class="text-sm text-primary hover:underline">← Employees</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Add Employee</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-sm font-semibold text-app-text/50 uppercase tracking-wider">Personal Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input v-model="form.name"  label="Full Name"  required :error="form.errors.name" />
          <Input v-model="form.email" label="Email"      required type="email" :error="form.errors.email" />
          <Input v-model="form.phone" label="Phone"      :error="form.errors.phone" />
          <Input v-model="form.start_date" label="Start Date" type="date" required :error="form.errors.start_date" />
        </div>
      </div>

      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-sm font-semibold text-app-text/50 uppercase tracking-wider">Role & Department</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Department</label>
            <select v-model="form.department_id"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
              <option value="">No department</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Job Title</label>
            <select v-model="form.job_title_id"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
              <option value="">No title</option>
              <option v-for="j in job_titles" :key="j.id" :value="j.id">{{ j.name }}</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Employment Type</label>
            <select v-model="form.employment_type"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
              <option value="full_time">Full Time</option>
              <option value="part_time">Part Time</option>
              <option value="contract">Contract</option>
              <option value="intern">Intern</option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3">
        <a href="/hr/employees" class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Add Employee</Button>
      </div>
    </form>
  </div>
</template>
