<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })
defineProps({ resources: { type: Array, default: () => [] } })
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Resources</h1>
        <p class="text-sm text-app-text/60 mt-1">Rooms, equipment and staff available for booking</p>
      </div>
      <a href="/bookings/resources/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Resource
      </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-if="!resources.length" class="col-span-full text-center py-12 text-app-text/40">
        No resources yet. Add your first resource to get started.
      </div>
      <div v-for="resource in resources" :key="resource.id"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: resource.colour }"></div>
          <h3 class="font-semibold text-app-text">{{ resource.name }}</h3>
        </div>
        <div class="space-y-1 text-sm text-app-text/60">
          <p>Type: <span class="capitalize">{{ resource.type }}</span></p>
          <p>Capacity: {{ resource.capacity }}</p>
          <p v-if="resource.location">Location: {{ resource.location }}</p>
          <p>Bookings: {{ resource.bookings_count }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
