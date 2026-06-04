<script setup>
defineProps({ app: { type: Object, required: true } })
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4" style="font-family: Arial, sans-serif;">
    <div class="max-w-sm w-full text-center">
      <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
      </div>
      <h1 class="text-xl font-bold text-gray-900 mb-2">Invite Link Expired</h1>
      <p class="text-sm text-gray-500 mb-6">
        This invite link is no longer valid. Please contact us to receive a new invitation.
      </p>
      <a href="/portal/login"
         class="inline-block px-6 py-2.5 rounded-xl text-sm font-semibold text-white"
         style="background-color: var(--color-primary, #1E3A5F);">
        Back to Login
      </a>
    </div>
  </div>
</template>
