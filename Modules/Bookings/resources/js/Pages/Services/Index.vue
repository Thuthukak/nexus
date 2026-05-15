<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })
defineProps({ services: { type: Array, default: () => [] } })
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Services</h1>
        <p class="text-sm text-app-text/60 mt-1">Bookable services and their durations</p>
      </div>
      <a href="/bookings/services/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Service
      </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-if="!services.length" class="col-span-full text-center py-12 text-app-text/40">
        No services yet. Add your first service to accept bookings.
      </div>
      <div v-for="service in services" :key="service.id"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h3 class="font-semibold text-app-text mb-3">{{ service.name }}</h3>
        <div class="space-y-1 text-sm text-app-text/60">
          <p>Duration: {{ service.duration_minutes }} min</p>
          <p v-if="service.buffer_minutes">Buffer: {{ service.buffer_minutes }} min</p>
          <p>Price: R {{ service.price }}</p>
          <p>Max Participants: {{ service.max_participants }}</p>
          <p>Bookings: {{ service.bookings_count }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
