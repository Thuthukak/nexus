<script setup>
import PortalLayout from '@shared/layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

const props = defineProps({
  invoice: { type: Object, required: true },
})

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

const statusColour = {
  paid:        'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900/30',
  part_paid:   'text-yellow-700 bg-yellow-100',
  sent:        'text-blue-700 bg-blue-100',
  overdue:     'text-red-700 bg-red-100',
  approved:    'text-purple-700 bg-purple-100',
  deposit_paid:'text-blue-700 bg-blue-100',
  cancelled:   'text-gray-500 bg-gray-100',
}
</script>

<template>
  <div class="max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <a href="/portal/invoices" class="text-sm text-primary hover:underline">← Invoices</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ invoice.reference }}</h1>
        <div class="flex items-center gap-3 mt-2">
          <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize"
                :class="statusColour[invoice.status] ?? 'text-gray-500 bg-gray-100'">
            {{ invoice.status }}
          </span>
          <span class="text-sm text-gray-500">Due {{ invoice.due_date }}</span>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a :href="`/portal/invoices/${invoice.id}/pdf`"
           class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 dark:border-gray-700 px-3 py-1.5 rounded-lg transition-colors">
          Download PDF
        </a>
        <a v-if="invoice.payment_url && invoice.balance_due > 0"
           :href="invoice.payment_url"
           class="text-sm font-semibold px-4 py-1.5 rounded-lg text-white"
           style="background-color: var(--color-primary);">
          {{ invoice.payment_stage }} — {{ currency(invoice.amount_due_now) }}
        </a>
      </div>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-gray-400 mb-1">Total</p>
        <p class="text-base font-bold text-gray-900 dark:text-white">{{ currency(invoice.total) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-gray-400 mb-1">Paid</p>
        <p class="text-base font-bold text-green-600">{{ currency(invoice.paid_total) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3"
           :class="invoice.balance_due > 0 ? 'border-red-200 dark:border-red-800' : ''">
        <p class="text-xs text-gray-400 mb-1">Balance Due</p>
        <p class="text-base font-bold" :class="invoice.balance_due > 0 ? 'text-red-600' : 'text-green-600'">
          {{ currency(invoice.balance_due) }}
        </p>
      </div>
    </div>

    <!-- Deposit notice -->
    <div v-if="invoice.deposit_required && !invoice.deposit_paid_at && invoice.balance_due > 0"
         class="mb-4 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl text-sm text-blue-700 dark:text-blue-400">
      A deposit of <strong>{{ currency(invoice.deposit_amount) }}</strong> is required to begin work.
    </div>

    <!-- Line items -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Line Items</h2>
      </div>
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Item</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Qty</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Price</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
          <tr v-for="line in invoice.lines" :key="line.id">
            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ line.description }}</td>
            <td class="px-5 py-3 text-right text-gray-500">{{ line.qty }}</td>
            <td class="px-5 py-3 text-right text-gray-500">{{ currency(line.unit_price) }}</td>
            <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white">{{ currency(line.line_total) }}</td>
          </tr>
        </tbody>
      </table>
      <div class="border-t border-gray-200 dark:border-gray-700 px-5 py-4 bg-gray-50 dark:bg-gray-800/50 space-y-2">
        <div class="flex justify-between text-sm text-gray-500">
          <span>Subtotal</span><span>{{ currency(invoice.subtotal) }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-500">
          <span>Tax</span><span>{{ currency(invoice.tax_total) }}</span>
        </div>
        <div class="flex justify-between text-base font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
          <span>Total</span><span>{{ currency(invoice.total) }}</span>
        </div>
      </div>
    </div>

    <!-- Payment history -->
    <div v-if="invoice.payments?.length"
         class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Payment History</h2>
      </div>
      <div class="divide-y divide-gray-50 dark:divide-gray-800">
        <div v-for="p in invoice.payments" :key="p.paid_at"
             class="flex items-center justify-between px-5 py-3 text-sm">
          <div>
            <p class="font-medium text-gray-900 dark:text-white capitalize">{{ p.method?.replace('_', ' ') }}</p>
            <p class="text-xs text-gray-400">{{ p.paid_at }}</p>
          </div>
          <p class="font-semibold text-green-600">{{ currency(p.amount) }}</p>
        </div>
      </div>
    </div>

    <!-- Notes -->
    <div v-if="invoice.notes"
         class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
      <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Notes</h2>
      <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ invoice.notes }}</p>
    </div>
  </div>
</template>
