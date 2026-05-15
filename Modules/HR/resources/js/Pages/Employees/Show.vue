<script setup>
import { computed } from 'vue' // Move this import up here
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge from '@shared/components/display/Badge.vue'
import DataTable from '@shared/components/data/DataTable.vue'

// 1. Define Options and Props
defineOptions({ layout: AppLayout })

const props = defineProps({ 
  employee: { type: Object, required: true } 
})

// 2. Constants / Config
const statusType = {
  active: 'success', on_leave: 'warning',
  suspended: 'danger', terminated: 'neutral',
}

const leaveStatusType = {
  approved: 'success', pending: 'warning',
  rejected: 'danger',  cancelled: 'neutral',
}

const leaveColumns = [
  { key: 'type',       label: 'Leave Type', sortable: true },
  { key: 'start_date', label: 'From',       sortable: true },
  { key: 'end_date',   label: 'To',         sortable: true },
  { key: 'days',       label: 'Days',       sortable: true },
  { key: 'status',     label: 'Status',     sortable: true },
]

// 3. Computed Properties (using 'props' defined above)
const details = computed(() => [
  { label: 'Email',           value: props.employee.email },
  { label: 'Phone',           value: props.employee.phone },
  { label: 'Department',      value: props.employee.department },
  { label: 'Job Title',       value: props.employee.job_title },
  { label: 'Employment Type', value: props.employee.employment_type?.replace('_', ' ') },
  { label: 'Start Date',      value: props.employee.start_date },
])
</script>

<template>
  <div>
    <div class="mb-6">
      <a href="/hr/employees" class="text-sm text-primary hover:underline">← Employees</a>
      <div class="flex items-center gap-4 mt-2">
        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center">
          <span class="text-white text-lg font-bold">
            {{ employee.name?.charAt(0)?.toUpperCase() }}
          </span>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-app-text">{{ employee.name }}</h1>
          <div class="flex items-center gap-3 mt-1">
            <Badge :type="statusType[employee.status]" dot>{{ employee.status }}</Badge>
            <span class="text-sm text-app-text/50">{{ employee.employee_number }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-1 space-y-6">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Details</h2>
          <dl class="space-y-3">
            <div v-for="field in details" :key="field.label">
              <dt class="text-xs text-app-text/50">{{ field.label }}</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ field.value ?? '—' }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <div class="lg:col-span-2 space-y-6">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
            <h2 class="text-sm font-semibold text-app-text">Leave History</h2>
            <a href="/hr/leave/apply" class="text-xs text-primary hover:underline">Apply for leave</a>
          </div>
          <DataTable :columns="leaveColumns" :rows="employee.leave" empty-message="No leave history.">
            <template #cell-status="{ value }">
              <Badge :type="leaveStatusType[value] ?? 'neutral'" dot>{{ value }}</Badge>
            </template>
          </DataTable>
        </div>
      </div>
    </div>
  </div>
</template>

