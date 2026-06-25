<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, router }          from '@inertiajs/vue3'

const props = defineProps({
  quotation: { type: Object, required: true },
  action:    { type: String, default: null },
  app:       { type: Object, required: true },
})

const accepted  = ref(new URLSearchParams(window.location.search).has('accepted'))
const declined  = ref(new URLSearchParams(window.location.search).has('declined'))
const accepting = ref(false)
const declining = ref(false)

function accept() {
  accepting.value = true
  router.post(`/quote/${props.quotation.token}/accept`, {}, {
    onFinish: () => accepting.value = false,
  })
}

function decline() {
  declining.value = true
  router.post(`/quote/${props.quotation.token}/decline`, {}, {
    onFinish: () => declining.value = false,
  })
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

const canRespond = computed(() =>
  props.quotation.status === 'sent' && !accepted.value && !declined.value
)

const statusColour = {
  accepted: 'text-green-600 bg-green-50 border-green-200',
  declined: 'text-red-600 bg-red-50 border-red-200',
  expired:  'text-gray-500 bg-gray-50 border-gray-200',
  converted:'text-purple-600 bg-purple-50 border-purple-200',
}
</script>

<template>
  <div class="min-h-screen bg-gray-50" style="font-family: Arial, sans-serif;">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="max-w-2xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <img v-if="app.logo_url" :src="app.logo_url" class="h-8 w-auto object-contain" />
          <span v-else class="text-lg font-bold text-gray-800">{{ app.name }}</span>
        </div>
        <span class="text-sm text-gray-500">Quotation</span>
      </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-8 space-y-6">

      <!-- Success/decline banners -->
      <div v-if="accepted"
           class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-2xl text-green-700 font-semibold">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Thank you! You've accepted this quotation. We'll be in touch shortly.
      </div>

      <div v-if="declined"
           class="flex items-center gap-3 p-4 bg-orange-50 border border-orange-200 rounded-2xl text-orange-700 font-semibold">
        You've declined this quotation. If you'd like to discuss further, please contact us.
      </div>

      <!-- Non-pending status banner -->
      <div v-if="!['sent'].includes(quotation.status) && !accepted && !declined"
           class="flex items-center px-4 gap-1 py-3 rounded-xl border text-sm font-medium"
           :class="statusColour[quotation.status] ?? 'text-gray-600 bg-gray-50 border-gray-200'">
        This quotation is<strong class="capitalize">{{quotation.status}}</strong>.
      </div>

      <!-- Quote card -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="bg-gray-800 px-6 py-5">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-400 text-sm">Quotation from</p>
              <p class="text-white font-bold text-lg">{{ app.name }}</p>
            </div>
            <div class="text-right">
              <p class="text-gray-400 text-sm">Reference</p>
              <p class="text-white font-bold">{{ quotation.reference }}</p>
            </div>
          </div>
        </div>

        <div class="px-6 py-5 border-b border-gray-200">
          <div class="flex justify-between text-sm text-gray-500 mb-3">
            <span>Prepared for</span>
            <span>Valid until {{ quotation.valid_until }}</span>
          </div>
          <p class="font-semibold text-gray-900 text-base">{{ quotation.customer }}</p>
          <p v-if="quotation.contact_name" class="text-gray-500 text-sm">{{ quotation.contact_name }}</p>
        </div>

        <!-- Line items -->
        <table class="w-full text-sm">
          <thead class="bg-primary text-white">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-400">Item</th>
              <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-400">Qty</th>
              <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-400">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="line in quotation.lines" :key="line.description"
                class="border-t border-gray-50">
              <td class="px-6 py-3 text-gray-700">{{ line.description }}</td>
              <td class="px-6 py-3 text-right text-gray-500">{{ line.qty }}</td>
              <td class="px-6 py-3 text-right font-medium text-gray-900">{{ currency(line.line_total) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Totals -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 space-y-2">
          <div class="flex justify-between text-sm text-gray-500">
            <span>Subtotal</span><span>{{ currency(quotation.subtotal) }}</span>
          </div>
          <div class="flex justify-between text-sm text-gray-500">
            <span>Tax</span><span>{{ currency(quotation.tax_total) }}</span>
          </div>
          <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-200">
            <span>Total</span><span>{{ currency(quotation.total) }}</span>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="quotation.notes"
           class="bg-white rounded-2xl border border-gray-200 p-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Notes</p>
        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ quotation.notes }}</p>
      </div>

      <!-- Terms -->
      <div v-if="quotation.terms"
           class="bg-white rounded-2xl border border-gray-200 p-6">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Terms & Conditions</p>
        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ quotation.terms }}</p>
      </div>

      <!-- Accept/Decline CTA -->
      <div v-if="canRespond"
           class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
        <div class="text-center">
          <p class="font-semibold text-gray-900 mb-1">Ready to proceed?</p>
          <p class="text-sm text-gray-500">
            This quotation is valid until <strong>{{ quotation.valid_until }}</strong>.
          </p>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <button @click="accept" :disabled="accepting || declining"
                  class="py-3 px-4 rounded-xl bg-gray-900 text-white font-semibold text-sm hover:bg-gray-800 transition-colors disabled:opacity-50">
            <span v-if="accepting">Processing…</span>
            <span v-else>✓ Accept Quotation</span>
          </button>
          <button @click="decline" :disabled="accepting || declining"
                  class="py-3 px-4 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold text-sm hover:border-gray-300 transition-colors disabled:opacity-50">
            <span v-if="declining">Processing…</span>
            <span v-else>✗ Decline</span>
          </button>
        </div>
      </div>

      <p class="text-center text-xs text-gray-400">
        {{ quotation.reference }} · {{ app.name }}
      </p>
    </div>
  </div>
</template>
