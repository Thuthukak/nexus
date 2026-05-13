<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import DataTable from '@shared/components/data/DataTable.vue'
import Badge from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })
defineProps({
  invoices: { type: Array, default: () => [] },
})

const columns = [
  { key: 'reference', label: 'Reference', sortable: true },
  { key: 'customer',  label: 'Customer',  sortable: true },
  { key: 'total',     label: 'Total',     sortable: true },
  { key: 'status',    label: 'Status',    sortable: true },
  { key: 'due_date',  label: 'Due Date',  sortable: true },
]

const statusType = {
  paid:     'success',
  draft:    'neutral',
  overdue:  'danger',
  sent:     'info',
  approved: 'warning',
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Invoices</h1>
        <p class="text-sm text-app-text/60 mt-1">Manage and track all invoices</p>
      </div>
      <a href="/financial/invoices/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90 transition-opacity">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Invoice
      </a>
    </div>

    <DataTable
      :columns="columns"
      :rows="invoices"
      empty-message="No invoices found. Create your first invoice to get started."
    >
      <template #cell-status="{ value }">
        <Badge :type="statusType[value] ?? 'neutral'" dot>
          {{ value ?? 'draft' }}
        </Badge>
      </template>
      <template #cell-total="{ value }">
        R {{ Number(value ?? 0).toFixed(2) }}
      </template>
    </DataTable>
  </div>
</template>
