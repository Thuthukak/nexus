<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  invoice: { type: Object, required: true },
})

const statusType = {
  paid: 'success', draft: 'neutral', overdue: 'danger',
  sent: 'info', approved: 'warning', cancelled: 'neutral', part_paid: 'warning',
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6 flex items-start justify-between">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <a href="/financial/invoices" class="text-sm text-primary hover:underline">← Invoices</a>
        </div>
        <h1 class="text-2xl font-bold text-app-text">{{ invoice.reference }}</h1>
        <div class="flex items-center gap-3 mt-2">
          <Badge :type="statusType[invoice.status]" dot>{{ invoice.status }}</Badge>
          <span class="text-sm text-app-text/50">Due {{ invoice.due_date }}</span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Customer -->
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-sm font-semibold text-app-text/50 uppercase tracking-wider mb-3">Bill To</h2>
          <p class="font-semibold text-app-text">{{ invoice.customer?.company_name }}</p>
          <p class="text-sm text-app-text/60">{{ invoice.customer?.email }}</p>
        </div>

        <!-- Line items -->
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
          <table class="w-full text-sm">
            <thead class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Description</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Qty</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Unit Price</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50 whitespace-nowrap">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-for="line in invoice.lines" :key="line.id">
                <td class="px-4 py-3 text-app-text">{{ line.description }}</td>
                <td class="px-4 py-3 text-right text-app-text/70">{{ line.qty }}</td>
                <td class="px-4 py-3 text-right text-app-text/70">R {{ Number(line.unit_price).toFixed(2) }}</td>
                <td class="px-4 py-3 text-right font-medium text-app-text">R {{ Number(line.line_total).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Totals -->
          <div class="border-t border-gray-100 dark:border-gray-800 px-4 py-4 space-y-2">
            <div class="flex justify-between text-sm text-app-text/60">
              <span>Subtotal</span>
              <span>R {{ Number(invoice.subtotal).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-sm text-app-text/60">
              <span>Tax</span>
              <span>R {{ Number(invoice.tax_total).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-base font-bold text-app-text border-t border-gray-100 dark:border-gray-800 pt-2 mt-2">
              <span>Total</span>
              <span>R {{ Number(invoice.total).toFixed(2) }}</span>
            </div>
            <div v-if="invoice.balance_due > 0" class="flex justify-between text-sm font-semibold text-red-500">
              <span>Balance Due</span>
              <span>R {{ Number(invoice.balance_due).toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-sm font-semibold text-app-text/50 uppercase tracking-wider mb-4">Details</h2>
          <dl class="space-y-3 text-sm">
            <div>
              <dt class="text-sm text-app-text/50">Issue Date</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ invoice.issue_date }}</dd>
            </div>
            <div>
              <dt class="text-sm text-app-text/50">Due Date</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ invoice.due_date }}</dd>
            </div>
            <div>
              <dt class="text-sm text-app-text/50">Created By</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ invoice.created_by }}</dd>
            </div>
          </dl>
        </div>

        <div v-if="invoice.notes" class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-sm font-semibold text-app-text/50 uppercase tracking-wider mb-2">Notes</h2>
          <p class="text-sm text-app-text/70">{{ invoice.notes }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
