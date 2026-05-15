<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })
defineProps({ stats: { type: Object, required: true } })

const cards = [
  { label: 'Total Employees',  key: 'total_employees',  color: '#1E3A5F' },
  { label: 'On Leave Today',   key: 'on_leave_today',   color: '#F39C12' },
  { label: 'Pending Leave',    key: 'pending_leave',    color: '#EF4444' },
  { label: 'New This Month',   key: 'new_this_month',   color: '#10B981' },
]
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Human Resources</h1>
        <p class="text-sm text-app-text/60 mt-1">Employee and leave management overview</p>
      </div>
      <a href="/hr/employees/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90 transition-opacity">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Employee
      </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div v-for="card in cards" :key="card.key"
           class="bg-surface rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
        <p class="text-sm font-medium text-app-text/60 mb-3">{{ card.label }}</p>
        <p class="text-2xl font-bold text-app-text">{{ stats[card.key] ?? 0 }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
          <h2 class="text-sm font-semibold text-app-text">Quick Links</h2>
        </div>
        <div class="p-6 grid grid-cols-2 gap-3">
          <a v-for="link in quickLinks" :key="link.href" :href="link.href"
             class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 dark:border-gray-700 hover:border-primary/30 hover:bg-primary/5 transition-colors">
            <span class="text-sm font-medium text-app-text">{{ link.label }}</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const quickLinks = [
  { label: 'All Employees',   href: '/hr/employees' },
  { label: 'Leave Requests',  href: '/hr/leave' },
  { label: 'Departments',     href: '/hr/departments' },
  { label: 'Apply for Leave', href: '/hr/leave/apply' },
]
</script>
