<script setup>
import PortalLayout from '@shared/layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

const props = defineProps({
  bookings: { type: Array, default: () => [] },
})

const upcoming = props.bookings.filter(b => b.is_upcoming)
const past     = props.bookings.filter(b => !b.is_upcoming)

const statusColour = {
  confirmed:  'text-green-700 bg-green-100',
  pending:    'text-yellow-700 bg-yellow-100',
  cancelled:  'text-red-700 bg-red-100',
  completed:  'text-gray-500 bg-gray-100',
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bookings</h1>
      <p class="text-sm text-gray-500 mt-1">Your appointments and reservations</p>
    </div>

    <!-- Upcoming -->
    <div class="mb-8">
      <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
        Upcoming
      </h2>
      <div v-if="!upcoming.length"
           class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-10 text-center text-sm text-gray-400">
        No upcoming bookings.
      </div>
      <div v-else class="space-y-3">
        <div v-for="b in upcoming" :key="b.id"
             class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 flex items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background-color: var(--color-primary); opacity: 0.1;">
            </div>
            <div>
              <p class="font-semibold text-gray-900 dark:text-white">{{ b.service }}</p>
              <p v-if="b.resource" class="text-xs text-gray-400">{{ b.resource }}</p>
              <p class="text-sm text-gray-500 mt-0.5">{{ b.start_at }}</p>
            </div>
          </div>
          <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize"
                :class="statusColour[b.status] ?? 'text-gray-500 bg-gray-100'">
            {{ b.status }}
          </span>
        </div>
      </div>
    </div>

    <!-- Past -->
    <div v-if="past.length">
      <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wide">
        Past Bookings
      </h2>
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="b in past" :key="b.id"
               class="flex items-center justify-between px-5 py-3 text-sm">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">{{ b.service }}</p>
              <p class="text-xs text-gray-400">{{ b.start_at }}</p>
            </div>
            <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize"
                  :class="statusColour[b.status] ?? 'text-gray-500 bg-gray-100'">
              {{ b.status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
