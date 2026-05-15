<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import Button from '@shared/components/buttons/Button.vue'
import Input from '@shared/components/form/Input.vue'

defineOptions({ layout: AppLayout })
defineProps({ departments: { type: Array, default: () => [] } })

const form = useForm({ name: '' })

function submit() {
  form.post('/hr/departments', {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">Departments</h1>
      <p class="text-sm text-app-text/60 mt-1">Manage organisational structure</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
          <table class="w-full text-sm">
            <thead class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Employees</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-if="!departments.length">
                <td colspan="2" class="px-4 py-12 text-center text-app-text/40">No departments yet.</td>
              </tr>
              <tr v-for="dept in departments" :key="dept.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                <td class="px-4 h-12 font-medium text-app-text">{{ dept.name }}</td>
                <td class="px-4 h-12 text-app-text/60">{{ dept.employees_count }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-sm font-semibold text-app-text mb-4">Add Department</h2>
          <form @submit.prevent="submit" class="space-y-4">
            <Input v-model="form.name" label="Department Name" required :error="form.errors.name" />
            <Button type="submit" :loading="form.processing" class="w-full">Create</Button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
