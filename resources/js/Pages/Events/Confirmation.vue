<script setup>
defineProps({
  order: { type: Object, required: true },
  app:   { type: Object, required: true },
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4"
       style="font-family: Arial, sans-serif;">
    <div class="max-w-lg w-full">

      <!-- Header -->
      <div class="text-center mb-8">
        <img v-if="app.logo_url" :src="app.logo_url"
             class="h-10 w-auto object-contain mx-auto mb-4" />
        <div v-if="order.status === 'paid'"
             class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <div v-else
             class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ order.status === 'paid' ? 'Booking Confirmed!' : 'Order Received' }}
        </h1>
        <p class="text-gray-500 mt-1">
          {{ order.status === 'paid'
            ? 'Your tickets have been sent to ' + order.customer_email
            : 'Payment is being processed.' }}
        </p>
      </div>

      <!-- Order card -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs text-gray-400 mb-0.5">Order Reference</p>
              <p class="font-bold text-gray-900 font-mono">{{ order.reference }}</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full"
                  :class="order.status === 'paid'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-yellow-100 text-yellow-700'">
              {{ order.status === 'paid' ? '✓ Paid' : 'Pending' }}
            </span>
          </div>
        </div>

        <div class="px-6 py-4 space-y-3">
          <div>
            <p class="text-xs text-gray-400 mb-1">Event</p>
            <p class="font-bold text-gray-900">{{ order.event.title }}</p>
            <p class="text-sm text-gray-500">{{ order.event.starts_at }}</p>
            <p v-if="order.event.venue" class="text-sm text-gray-500">📍 {{ order.event.venue }}</p>
          </div>

          <div class="border-t border-gray-100 pt-3">
            <div v-for="item in order.items" :key="item.name"
                 class="flex items-center justify-between text-sm py-1">
              <span class="text-gray-600">{{ item.name }} × {{ item.quantity }}</span>
              <span class="font-medium text-gray-900">
                R {{ Number(item.subtotal).toLocaleString('en-ZA', {minimumFractionDigits: 2}) }}
              </span>
            </div>
            <div class="flex items-center justify-between font-bold text-base pt-2 mt-1 border-t border-gray-200">
              <span class="text-gray-900">Total</span>
              <span class="text-gray-900">
                R {{ Number(order.total).toLocaleString('en-ZA', {minimumFractionDigits: 2}) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- What's next -->
      <div v-if="order.status === 'paid'"
           class="bg-blue-50 border border-blue-100 rounded-2xl px-6 py-5 mb-6">
        <p class="font-semibold text-blue-800 mb-2">What happens next?</p>
        <ul class="text-sm text-blue-700 space-y-1.5">
          <li>✉️ A confirmation email with your ticket PDF has been sent to <strong>{{ order.customer_email }}</strong></li>
          <li>🎫 Your PDF contains {{ order.tickets_count }} ticket(s) — one per page</li>
          <li>📱 Present the ticket(s) at the entrance on the day</li>
        </ul>
      </div>

      <div class="flex gap-3">
        <a href="/events"
           class="flex-1 py-3 rounded-xl text-sm font-semibold text-center border-2 border-gray-200 text-gray-600 hover:border-gray-300 transition-colors">
          Browse More Events
        </a>
        <a :href="`/events/${order.event.slug}`"
           class="flex-1 py-3 rounded-xl text-sm font-semibold text-center text-white"
           style="background-color: var(--color-primary, #1E3A5F);">
          Event Details
        </a>
      </div>
    </div>
  </div>
</template>
