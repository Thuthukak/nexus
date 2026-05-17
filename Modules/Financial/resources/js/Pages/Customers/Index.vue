<script setup>
import { ref }       from 'vue'
import { router }    from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import DataTable     from '@shared/components/data/DataTable.vue'
import Badge         from '@shared/components/display/Badge.vue'
import ConfirmDialog from '@shared/components/feedback/ConfirmDialog.vue'

defineOptions({ layout: AppLayout })
defineProps({ customers: { type: Array, default: () => [] } })

const columns = [
  { key: 'company_name',   label: 'Company',   sortable: true },
  { key: 'contact_name',   label: 'Contact',   sortable: true },
  { key: 'email',          label: 'Email',     sortable: true },
  { key: 'invoices_count', label: 'Invoices',  sortable: true },
  { key: 'is_active',      label: 'Status',    sortable: true },
  { key: 'actions',        label: '',          sortable: false },
]

const confirmDelete = ref(false)
const deletingId    = ref(null)

function promptDelete(id) {
  deletingId.value    = id
  confirmDelete.value = true
}

function handleDelete() {
  router.delete(`/financial/customers/${deletingId.value}`, {
    onFinish: () => {
      confirmDelete.value = false
      deletingId.value    = null
    },
  })
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Customers</h1>
        <p class="text-sm text-app-text/60 mt-1">{{ customers.length }} customer(s)</p>
      </div>
      <a href="/financial/customers/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Customer
      </a>
    </div>

    <DataTable :columns="columns" :rows="customers" empty-message="No customers yet.">
      <template #cell-company_name="{ row, value }">
        <a :href="`/financial/customers/${row.id}`" class="font-medium text-primary hover:underline">
          {{ value }}
        </a>
      </template>
      <template #cell-is_active="{ value }">
        <Badge :type="value ? 'success' : 'neutral'" dot>{{ value ? 'Active' : 'Inactive' }}</Badge>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-end gap-1">
          <a :href="`/financial/customers/${row.id}`"
             class="px-2 py-1 text-xs text-app-text/50 hover:text-primary rounded transition-colors">
            View
          </a>
          <a :href="`/financial/customers/${row.id}/edit`"
             class="px-2 py-1 text-xs text-app-text/50 hover:text-primary rounded transition-colors">
            Edit
          </a>
          <button @click="promptDelete(row.id)"
                  class="px-2 py-1 text-xs text-app-text/50 hover:text-red-500 rounded transition-colors">
            Delete
          </button>
        </div>
      </template>
    </DataTable>

    <ConfirmDialog
      :show="confirmDelete"
      title="Delete Customer"
      message="This customer and all associated data will be deleted."
      confirm-label="Delete"
      danger
      @confirm="handleDelete"
      @cancel="confirmDelete = false"
    />
  </div>
</template>
