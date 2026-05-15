<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import DataTable from '@shared/components/data/DataTable.vue'
import Badge from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })
defineProps({ bookings: { type: Array, default: () => [] } })

const columns = [
  { key: 'reference', label: 'Reference', sortable: true },
  { key: 'customer',  label: 'Customer',  sortable: true },
  { key: 'service',   label: 'Service',   sortable: true },
  { key: 'resource',  label: 'Resource',  sortable: true },
  { key: 'start_at',  label: 'Date/Time', sortable: true },
  { key: 'status',    label: 'Status',    sortable: true },
]

const statusType = {
  confirmed: 'success', pending: 'warning', cancelled: 'danger',
  completed: 'neutral', in_progress: 'info', no_show: 'danger',
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">All Bookings</h1>
        <p class="text-sm text-app-text/60 mt-1">{{ bookings.length }} total bookings</p>
      </div>
      <a href="/bookings/bookings/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Booking
      </a>
    </div>

    <DataTable :columns="columns" :rows="bookings" empty-message="No bookings found.">
      <template #cell-status="{ value }">
        <Badge :type="statusType[value] ?? 'neutral'" dot>{{ value }}</Badge>
      </template>
      <template #cell-reference="{ row, value }">
        <a :href="`/bookings/bookings/${row.id}`" class="font-medium text-primary hover:underline">
          {{ value }}
        </a>
      </template>
    </DataTable>
  </div>
</template>
