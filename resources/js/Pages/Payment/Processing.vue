<script setup>
defineProps({
  invoice: { type: Object, required: true },
  app:     { type: Object, required: true },
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4" style="font-family: Arial, sans-serif;">
    <div class="max-w-md w-full text-center">
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Received</h1>
      <p class="text-gray-500 mb-6">
        Thank you! Your payment for invoice
        <strong>{{ invoice.reference }}</strong> is being processed.
        A receipt will be emailed to you shortly.
      </p>
      <div class="bg-white rounded-xl border border-gray-200 p-4 text-sm text-left">
        <div class="flex justify-between py-1">
          <span class="text-gray-500">Invoice</span>
          <span class="font-medium">{{ invoice.reference }}</span>
        </div>
        <div class="flex justify-between py-1">
          <span class="text-gray-500">To</span>
          <span class="font-medium">{{ invoice.customer_name }}</span>
        </div>
      </div>
      <p class="text-xs text-gray-400 mt-6">{{ app.name }}</p>
    </div>
  </div>
</template>
