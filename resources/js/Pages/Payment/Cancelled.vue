<script setup>
defineProps({
  invoice: { type: Object, required: true },
  app:     { type: Object, required: true },
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4" style="font-family: Arial, sans-serif;">
    <div class="max-w-md w-full text-center">
      <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Cancelled</h1>
      <p class="text-gray-500 mb-6">
        Your payment was cancelled. Invoice
        <strong>{{ invoice.reference }}</strong> remains outstanding.
      </p>
      <a :href="`/pay/${invoice.token}`"
         class="inline-block px-6 py-3 bg-gray-900 text-white rounded-xl font-semibold text-sm hover:bg-gray-800 transition-colors">
        Try Again
      </a>
      <p class="text-xs text-gray-400 mt-6">{{ app.name }}</p>
    </div>
  </div>
</template>
