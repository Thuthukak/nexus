<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge     from '@shared/components/display/Badge.vue'
import DataTable from '@shared/components/data/DataTable.vue'

defineOptions({ layout: AppLayout })
defineProps({ customer: { type: Object, required: true } })

const statusType = {
  paid: 'success', draft: 'neutral', overdue: 'danger',
  sent: 'info', approved: 'warning', part_paid: 'warning',
}

const invoiceColumns = [
  { key: 'reference', label: 'Reference', sortable: true },
  { key: 'total',     label: 'Total',     sortable: true },
  { key: 'due_date',  label: 'Due Date',  sortable: true },
  { key: 'status',    label: 'Status',    sortable: true },
]

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div class="max-w-5xl">
    <!-- Header -->
    <div class="mb-6">
      <a href="/financial/customers" class="text-sm text-primary hover:underline">← Customers</a>
      <div class="flex items-start justify-between mt-3 gap-4 flex-wrap">
        <div>
          <h1 class="text-2xl font-bold text-app-text">{{ customer.company_name }}</h1>
          <div class="flex items-center gap-3 mt-2">
            <Badge :type="customer.is_active ? 'success' : 'neutral'" dot>
              {{ customer.is_active ? 'Active' : 'Inactive' }}
            </Badge>
            <span v-if="customer.vat_number" class="text-sm text-app-text/50">
              VAT: {{ customer.vat_number }}
            </span>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <a :href="`/financial/customers/${customer.id}/edit`"
             class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-app-text/70 hover:text-app-text transition-colors">
            Edit Customer
          </a>
          <a :href="`/financial/invoices/create?customer_id=${customer.id}`"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Invoice
          </a>
        </div>
      </div>
    </div>

    <!-- Contact details row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div v-if="customer.contact_name || customer.email || customer.phone"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Contact</h2>
        <p v-if="customer.contact_name" class="text-sm font-medium text-app-text">{{ customer.contact_name }}</p>
        <p v-if="customer.email" class="text-sm text-app-text/60 mt-1">{{ customer.email }}</p>
        <p v-if="customer.phone" class="text-sm text-app-text/60">{{ customer.phone }}</p>
      </div>

      <div v-if="customer.address?.city || customer.address?.line1"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Address</h2>
        <div class="text-sm text-app-text/70 space-y-0.5">
          <p v-if="customer.address?.line1">{{ customer.address.line1 }}</p>
          <p v-if="customer.address?.line2">{{ customer.address.line2 }}</p>
          <p v-if="customer.address?.city">
            {{ customer.address.city }}
            <span v-if="customer.address?.code">, {{ customer.address.code }}</span>
          </p>
        </div>
      </div>

      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Summary</h2>
        <dl class="space-y-2 text-sm">
          <div class="flex justify-between">
            <dt class="text-app-text/50">Total Invoices</dt>
            <dd class="font-medium text-app-text">{{ customer.invoices?.length ?? 0 }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-app-text/50">Outstanding</dt>
            <dd class="font-medium text-red-500">
              {{ currency(customer.invoices?.filter(i => ['sent','approved','overdue','part_paid'].includes(i.status)).reduce((s, i) => s + Number(i.total), 0) ?? 0) }}
            </dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Invoices — full width -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-app-text">Invoices</h2>
        <span class="text-xs text-app-text/40">{{ customer.invoices?.length ?? 0 }} total</span>
      </div>
      <DataTable :columns="invoiceColumns" :rows="customer.invoices ?? []"
                 empty-message="No invoices for this customer yet.">
        <template #cell-reference="{ row, value }">
          <a :href="`/financial/invoices/${row.id}`" class="font-medium text-primary hover:underline">
            {{ value }}
          </a>
        </template>
        <template #cell-total="{ value }">
          <span class="font-medium">{{ currency(value) }}</span>
        </template>
        <template #cell-status="{ value }">
          <Badge :type="statusType[value] ?? 'neutral'" dot>{{ value }}</Badge>
        </template>
      </DataTable>
    </div>
  </div>
</template>
