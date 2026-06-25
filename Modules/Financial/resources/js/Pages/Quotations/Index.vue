<script setup>
import { ref }          from 'vue'
import { router }       from '@inertiajs/vue3'
import AppLayout        from '@shared/layouts/AppLayout.vue'
import DataTable        from '@shared/components/data/DataTable.vue'
import Badge            from '@shared/components/display/Badge.vue'
import ConfirmDialog    from '@shared/components/feedback/ConfirmDialog.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  quotations: { type: Array,  default: () => [] },
  stats:      { type: Object, default: () => ({}) },
  filters:    { type: Object, default: () => ({}) },
  statuses:   { type: Array,  default: () => [] },
})

const columns = [
  { key: 'reference',  label: 'Reference',   sortable: true },
  { key: 'customer',   label: 'Customer',    sortable: true },
  { key: 'total',      label: 'Total',       sortable: true },
  { key: 'valid_until',label: 'Valid Until',  sortable: true },
  { key: 'status',     label: 'Status',      sortable: true },
  { key: 'actions',   label: '',            sortable: false },
]

const statusType = {
  draft:     'neutral',
  sent:      'info',
  accepted:  'success',
  declined:  'danger',
  expired:   'neutral',
  converted: 'warning',
}

const search         = ref(props.filters.search ?? '')
const selectedStatus = ref(props.filters.status ?? '')

function applyFilters() {
  router.get('/financial/quotations', {
    search: search.value        || undefined,
    status: selectedStatus.value || undefined,
  }, { preserveState: true, replace: true })
}

const confirmDelete = ref(false)
const deletingId    = ref(null)

function promptDelete(id) {
  deletingId.value    = id
  confirmDelete.value = true
}

function handleDelete() {
  router.delete(`/financial/quotations/${deletingId.value}`, {}, {
    onFinish: () => {
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
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Quotations</h1>
        <p class="text-sm text-app-text/60 mt-1">{{ quotations.length }} quotation(s)</p>
      </div>
      <a href="/financial/quotations/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Quotation
      </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Total Quotes</p>
        <p class="text-2xl font-bold text-app-text">{{ stats.total }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Pending Value</p>
        <p class="text-xl font-bold text-blue-600">{{ currency(stats.pending_value) }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Accepted</p>
        <p class="text-2xl font-bold text-green-600">{{ stats.accepted_count }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Acceptance Rate</p>
        <p class="text-2xl font-bold text-app-text">{{ stats.acceptance_rate }}%</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-4 mb-4 flex flex-wrap gap-3 items-end">
      <div class="flex-1 min-w-44">
        <label class="text-xs font-medium text-app-text/50 mb-1 block">Search</label>
        <input v-model="search" @keyup.enter="applyFilters" placeholder="Reference or customer…"
               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
      </div>
      <div>
        <label class="text-xs font-medium text-app-text/50 mb-1 block">Status</label>
        <select v-model="selectedStatus"
                class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
          <option value="">All</option>
          <option v-for="s in statuses" :key="s" :value="s" class="capitalize">{{ s }}</option>
        </select>
      </div>
      <button @click="applyFilters"
              class="px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        Filter
      </button>
    </div>

    <!-- Table -->
    <DataTable :columns="columns" :rows="quotations" empty-message="No quotations found.">
      <template #cell-reference="{ row, value }">
        <a :href="`/financial/quotations/${row.id}`" class="font-medium text-primary hover:underline">
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
          <a :href="`/financial/quotations/${row.id}`"
             class="px-2 py-1 text-xs text-app-text/50 hover:text-primary rounded transition-colors">
            View
          </a>
          <a v-if="row.status === 'draft'"
             :href="`/financial/quotations/${row.id}/edit`"
             class="px-2 py-1 text-xs text-app-text/50 hover:text-primary rounded transition-colors">
            Edit
          </a>
          <button v-if="row.status !== 'converted'"
                  @click="promptDelete(row.id)"
                  class="px-2 py-1 text-xs text-app-text/50 hover:text-red-500 rounded transition-colors">
            Delete
          </button>
        </div>
      </template>
    </DataTable>

    <ConfirmDialog
      :show="confirmDelete"
      title="Delete Quotation"
      message="This quotation will be permanently deleted."
      confirm-label="Delete"
      danger
      @confirm="handleDelete"
      @cancel="confirmDelete = false"
    />
  </div>
</template>
