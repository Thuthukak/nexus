<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge     from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })
defineProps({
  stats:          { type: Object, required: true },
  recentInvoices: { type: Array,  default: () => [] },
  recentPayments: { type: Array,  default: () => [] },
})

const statusType = {
  paid: 'success', draft: 'neutral', overdue: 'danger',
  sent: 'info', approved: 'warning', part_paid: 'warning', cancelled: 'neutral',
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
        <h1 class="text-2xl font-bold text-app-text">Financial</h1>
        <p class="text-sm text-app-text/60 mt-1">Overview of your financial activity</p>
      </div>
      <a href="/financial/invoices/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Invoice
      </a>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
      <div class="bg-surface rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
        <p class="text-sm font-medium text-app-text/60 mb-1">Outstanding</p>
        <p class="text-2xl font-bold text-app-text">{{ currency(stats.total_outstanding) }}</p>
        <p class="text-xs text-app-text/40 mt-1">Across all unpaid invoices</p>
      </div>
      <div class="bg-surface rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
        <p class="text-sm font-medium text-app-text/60 mb-1">Paid This Month</p>
        <p class="text-2xl font-bold text-green-600">{{ currency(stats.paid_this_month) }}</p>
        <p class="text-xs text-app-text/40 mt-1">Payments received this month</p>
      </div>
      <div class="bg-surface rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
        <p class="text-sm font-medium text-app-text/60 mb-1">Overdue</p>
        <p class="text-2xl font-bold text-red-500">{{ currency(stats.overdue_amount) }}</p>
        <p class="text-xs text-app-text/40 mt-1">{{ stats.overdue_count }} overdue invoice(s)</p>
      </div>
      <div class="bg-surface rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
        <p class="text-sm font-medium text-app-text/60 mb-1">Draft Invoices</p>
        <p class="text-2xl font-bold text-app-text">{{ stats.draft_count }}</p>
        <p class="text-xs text-app-text/40 mt-1">Awaiting approval</p>
      </div>
      <div class="bg-surface rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
        <p class="text-sm font-medium text-app-text/60 mb-1">Total Customers</p>
        <p class="text-2xl font-bold text-app-text">{{ stats.total_customers }}</p>
        <p class="text-xs text-app-text/40 mt-1">Active customer accounts</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent invoices -->
      <div class="lg:col-span-2 bg-surface rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-app-text">Recent Invoices</h2>
          <a href="/financial/invoices" class="text-xs text-primary hover:underline">View all</a>
        </div>

        <div v-if="!recentInvoices.length" class="px-6 py-10 text-center text-app-text/40 text-sm">
          No invoices yet.
        </div>

        <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="inv in recentInvoices" :key="inv.id"
               class="flex items-center justify-between px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <div class="flex items-center gap-4 min-w-0">
              <div class="min-w-0">
                <a :href="`/financial/invoices/${inv.id}`"
                   class="text-sm font-medium text-primary hover:underline">
                  {{ inv.reference }}
                </a>
                <p class="text-xs text-app-text/50 truncate">{{ inv.customer }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4 flex-shrink-0">
              <Badge :type="statusType[inv.status] ?? 'neutral'" dot>{{ inv.status }}</Badge>
              <span class="text-sm font-semibold text-app-text w-28 text-right">
                {{ currency(inv.total) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent payments -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
          <h2 class="text-sm font-semibold text-app-text">Recent Payments</h2>
        </div>

        <div v-if="!recentPayments.length" class="px-6 py-10 text-center text-app-text/40 text-sm">
          No payments recorded yet.
        </div>

        <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="p in recentPayments" :key="p.id"
               class="px-6 py-3">
            <div class="flex items-center justify-between mb-0.5">
              <span class="text-sm font-medium text-app-text">{{ currency(p.amount) }}</span>
              <span class="text-xs text-app-text/40">{{ p.paid_at }}</span>
            </div>
            <p class="text-xs text-app-text/50 truncate">
              {{ p.customer }} · {{ p.reference }}
            </p>
            <p class="text-xs text-app-text/30 capitalize mt-0.5">{{ p.method?.replace('_', ' ') }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
