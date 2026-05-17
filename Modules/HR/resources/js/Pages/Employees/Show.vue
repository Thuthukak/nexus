<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge     from '@shared/components/display/Badge.vue'
import DataTable from '@shared/components/data/DataTable.vue'

defineOptions({ layout: AppLayout })
defineProps({ employee: { type: Object, required: true } })

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
  { key: 'days',       label: 'Days',       sortable: false },
  { key: 'status',     label: 'Status',     sortable: true },
]
</script>

<template>
  <div class="max-w-5xl">
    <!-- Header -->
    <div class="mb-6">
      <a href="/hr/employees" class="text-sm text-primary hover:underline">← Employees</a>
      <div class="flex items-start justify-between mt-3 gap-4 flex-wrap">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
            <span class="text-white text-xl font-bold">
              {{ employee.name?.charAt(0)?.toUpperCase() }}
            </span>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-app-text">{{ employee.name }}</h1>
            <div class="flex items-center gap-3 mt-1 flex-wrap">
              <Badge :type="statusType[employee.status]" dot>{{ employee.status }}</Badge>
              <span class="text-sm text-app-text/50">{{ employee.employee_number }}</span>
              <span v-if="employee.job_title" class="text-sm text-app-text/50">
                {{ employee.job_title }}
              </span>
            </div>
          </div>
        </div>
        <a :href="`/hr/leave/apply`"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
          Apply for Leave
        </a>
      </div>
    </div>

    <!-- Detail cards row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Contact</h2>
        <dl class="space-y-2 text-sm">
          <div v-if="employee.email">
            <dt class="text-xs text-app-text/40">Email</dt>
            <dd class="font-medium text-app-text mt-0.5 truncate">{{ employee.email }}</dd>
          </div>
          <div v-if="employee.phone">
            <dt class="text-xs text-app-text/40">Phone</dt>
            <dd class="font-medium text-app-text mt-0.5">{{ employee.phone }}</dd>
          </div>
        </dl>
      </div>

      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Employment</h2>
        <dl class="space-y-2 text-sm">
          <div>
            <dt class="text-xs text-app-text/40">Type</dt>
            <dd class="font-medium text-app-text mt-0.5 capitalize">
              {{ employee.employment_type?.replace('_', ' ') }}
            </dd>
          </div>
          <div>
            <dt class="text-xs text-app-text/40">Start Date</dt>
            <dd class="font-medium text-app-text mt-0.5">{{ employee.start_date }}</dd>
          </div>
        </dl>
      </div>

      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Organisation</h2>
        <dl class="space-y-2 text-sm">
          <div v-if="employee.department">
            <dt class="text-xs text-app-text/40">Department</dt>
            <dd class="font-medium text-app-text mt-0.5">{{ employee.department }}</dd>
          </div>
          <div v-if="employee.job_title">
            <dt class="text-xs text-app-text/40">Job Title</dt>
            <dd class="font-medium text-app-text mt-0.5">{{ employee.job_title }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Leave history — full width -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-app-text">Leave History</h2>
        <span class="text-xs text-app-text/40">{{ employee.leave?.length ?? 0 }} applications</span>
      </div>
      <DataTable :columns="leaveColumns" :rows="employee.leave ?? []"
                 empty-message="No leave history for this employee.">
        <template #cell-status="{ value }">
          <Badge :type="leaveStatusType[value] ?? 'neutral'" dot>{{ value }}</Badge>
        </template>
        <template #cell-days="{ value }">
          <span class="text-app-text/70">{{ value }} day{{ value !== 1 ? 's' : '' }}</span>
        </template>
      </DataTable>
    </div>
  </div>
</template>
