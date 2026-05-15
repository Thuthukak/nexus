<script setup>
import { ref } from 'vue'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import DataTable     from '@shared/components/data/DataTable.vue'
import Badge         from '@shared/components/display/Badge.vue'
import ConfirmDialog from '@shared/components/feedback/ConfirmDialog.vue'
import { router }    from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })
defineProps({ invoices: { type: Array, default: () => [] } })

const columns = [
  { key: 'reference', label: 'Reference', sortable: true },
  { key: 'customer',  label: 'Customer',  sortable: true },
  { key: 'total',     label: 'Total',     sortable: true },
  { key: 'status',    label: 'Status',    sortable: true },
  { key: 'due_date',  label: 'Due Date',  sortable: true },
  { key: 'actions',   label: '',          sortable: false },
]

const statusType = {
  paid: 'success', draft: 'neutral', overdue: 'danger',
  sent: 'info', approved: 'warning',
}

const confirmDelete  = ref(false)
const deletingId     = ref(null)
const deleteLoading  = ref(false)

function promptDelete(id) {
  deletingId.value    = id
  confirmDelete.value = true
}

function handleDelete() {
  deleteLoading.value = true
  router.delete(`/financial/invoices/${deletingId.value}`, {
    onFinish: () => {
      deleteLoading.value  = false
      confirmDelete.value  = false
      deletingId.value     = null
    },
  })
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
      empty-message="No invoices yet. Create your first invoice to get started."
    >
      <template #cell-status="{ value }">
        <Badge :type="statusType[value] ?? 'neutral'" dot>{{ value }}</Badge>
      </template>
      <template #cell-total="{ value }">
        R {{ Number(value ?? 0).toFixed(2) }}
      </template>
      <template #cell-reference="{ row, value }">
        <a :href="`/financial/invoices/${row.id}`" class="font-medium text-primary hover:underline">
          {{ value }}
        </a>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-end gap-2">
          <a :href="`/financial/invoices/${row.id}`"
              class="text-xs text-app-text/50 hover:text-primary transition-colors px-2 py-1 rounded">
            View
          </a>
          <button
            @click="promptDelete(row.id)"
            class="text-xs text-app-text/50 hover:text-red-500 transition-colors px-2 py-1 rounded"
          >
            Delete
          </button>
        </div>
      </template>
    </DataTable>

    <ConfirmDialog
      :show="confirmDelete"
      title="Delete Invoice"
      message="This invoice will be permanently deleted. This action cannot be undone."
      confirm-label="Delete Invoice"
      :loading="deleteLoading"
      danger
      @confirm="handleDelete"
      @cancel="confirmDelete = false"
    />
  </div>
</template>
