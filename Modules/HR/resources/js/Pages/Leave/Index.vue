<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import DataTable from '@shared/components/data/DataTable.vue'
import Badge from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })
defineProps({ applications: { type: Array, default: () => [] } })

const columns = [
  { key: 'employee',   label: 'Employee',   sortable: true },
  { key: 'type',       label: 'Leave Type', sortable: true },
  { key: 'start_date', label: 'From',       sortable: true },
  { key: 'end_date',   label: 'To',         sortable: true },
  { key: 'days',       label: 'Days',       sortable: true },
  { key: 'status',     label: 'Status',     sortable: true },
]

const statusType = {
  approved: 'success', pending: 'warning',
  rejected: 'danger',  cancelled: 'neutral',
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Leave Management</h1>
        <p class="text-sm text-app-text/60 mt-1">Review and manage leave applications</p>
      </div>
      <a href="/hr/leave/apply"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Apply for Leave
      </a>
    </div>

    <DataTable :columns="columns" :rows="applications" empty-message="No leave applications.">
      <template #cell-status="{ value }">
        <Badge :type="statusType[value] ?? 'neutral'" dot>{{ value }}</Badge>
      </template>
    </DataTable>
  </div>
</template>
