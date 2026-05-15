<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })
defineProps({
  stats:          { type: Object, required: true },
  todaysBookings: { type: Array,  default: () => [] },
})

const statusType = {
  confirmed: 'success', pending: 'warning',
  cancelled: 'danger',  completed: 'neutral', in_progress: 'info',
}

const statCards = [
  { label: "Today's Bookings", key: 'today',     color: '#1E3A5F' },
  { label: 'Upcoming',         key: 'upcoming',  color: '#2E86AB' },
  { label: 'Pending Approval', key: 'pending',   color: '#F39C12' },
  { label: 'Completed (Month)',key: 'completed', color: '#10B981' },
]
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Bookings</h1>
        <p class="text-sm text-app-text/60 mt-1">Appointment and resource booking overview</p>
      </div>
      <a href="/bookings/bookings/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Booking
      </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div v-for="card in statCards" :key="card.key"
           class="bg-surface rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-800">
        <p class="text-sm font-medium text-app-text/60 mb-3">{{ card.label }}</p>
        <p class="text-2xl font-bold text-app-text">{{ stats[card.key] ?? 0 }}</p>
      </div>
    </div>

    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
        <h2 class="text-sm font-semibold text-app-text">Today's Schedule</h2>
        <a href="/bookings/bookings" class="text-xs text-primary hover:underline">View all</a>
      </div>

      <div v-if="!todaysBookings.length" class="px-6 py-12 text-center text-app-text/40 text-sm">
        No bookings scheduled for today.
      </div>

      <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
        <div v-for="booking in todaysBookings" :key="booking.id"
             class="px-6 py-4 flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="text-center w-16">
              <p class="text-sm font-bold text-app-text">{{ booking.start_at }}</p>
              <p class="text-xs text-app-text/40">{{ booking.end_at }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-app-text">{{ booking.customer }}</p>
              <p class="text-xs text-app-text/50">{{ booking.service }}
                <span v-if="booking.resource"> · {{ booking.resource }}</span>
              </p>
            </div>
          </div>
          <Badge :type="statusType[booking.status] ?? 'neutral'" dot>
            {{ booking.status }}
          </Badge>
        </div>
      </div>
    </div>
  </div>
</template>
