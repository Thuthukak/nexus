<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout   from '@shared/layouts/AppLayout.vue'
import Input       from '@shared/components/form/Input.vue'
import Button      from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({ customer: { type: Object, required: true } })

const form = useForm({
  company_name: props.customer.company_name,
  contact_name: props.customer.contact_name ?? '',
  email:        props.customer.email        ?? '',
  phone:        props.customer.phone        ?? '',
  vat_number:   props.customer.vat_number   ?? '',
  is_active:    props.customer.is_active,
  address: {
    line1: props.customer.address?.line1 ?? '',
    line2: props.customer.address?.line2 ?? '',
    city:  props.customer.address?.city  ?? '',
    code:  props.customer.address?.code  ?? '',
  },
})

function submit() {
  form.put(`/financial/customers/${props.customer.id}`)
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <a :href="`/financial/customers/${customer.id}`" class="text-sm text-primary hover:underline">← Customer</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Edit Customer</h1>
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

        <div class="flex items-center gap-2 pt-2">
          <input v-model="form.is_active" type="checkbox" id="is_active"
                 class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
          <label for="is_active" class="text-sm font-medium text-app-text">Active customer</label>
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
        <a :href="`/financial/customers/${customer.id}`"
           class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Save Changes</Button>
      </div>
    </form>
  </div>
</template>
