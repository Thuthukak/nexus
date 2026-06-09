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
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="flex items-stretch gap-0">

          <!-- Banner thumbnail -->
          <div class="w-36 flex-shrink-0 bg-gradient-to-br from-primary/30 to-primary/10 relative overflow-hidden">
            <img v-if="event.banner_url" :src="event.banner_url"
                 class="w-full h-full object-cover absolute inset-0" />
            <div v-else class="w-full h-full flex items-center justify-center p-3">
              <svg class="w-8 h-8 text-primary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>

          <div class="flex-1 p-5 flex items-start justify-between gap-4 min-w-0">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-1 flex-wrap">
                <h2 class="font-bold text-app-text text-base">{{ event.title }}</h2>
                <Badge :type="statusType[event.status]">{{ event.status }}</Badge>
                <span v-if="event.is_featured"
                      class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-medium">
                  ⭐ Featured
                </span>
                <span v-if="event.is_sold_out"
                      class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">
                  Sold Out
                </span>
              </div>

              <div class="flex items-center gap-4 text-xs text-app-text/50 mb-3 flex-wrap">
                <span>📅 {{ event.starts_at }}</span>
                <span v-if="event.venue">📍 {{ event.venue }}</span>
              </div>

              <!-- Stats -->
              <div class="flex items-center gap-6 text-sm flex-wrap">
                <div>
                  <p class="text-xs text-app-text/40">Tickets Sold</p>
                  <p class="font-bold text-app-text">
                    {{ event.total_sold }}<span class="text-app-text/40 font-normal">/{{ event.total_capacity }}</span>
                  </p>
                </div>
                <div>
                  <p class="text-xs text-app-text/40">Revenue</p>
                  <p class="font-bold text-green-600">{{ currency(event.total_revenue) }}</p>
                </div>
                <div>
                  <p class="text-xs text-app-text/40">Orders</p>
                  <p class="font-bold text-app-text">{{ event.orders_count }}</p>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-1.5 flex-shrink-0">
              <a :href="`/events-admin/events/${event.id}/edit`"
                 class="px-3 py-1.5 text-xs font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors text-center">
                Edit
              </a>
              <a :href="`/events-admin/events/${event.id}/orders`"
                 class="px-3 py-1.5 text-xs font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors text-center">
                Orders
              </a>
              <a :href="`/events/${event.slug}`" target="_blank"
                 class="px-3 py-1.5 text-xs font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors text-center">
                Public Page ↗
              </a>
              <button @click="promptDelete(event.id)"
                      class="px-3 py-1.5 text-xs font-medium border border-red-200 text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                Delete
              </button>
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
