<script setup>
import PortalLayout from '@shared/layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

const props = defineProps({
  customer:         { type: Object, required: true },
  stats:            { type: Object, required: true },
  recentInvoices:   { type: Array,  default: () => [] },
  pendingQuotes:    { type: Array,  default: () => [] },
  upcomingBookings: { type: Array,  default: () => [] },
})

const statusColour = {
  paid:        'text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
  part_paid:   'text-yellow-700 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400',
  sent:        'text-blue-700 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
  overdue:     'text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-400',
  draft:       'text-gray-600 bg-gray-100 dark:bg-gray-800 dark:text-gray-400',
  approved:    'text-purple-700 bg-purple-100',
  deposit_paid:'text-blue-700 bg-blue-100',
  cancelled:   'text-gray-500 bg-gray-100',
  accepted:    'text-green-700 bg-green-100',
  declined:    'text-red-700 bg-red-100',
  converted:   'text-purple-700 bg-purple-100',
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div>
    <!-- Greeting -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        Welcome back, {{ customer.contact_name ?? customer.company_name }}
      </h1>
      <p class="text-gray-500 mt-1 text-sm">Here's an overview of your account.</p>
    </div>

    <!-- Overdue banner -->
    <div v-if="stats.overdue_count > 0"
         class="mb-6 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-5">
      <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
      </svg>
      <div>
        <p class="text-sm font-semibold text-red-700 dark:text-red-400">
          You have {{ stats.overdue_count }} overdue invoice{{ stats.overdue_count > 1 ? 's' : '' }}
        </p>
        <p class="text-sm text-red-600 dark:text-red-500 mt-0.5">
          Total outstanding: <strong>{{ currency(stats.overdue_amount) }}</strong>.
          Please make payment as soon as possible.
        </p>
        <a href="/portal/invoices"
           class="inline-block mt-2 text-sm font-semibold text-red-700 dark:text-red-400 underline">
          View overdue invoices →
        </a>
      </div>
    </div>

    <!-- Pending quote banner -->
    <div v-if="stats.pending_quotes > 0"
         class="mb-6 flex items-start gap-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5">
      <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <div>
        <p class="text-sm font-semibold text-blue-700 dark:text-blue-400">
          {{ stats.pending_quotes }} quotation{{ stats.pending_quotes > 1 ? 's' : '' }} awaiting your response
        </p>
        <a href="/portal/quotations"
           class="inline-block mt-1 text-sm font-semibold text-blue-700 dark:text-blue-400 underline">
          Review quotations →
        </a>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4">
        <p class="text-xs text-gray-500 mb-1">Outstanding</p>
        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ currency(stats.outstanding) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4">
        <p class="text-xs text-gray-500 mb-1">Total Paid</p>
        <p class="text-xl font-bold text-green-600">{{ currency(stats.paid_total) }}</p>
      </div>
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-5 py-4 col-span-2 sm:col-span-1">
        <p class="text-xs text-gray-500 mb-1">Pending Quotes</p>
        <p class="text-xl font-bold text-blue-600">{{ stats.pending_quotes }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent invoices -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Invoices</h2>
          <a href="/portal/invoices" class="text-xs text-primary hover:underline">View all →</a>
        </div>
        <div v-if="!recentInvoices.length" class="px-5 py-8 text-center text-sm text-gray-400">
          No invoices yet.
        </div>
        <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="inv in recentInvoices" :key="inv.id"
               class="flex items-center justify-between px-5 py-3">
            <div>
              <a :href="`/portal/invoices/${inv.id}`"
                 class="text-sm font-medium text-primary hover:underline">
                {{ inv.reference }}
              </a>
              <p class="text-xs text-gray-400 mt-0.5">Due {{ inv.due_date }}</p>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize"
                    :class="statusColour[inv.status] ?? 'text-gray-500 bg-gray-100'">
                {{ inv.status }}
              </span>
              <span class="text-sm font-semibold text-gray-900 dark:text-white">
                R{{ Number(inv.balance_due ?? inv.total).toLocaleString('en-ZA', {minimumFractionDigits: 2}) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending quotations -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Pending Quotations</h2>
          <a href="/portal/quotations" class="text-xs text-primary hover:underline">View all →</a>
        </div>
        <div v-if="!pendingQuotes.length" class="px-5 py-8 text-center text-sm text-gray-400">
          No pending quotations.
        </div>
        <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="q in pendingQuotes" :key="q.id"
               class="flex items-center justify-between px-5 py-3">
            <div>
              <a :href="`/portal/quotations/${q.id}`"
                 class="text-sm font-medium text-primary hover:underline">
                {{ q.reference }}
              </a>
              <p class="text-xs text-gray-400 mt-0.5">Valid until {{ q.valid_until }}</p>
            </div>
            <div class="flex items-center gap-3">
              <a :href="q.quote_url" target="_blank"
                 class="text-xs font-semibold text-white px-3 py-1 rounded-lg"
                 style="background-color: var(--color-primary);">
                Respond
              </a>
              <span class="text-sm font-semibold text-gray-900 dark:text-white">
                R{{ Number(q.total).toLocaleString('en-ZA', {minimumFractionDigits: 2}) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Upcoming bookings -->
      <div v-if="upcomingBookings.length"
           class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Upcoming Bookings</h2>
          <a href="/portal/bookings" class="text-xs text-primary hover:underline">View all →</a>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="b in upcomingBookings" :key="b.id"
               class="flex items-center justify-between px-5 py-3">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ b.service }}</p>
              <p class="text-xs text-gray-400">{{ b.start_at }}</p>
            </div>
            <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize"
                  :class="b.status === 'confirmed'
                    ? 'text-green-700 bg-green-100'
                    : 'text-yellow-700 bg-yellow-100'">
              {{ b.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
