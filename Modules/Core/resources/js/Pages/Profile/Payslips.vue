<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  payslips: { type: Array, default: () => [] },
})

function currency(val) {
  if (! val) return '—'
  return 'R ' + Number(val).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div class="max-w-3xl">
    <div class="mb-6">
      <a href="/profile" class="text-sm text-primary hover:underline">← Profile</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">My Payslips</h1>
      <p class="text-sm text-app-text/60 mt-1">Your payslip history</p>
    </div>

    <div v-if="!payslips.length"
         class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-6 py-14 text-center">
      <svg class="w-10 h-10 text-app-text/20 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <p class="text-sm text-app-text/40">No payslips available yet.</p>
    </div>

    <div v-else class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-800">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Period</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Gross</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Net</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Uploaded</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
          <tr v-for="slip in payslips" :key="slip.id"
              class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
            <td class="px-5 py-3.5 font-medium text-app-text">{{ slip.period_label }}</td>
            <td class="px-5 py-3.5 text-right text-app-text/70">{{ currency(slip.gross_amount) }}</td>
            <td class="px-5 py-3.5 text-right font-medium text-app-text">{{ currency(slip.net_amount) }}</td>
            <td class="px-5 py-3.5 text-right text-xs text-app-text/40">{{ slip.created_at }}</td>
            <td class="px-5 py-3.5 text-right">
              <a :href="`/hr/my-payslips/${slip.id}/download`"
                 class="text-xs font-medium text-primary hover:underline">
                Download
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
