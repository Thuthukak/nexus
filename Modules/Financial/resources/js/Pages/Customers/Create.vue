<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout   from '@shared/layouts/AppLayout.vue'
import Input       from '@shared/components/form/Input.vue'
import Button      from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const form = useForm({
  company_name: '',
  contact_name: '',
  email:        '',
  phone:        '',
  vat_number:   '',
  address: {
    line1: '',
    line2: '',
    city:  '',
    code:  '',
  },
})

function submit() {
  form.post('/financial/customers')
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <a href="/financial/customers" class="text-sm text-primary hover:underline">← Customers</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">New Customer</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Company Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input v-model="form.company_name" label="Company Name" required :error="form.errors.company_name" class="sm:col-span-2" />
          <Input v-model="form.contact_name" label="Contact Person" :error="form.errors.contact_name" />
          <Input v-model="form.vat_number"   label="VAT Number"    :error="form.errors.vat_number" />
          <Input v-model="form.email"        label="Email"         type="email" :error="form.errors.email" />
          <Input v-model="form.phone"        label="Phone"         :error="form.errors.phone" />
        </div>
      </div>

      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Address</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input v-model="form.address.line1" label="Street Address" class="sm:col-span-2" />
          <Input v-model="form.address.line2" label="Suburb / Unit" class="sm:col-span-2" />
          <Input v-model="form.address.city"  label="City" />
          <Input v-model="form.address.code"  label="Postal Code" />
        </div>
      </div>

      <div class="flex items-center justify-end gap-3">
        <a href="/financial/customers" class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Create Customer</Button>
      </div>
    </form>
  </div>
</template>
