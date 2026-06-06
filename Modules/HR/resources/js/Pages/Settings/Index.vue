<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout   from '@shared/layouts/AppLayout.vue'
import Input       from '@shared/components/form/Input.vue'
import Button      from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  settings: { type: Object, required: true },
})

const form = useForm({
  document_expiry_warning_days: props.settings.document_expiry_warning_days,
  company_name:                 props.settings.company_name,
  payroll_period_start_day:     props.settings.payroll_period_start_day,
})

function submit() {
  form.patch('/hr/settings')
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">HR Settings</h1>
      <p class="text-sm text-app-text/60 mt-1">Configure HR module behaviour</p>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Company</h2>
        <Input v-model="form.company_name" label="Company Name"
               hint="Used on payslips and HR documents"
               :error="form.errors.company_name" />
      </div>

      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Documents</h2>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">
            Expiry Warning (days before)
          </label>
          <input v-model.number="form.document_expiry_warning_days"
                 type="number" min="1" max="365"
                 class="w-32 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
          <p class="text-xs text-app-text/40">
            Receive a notification this many days before a document expires.
            Currently set to {{ form.document_expiry_warning_days }} days.
          </p>
          <p v-if="form.errors.document_expiry_warning_days" class="text-xs text-red-500">
            {{ form.errors.document_expiry_warning_days }}
          </p>
        </div>
      </div>

      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Payroll</h2>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Payroll Period Start Day</label>
          <input v-model.number="form.payroll_period_start_day"
                 type="number" min="1" max="28"
                 class="w-24 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
          <p class="text-xs text-app-text/40">
            Day of month the payroll period starts (1–28)
          </p>
        </div>
      </div>

      <div class="flex justify-end">
        <Button type="submit" :loading="form.processing">Save Settings</Button>
      </div>
    </form>
  </div>
</template>
