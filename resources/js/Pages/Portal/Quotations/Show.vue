<script setup>
import PortalLayout from '@shared/layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

const props = defineProps({
  quotation: { type: Object, required: true },
})

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div class="max-w-3xl">
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <a href="/portal/quotations" class="text-sm text-primary hover:underline">← Quotations</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ quotation.reference }}</h1>
        <p class="text-sm text-gray-500 mt-1">Valid until {{ quotation.valid_until }}</p>
      </div>

      <!-- CTA for pending quotes -->
      <a v-if="quotation.status === 'sent' && quotation.quote_url && !quotation.is_expired"
         :href="quotation.quote_url"
         class="flex-shrink-0 px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
         style="background-color: var(--color-primary);">
        Accept or Decline →
      </a>
    </div>

    <!-- Status banners -->
    <div v-if="quotation.accepted_at"
         class="mb-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-semibold">
      ✓ You accepted this quotation on {{ quotation.accepted_at }}.
    </div>
    <div v-if="quotation.declined_at"
         class="mb-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600 font-semibold">
      ✗ You declined this quotation on {{ quotation.declined_at }}.
    </div>
    <div v-if="quotation.is_expired && quotation.status === 'sent'"
         class="mb-4 px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-xl text-sm text-yellow-700">
      This quotation has expired. Please contact us for an updated quotation.
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-gray-400 mb-1">Subtotal</p>
        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ currency(quotation.subtotal) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-gray-400 mb-1">Tax</p>
        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ currency(quotation.tax_total) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-gray-400 mb-1">Total</p>
        <p class="text-base font-bold text-gray-900 dark:text-white">{{ currency(quotation.total) }}</p>
      </div>
    </div>

    <!-- Line items -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
      <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Items</h2>
      </div>
      <table class="w-full text-sm">
        <thead class="bg-primary text-white dark:bg-gray-800">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Description</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Qty</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Price</th>
            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
          <tr v-for="line in quotation.lines" :key="line.id">
            <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ line.description }}</td>
            <td class="px-5 py-3 text-right text-gray-500">{{ line.qty }}</td>
            <td class="px-5 py-3 text-right text-gray-500">{{ currency(line.unit_price) }}</td>
            <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white">{{ currency(line.line_total) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Notes + Terms -->
    <div v-if="quotation.notes || quotation.terms" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div v-if="quotation.notes"
           class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Notes</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ quotation.notes }}</p>
      </div>
      <div v-if="quotation.terms"
           class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Terms & Conditions</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ quotation.terms }}</p>
      </div>
    </div>
  </div>
</template>
