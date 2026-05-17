<script setup>
import { ref }        from 'vue'
import { router }     from '@inertiajs/vue3'
import AppLayout      from '@shared/layouts/AppLayout.vue'
import DataTable      from '@shared/components/data/DataTable.vue'
import Badge          from '@shared/components/display/Badge.vue'
import ConfirmDialog  from '@shared/components/feedback/ConfirmDialog.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  invoices: { type: Array,  default: () => [] },
  filters:  { type: Object, default: () => ({}) },
  statuses: { type: Array,  default: () => [] },
})

const columns = [
  { key: 'reference',  label: 'Reference',   sortable: true },
  { key: 'customer',   label: 'Customer',    sortable: true },
  { key: 'issue_date', label: 'Issued',      sortable: true },
  { key: 'due_date',   label: 'Due',         sortable: true },
  { key: 'total',      label: 'Total',       sortable: true },
  { key: 'status',     label: 'Status',      sortable: true },
  { key: 'actions',    label: '',            sortable: false },
]

const statusType = {
  paid: 'success', draft: 'neutral', overdue: 'danger',
  sent: 'info', approved: 'warning', part_paid: 'warning', cancelled: 'neutral',
}

const search         = ref(props.filters.search ?? '')
const selectedStatus = ref(props.filters.status ?? '')

function applyFilters() {
  router.get('/financial/invoices', {
    search: search.value       || undefined,
    status: selectedStatus.value || undefined,
  }, { preserveState: true, replace: true })
}

function clearFilters() {
  search.value         = ''
  selectedStatus.value = ''
  router.get('/financial/invoices', {}, { preserveState: true, replace: true })
}

const confirmDelete = ref(false)
const deletingId    = ref(null)
const deleteLoading = ref(false)

function promptDelete(id) {
  deletingId.value    = id
  confirmDelete.value = true
}

function handleDelete() {
  deleteLoading.value = true
  router.delete(`/financial/invoices/${deletingId.value}`, {
    onFinish: () => {
      deleteLoading.value = false
      confirmDelete.value = false
      deletingId.value    = null
    },
  })
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Invoices</h1>
        <p class="text-sm text-app-text/60 mt-1">{{ invoices.length }} invoice(s)</p>
      </div>
      <a href="/financial/invoices/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Invoice
      </a>
    </div>

    <!-- Filters -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-4 mb-4 flex flex-wrap items-end gap-3">
      <div class="flex-1 min-w-48">
        <label class="text-xs font-medium text-app-text/50 mb-1 block">Search</label>
        <input
          v-model="search"
          @keyup.enter="applyFilters"
          placeholder="Reference or customer…"
          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
        />
      </div>
      <div>
        <label class="text-xs font-medium text-app-text/50 mb-1 block">Status</label>
        <select
          v-model="selectedStatus"
          class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
        >
          <option value="">All statuses</option>
          <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s }}</option>
        </select>
      </div>
      <button @click="applyFilters"
              class="px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        Filter
      </button>
      <button v-if="filters.search || filters.status"
              @click="clearFilters"
              class="px-4 py-2 rounded-lg text-sm text-app-text/60 hover:text-app-text border border-gray-200 dark:border-gray-700">
        Clear
      </button>
    </div>

    <DataTable :columns="columns" :rows="invoices" empty-message="No invoices found.">
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
      <template #cell-actions="{ row }">
        <div class="flex items-center justify-end gap-1">
          <a :href="`/financial/invoices/${row.id}`"
             class="px-2 py-1 text-xs text-app-text/50 hover:text-primary rounded transition-colors">
            View
          </a>
          <a v-if="!['paid','cancelled'].includes(row.status)"
             :href="`/financial/invoices/${row.id}/edit`"
             class="px-2 py-1 text-xs text-app-text/50 hover:text-primary rounded transition-colors">
            Edit
          </a>
          <button
            v-if="row.status !== 'paid'"
            @click="promptDelete(row.id)"
            class="px-2 py-1 text-xs text-app-text/50 hover:text-red-500 rounded transition-colors">
            Delete
          </button>
        </div>
      </template>
    </DataTable>

    <ConfirmDialog
      :show="confirmDelete"
      title="Delete Invoice"
      message="This invoice will be permanently deleted and cannot be recovered."
      confirm-label="Delete"
      :loading="deleteLoading"
      danger
      @confirm="handleDelete"
      @cancel="confirmDelete = false"
    />
  </div>
</template>
