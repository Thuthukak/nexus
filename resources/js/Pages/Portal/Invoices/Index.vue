<script setup>
import PortalLayout from '@shared/layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

const props = defineProps({
  invoices: { type: Array, default: () => [] },
})

const statusColour = {
  paid:        'text-green-700 bg-green-100',
  part_paid:   'text-yellow-700 bg-yellow-100',
  sent:        'text-blue-700 bg-blue-100',
  overdue:     'text-red-700 bg-red-100',
  approved:    'text-purple-700 bg-purple-100',
  deposit_paid:'text-blue-700 bg-blue-100',
  cancelled:   'text-gray-500 bg-gray-100',
  draft:       'text-gray-500 bg-gray-100',
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoices</h1>
      <p class="text-sm text-gray-500 mt-1">Your invoice history</p>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
      <div v-if="!invoices.length"
            class="px-6 py-16 text-center text-gray-400">
        No invoices yet.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="dark:bg-gray-800 border-b border-gray-200 bg-primary text-white dark:border-gray-700">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Invoice</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Issue Date</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Due Date</th>
              <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Total</th>
              <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Balance</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
              <th class="px-5 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-for="inv in invoices" :key="inv.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
              <td class="px-5 py-3.5">
                <a :href="`/portal/invoices/${inv.id}`"
                    class="font-semibold text-primary hover:underline">
                  {{ inv.reference }}
                </a>
              </td>
              <td class="px-5 py-3.5 text-gray-500">{{ inv.issue_date }}</td>
              <td class="px-5 py-3.5 text-gray-500">{{ inv.due_date }}</td>
              <td class="px-5 py-3.5 text-right font-medium text-gray-900 dark:text-white">
                {{ currency(inv.total) }}
              </td>
              <td class="px-5 py-3.5 text-right font-semibold"
                  :class="inv.balance_due > 0 ? 'text-red-600' : 'text-green-600'">
                {{ currency(inv.balance_due) }}
              </td>
              <td class="px-5 py-3.5">
                <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize"
                      :class="statusColour[inv.status] ?? 'text-gray-500 bg-gray-100'">
                  {{ inv.status }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-right">
                <div class="flex items-center justify-end gap-2">
                  <a v-if="inv.payment_url && inv.balance_due > 0"
                      :href="inv.payment_url"
                      class="text-xs font-semibold px-3 py-1.5 rounded-lg text-white"
                      style="background-color: var(--color-primary);">
                    Pay Now
                  </a>
                  <a :href="`/portal/invoices/${inv.id}`"
                      class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    View
                  </a>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
