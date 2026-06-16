<script setup>
import { router }    from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import Badge         from '@shared/components/display/Badge.vue'
import ConfirmDialog from '@shared/components/feedback/ConfirmDialog.vue'
import { ref }       from 'vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  events: { type: Array, default: () => [] },
})

const confirmDelete = ref(false)
const deletingId    = ref(null)

function promptDelete(id) {
  deletingId.value    = id
  confirmDelete.value = true
}

function handleDelete() {
  router.delete(`/events-admin/events/${deletingId.value}`, {}, {
    onFinish: () => { confirmDelete.value = false; deletingId.value = null },
  })
}

const statusType = {
  draft:     'neutral',
  published: 'success',
  cancelled: 'danger',
  completed: 'info',
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Events</h1>
        <p class="text-sm text-app-text/60 mt-1">{{ events.length }} event(s)</p>
      </div>
      <a href="/events-admin/events/create"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Event
      </a>
    </div>

    <div v-if="!events.length"
          class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-6 py-16 text-center text-app-text/40 text-sm">
      No events yet. Create your first event to start selling tickets.
    </div>

    <div v-else class="space-y-4">
      <div v-for="event in events" :key="event.id"
            class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden hover:shadow-md transition-shadow">
        <div class="flex items-stretch">

          <!-- Banner thumbnail -->
          <div class="w-44 flex-shrink-0 relative overflow-hidden">
            <img v-if="event.banner_url" :src="event.banner_url"
                  class="w-full h-full object-cover absolute inset-0" />
            <div v-else class="w-full h-full flex items-center justify-center"
                  style="background: linear-gradient(135deg, var(--color-primary, #1E3A5F) 0%, #2E86AB 100%);">
              <svg class="w-10 h-10 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>

            <!-- Sold out overlay -->
            <div v-if="event.is_sold_out"
                  class="absolute inset-0 bg-black/50 flex items-center justify-center">
              <span class="text-white text-xs font-bold tracking-widest uppercase rotate-[-20deg]
                          border-2 border-white/70 px-2 py-0.5 rounded">
                Sold Out
              </span>
            </div>
          </div>

          <div class="flex-1 p-5 flex flex-col justify-between gap-4 min-w-0">

            <!-- Top row: title + badges + actions -->
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-2">
                  <h2 class="font-bold text-app-text text-base leading-tight">{{ event.title }}</h2>
                  <Badge :type="statusType[event.status]">{{ event.status }}</Badge>
                  <span v-if="event.is_featured"
                        class="inline-flex items-center gap-1 text-xs bg-yellow-100 text-yellow-700
                              dark:bg-yellow-900/30 dark:text-yellow-400 px-2 py-0.5 rounded-full font-medium">
                    <!-- Star -->
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Featured
                  </span>
                </div>

                <div class="flex items-center gap-4 text-xs text-app-text/50 flex-wrap">
                  <span class="flex items-center gap-1.5">
                    <!-- Calendar -->
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ event.starts_at }}
                  </span>
                  <span v-if="event.venue" class="flex items-center gap-1.5">
                    <!-- Location pin -->
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ event.venue }}
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-1.5 flex-shrink-0">
                <a :href="`/events-admin/events/${event.id}/edit`"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                          border border-gray-200 dark:border-gray-700 rounded-lg
                          text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  Edit
                </a>
                <a :href="`/events-admin/events/${event.id}/orders`"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                          border border-gray-200 dark:border-gray-700 rounded-lg
                          text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                  Orders
                </a>
                <a :href="`/events/${event.slug}`" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                          border border-gray-200 dark:border-gray-700 rounded-lg
                          text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                  </svg>
                  Public
                </a>
                <button @click="promptDelete(event.id)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                              border border-red-200 dark:border-red-900 text-red-500
                              rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  Delete
                </button>
              </div>
            </div>

            <!-- Bottom row: stats -->
            <div class="flex items-center gap-6 pt-4 border-t border-gray-100 dark:border-gray-800">

              <!-- Tickets sold progress -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between text-xs mb-1.5">
                  <span class="text-app-text/40">Tickets Sold</span>
                  <span class="font-semibold text-app-text">
                    {{ event.total_sold }}<span class="text-app-text/40 font-normal">/{{ event.total_capacity }}</span>
                  </span>
                </div>
                <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                  <div class="h-full rounded-full transition-all"
                      :style="{
                        width: event.total_capacity
                           ? `${Math.min(100, (event.total_sold / event.total_capacity) * 100)}%`
                          : '0%',
                        backgroundColor: 'var(--color-primary, #1E3A5F)',
                      }" />
                </div>
              </div>

              <div class="flex items-center gap-6 flex-shrink-0">
                <div class="text-center">
                  <p class="text-xs text-app-text/40 mb-0.5">Revenue</p>
                  <p class="font-bold text-green-600 text-sm">{{ currency(event.total_revenue) }}</p>
                </div>
                <div class="text-center">
                  <p class="text-xs text-app-text/40 mb-0.5">Orders</p>
                  <p class="font-bold text-app-text text-sm">{{ event.orders_count }}</p>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <ConfirmDialog :show="confirmDelete" title="Delete Event"
      message="This event and all its ticket types will be permanently deleted."
      confirm-label="Delete" danger
      @confirm="handleDelete" @cancel="confirmDelete = false" />
  </div>
</template>