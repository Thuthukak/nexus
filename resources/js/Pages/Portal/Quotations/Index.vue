<script setup>
import PortalLayout from '@shared/layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

defineProps({
  quotations: { type: Array, default: () => [] },
})

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

const statusColour = {
  draft:     'text-gray-500 bg-gray-100',
  sent:      'text-blue-700 bg-blue-100',
  accepted:  'text-green-700 bg-green-100',
  declined:  'text-red-700 bg-red-100',
  expired:   'text-gray-500 bg-gray-100',
  converted: 'text-purple-700 bg-purple-100',
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Quotations</h1>
      <p class="text-sm text-gray-500 mt-1">Quotations sent to you for review</p>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
      <div v-if="!quotations.length" class="px-6 py-16 text-center text-gray-400 text-sm">
        No quotations yet.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Reference</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Issue Date</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Valid Until</th>
              <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Total</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
              <th class="px-5 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-for="q in quotations" :key="q.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td class="px-5 py-3.5">
                <a :href="`/portal/quotations/${q.id}`"
                   class="font-semibold text-primary hover:underline">
                  {{ q.reference }}
                </a>
              </td>
              <td class="px-5 py-3.5 text-gray-500">{{ q.issue_date }}</td>
              <td class="px-5 py-3.5 text-gray-500">
                <span :class="q.is_expired ? 'text-red-500' : ''">{{ q.valid_until }}</span>
              </td>
              <td class="px-5 py-3.5 text-right font-semibold text-gray-900 dark:text-white">
                {{ currency(q.total) }}
              </td>
              <td class="px-5 py-3.5">
                <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize"
                      :class="statusColour[q.status] ?? 'text-gray-500 bg-gray-100'">
                  {{ q.status }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-right">
                <div class="flex items-center justify-end gap-2">
                  <a v-if="q.status === 'sent' && q.quote_url"
                     :href="q.quote_url"
                     class="text-xs font-semibold px-3 py-1 rounded-lg text-white"
                     style="background-color: var(--color-primary);">
                    Respond
                  </a>
                  <a :href="`/portal/quotations/${q.id}`"
                     class="text-xs text-gray-500 hover:text-gray-700">
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
