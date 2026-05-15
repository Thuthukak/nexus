<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import DataTable from '@shared/components/data/DataTable.vue'
import Badge from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })
defineProps({ employees: { type: Array, default: () => [] } })

const columns = [
  { key: 'name',            label: 'Name',            sortable: true },
  { key: 'department',      label: 'Department',      sortable: true },
  { key: 'job_title',       label: 'Job Title',       sortable: true },
  { key: 'employment_type', label: 'Type',            sortable: true },
  { key: 'status',          label: 'Status',          sortable: true },
  { key: 'start_date',      label: 'Start Date',      sortable: true },
]

const statusType = {
  active: 'success', on_leave: 'warning',
  suspended: 'danger', terminated: 'neutral',
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Employees</h1>
        <p class="text-sm text-app-text/60 mt-1">{{ employees.length }} total employees</p>
      </div>
      <a href="/hr/employees/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Employee
      </a>
    </div>

    <DataTable :columns="columns" :rows="employees" empty-message="No employees yet.">
      <template #cell-status="{ value }">
        <Badge :type="statusType[value] ?? 'neutral'" dot>{{ value }}</Badge>
      </template>
      <template #cell-employment_type="{ value }">
        <span class="text-app-text/70 capitalize">{{ value?.replace('_', ' ') }}</span>
      </template>
      <template #cell-name="{ row, value }">
        <a :href="`/hr/employees/${row.id}`" class="font-medium text-primary hover:underline">
          {{ value }}
        </a>
      </template>
    </DataTable>
  </div>
</template>
