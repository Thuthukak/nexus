<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge     from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })

defineProps({
  event:  { type: Object, required: true },
  orders: { type: Array,  default: () => [] },
  stats:  { type: Object, default: () => ({}) },
})

const statusType = {
  pending:  'warning',
  paid:     'success',
  cancelled:'neutral',
  refunded: 'danger',
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div class="max-w-5xl">
    <div class="mb-6">
      <a :href="`/events-admin/events/${event.id}/edit`"
         class="text-sm text-primary hover:underline">← {{ event.title }}</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Orders</h1>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3 text-center">
        <p class="text-2xl font-bold text-app-text">{{ stats.total_orders }}</p>
        <p class="text-xs text-app-text/50 mt-1">Total Orders</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3 text-center">
        <p class="text-2xl font-bold text-green-600">{{ stats.paid_orders }}</p>
        <p class="text-xs text-app-text/50 mt-1">Paid</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3 text-center">
        <p class="text-2xl font-bold text-app-text">{{ stats.tickets_sold }}</p>
        <p class="text-xs text-app-text/50 mt-1">Tickets Sold</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3 text-center">
        <p class="text-xl font-bold text-green-600">{{ currency(stats.total_revenue) }}</p>
        <p class="text-xs text-app-text/50 mt-1">Revenue</p>
      </div>
    </div>

    <!-- Orders table -->
    <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
      <div v-if="!orders.length"
           class="px-6 py-10 text-center text-app-text/40 text-sm">
        No orders yet.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 bg-primary text-white dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-800">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Order</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Customer</th>
              <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-app-text/50">Tickets</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Total</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Date</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-for="order in orders" :key="order.id"
                class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
              <td class="px-4 py-3 font-mono text-xs font-semibold text-app-text">
                {{ order.reference }}
              </td>
              <td class="px-4 py-3">
                <p class="font-medium text-app-text text-sm">{{ order.customer_name }}</p>
                <p class="text-xs text-app-text/50">{{ order.customer_email }}</p>
              </td>
              <td class="px-4 py-3 text-center text-app-text/70">{{ order.tickets_count }}</td>
              <td class="px-4 py-3 text-right font-semibold text-app-text">
                {{ currency(order.total) }}
              </td>
              <td class="px-4 py-3">
                <Badge :type="statusType[order.status] ?? 'neutral'" dot>
                  {{ order.status }}
                </Badge>
              </td>
              <td class="px-4 py-3 text-xs text-app-text/50">{{ order.created_at }}</td>
              <td class="px-4 py-3 text-right">
                <a v-if="order.status === 'paid'"
                   :href="`/events-admin/events/${event.id}/orders/${order.id}/download`"
                   class="text-xs text-primary hover:underline">
                  Download Tickets
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
