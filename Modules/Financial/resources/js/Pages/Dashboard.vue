<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })
defineProps({
  stats: { type: Object, required: true },
})

const statCards = [
  { label: 'Total Invoices',   key: 'total_invoices',  color: '#1E3A5F', prefix: '',  suffix: '' },
  { label: 'Outstanding',      key: 'outstanding',     color: '#F39C12', prefix: 'R', suffix: '' },
  { label: 'Paid This Month',  key: 'paid_this_month', color: '#10B981', prefix: 'R', suffix: '' },
  { label: 'Overdue',          key: 'overdue',         color: '#EF4444', prefix: '',  suffix: '' },
]
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Financial</h1>
        <p class="text-sm text-app-text/60 mt-1">Overview of your financial activity</p>
      </div>
      <a href="/financial/invoices/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90 transition-opacity">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Invoice
      </a>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div
        v-for="card in statCards"
        :key="card.key"
        class="bg-surface rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-800"
      >
        <p class="text-sm font-medium text-app-text/60 mb-3">{{ card.label }}</p>
        <p class="text-2xl font-bold text-app-text">
          {{ card.prefix }}{{ stats[card.key] ?? 0 }}{{ card.suffix }}
        </p>
      </div>
    </div>

    <!-- Recent invoices placeholder -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-app-text">Recent Invoices</h2>
        <a href="/financial/invoices" class="text-xs text-primary hover:underline">View all</a>
      </div>
      <div class="px-6 py-12 text-center text-app-text/40 text-sm">
        No invoices yet. Create your first invoice to get started.
      </div>
    </div>
  </div>
</template>
