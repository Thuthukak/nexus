<script setup>
import { computed } from 'vue'

defineProps({
  events:   { type: Array,  default: () => [] },
  featured: { type: Object, default: null },
  app:      { type: Object, required: true },
})

function currency(val) {
  if (val === null || val === undefined) return 'Free'
  if (val == 0) return 'Free'
  return 'From R ' + Number(val).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50" style="font-family: Arial, sans-serif;">

    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <img v-if="app.logo_url" :src="app.logo_url" class="h-16 w-auto object-contain" />
          <span v-else class="font-bold text-gray-900 text-lg">{{ app.name }}</span>
        </div>
        <span class="text-sm text-gray-400">Events</span>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-10">

      <div v-if="featured"
            class="relative rounded-3xl overflow-hidden mb-10 cursor-pointer"
            @click="window.location.href = `/events/${featured.slug}`">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent z-10" />
        <div v-if="featured.banner_url" class="h-80 sm:h-96">
          <img :src="featured.banner_url" class="w-full h-full object-cover" />
        </div>
        <div v-else class="h-80 sm:h-96"
              style="background: linear-gradient(135deg, var(--color-primary, #1E3A5F) 0%, #2E86AB 100%);" />

        <div class="absolute bottom-0 left-0 right-0 p-8 z-20">
          <span class="inline-flex items-center gap-1 text-xs font-bold bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full mb-3">
            <!-- Star -->
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            Featured Event
          </span>
          <h2 class="text-3xl font-bold text-white mb-2">{{ featured.title }}</h2>
          <div class="flex items-center gap-4 text-white/80 text-sm mb-4">
            <span class="flex items-center gap-1.5">
              <!-- Calendar -->
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              {{ featured.starts_at }}
            </span>
            <span v-if="featured.venue" class="flex items-center gap-1.5">
              <!-- Location pin -->
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              {{ featured.venue }}
            </span>
          </div>
          <div class="flex items-center gap-3">
            <a :href="`/events/${featured.slug}`"
                class="px-6 py-3 rounded-xl font-bold text-sm text-white"
                style="background-color: var(--color-primary, #1E3A5F);">
              Get Tickets — {{ currency(featured.min_price) }}
            </a>
            <span v-if="featured.is_sold_out"
                  class="px-4 py-3 rounded-xl font-bold text-sm bg-red-500 text-white">
              Sold Out
            </span>
          </div>
        </div>
      </div>

      <div>
        <h2 class="text-xl font-bold text-gray-900 mb-6">
          {{ events.length ? 'Upcoming Events' : '' }}
        </h2>

        <div v-if="!events.length"
              class="bg-white rounded-2xl border border-gray-200 px-6 py-16 text-center text-gray-400">
          <p class="text-lg font-medium">No upcoming events</p>
          <p class="text-sm mt-1">Check back soon!</p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <a v-for="event in events" :key="event.id"
              :href="`/events/${event.slug}`"
              class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition-all block">

            <div class="h-44 relative overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200">
              <img v-if="event.banner_url" :src="event.banner_url" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center"
                    style="background: linear-gradient(135deg, var(--color-primary, #1E3A5F) 0%, #2E86AB 100%);">
                <svg class="w-10 h-10 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <span v-if="event.is_sold_out"
                    class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                Sold Out
              </span>
            </div>

            <div class="p-4">
              <h3 class="font-bold text-gray-900 mb-1 line-clamp-2">{{ event.title }}</h3>
              <p class="flex items-center gap-1.5 text-xs text-gray-500 mb-1">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ event.starts_at }}
              </p>
              <p v-if="event.venue" class="flex items-center gap-1.5 text-xs text-gray-400 mb-3">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ event.venue }}
              </p>

              <div class="flex items-center justify-between mt-auto pt-3 border-t border-gray-100">
                <span class="text-sm font-bold text-gray-900">{{ currency(event.min_price) }}</span>
                <span class="text-xs font-semibold px-3 py-1.5 rounded-lg text-white"
                      style="background-color: var(--color-primary, #1E3A5F);">
                  View →
                </span>
              </div>
            </div>
          </a>
        </div>
      </div>
    </main>

    <footer class="text-center text-xs text-gray-400 py-8 mt-5">
      Powered by {{ app.name }}
    </footer>
  </div>
</template>