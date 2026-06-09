<script setup>
import { ref, computed, reactive } from 'vue'
import { router }                   from '@inertiajs/vue3'

const props = defineProps({
  event: { type: Object, required: true },
  app:   { type: Object, required: true },
})

// Ticket selection
const quantities  = reactive({})
const formData    = ref({ name: '', email: '', phone: '' })
const submitting  = ref(false)
const errors      = ref({})
const showForm    = ref(false)

// Init all quantities to 0
props.event.ticket_types?.forEach(tt => {
  quantities[tt.id] = 0
})

const totalTickets = computed(() =>
  Object.values(quantities).reduce((s, q) => s + q, 0)
)

const totalPrice = computed(() => {
  return props.event.ticket_types?.reduce((s, tt) => {
    return s + (tt.price * (quantities[tt.id] ?? 0))
  }, 0) ?? 0
})

function increment(id, max) {
  if ((quantities[id] ?? 0) < max) quantities[id] = (quantities[id] ?? 0) + 1
  if (totalTickets.value > 0) showForm.value = true
}

function decrement(id) {
  if ((quantities[id] ?? 0) > 0) quantities[id]--
  if (totalTickets.value === 0) showForm.value = false
}

async function checkout() {
  errors.value  = {}
  submitting.value = true

  const items = Object.entries(quantities)
    .filter(([, qty]) => qty > 0)
    .map(([id, qty]) => ({ ticket_type_id: id, quantity: qty }))

  try {
    router.post(`/events/${props.event.slug}/checkout`, {
      customer_name:  formData.value.name,
      customer_email: formData.value.email,
      customer_phone: formData.value.phone,
      items,
    }, {
      onError: (e) => { errors.value = e },
      onFinish: () => { submitting.value = false },
    })
  } catch {}
}

function currency(val) {
  if (val == 0) return 'Free'
  return 'R ' + Number(val).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50" style="font-family: Arial, sans-serif;">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <a href="/events" class="flex items-center gap-2">
            <img v-if="app.logo_url" :src="app.logo_url" class="h-8 w-auto object-contain" />
            <span v-else class="font-bold text-gray-900 text-lg">{{ app.name }}</span>
          </a>
        </div>
        <a href="/events" class="text-sm text-gray-500 hover:text-gray-700">← All Events</a>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left — event info -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Banner -->
          <div v-if="event.banner_url"
               class="rounded-2xl overflow-hidden h-64 sm:h-80">
            <img :src="event.banner_url" class="w-full h-full object-cover" />
          </div>
          <div v-else
               class="rounded-2xl overflow-hidden h-48 flex items-center justify-center"
               style="background: linear-gradient(135deg, var(--color-primary, #1E3A5F) 0%, #2E86AB 100%);">
            <h1 class="text-2xl font-bold text-white text-center px-6">{{ event.title }}</h1>
          </div>

          <h1 class="text-3xl font-bold text-gray-900">{{ event.title }}</h1>

          <div class="flex flex-col gap-2 text-sm text-gray-600">
            <div class="flex items-center gap-2">
              <span class="text-lg">📅</span>
              <span>
                {{ event.starts_at }}
                <span v-if="event.starts_time"> at {{ event.starts_time }}</span>
                <span v-if="event.ends_at"> – {{ event.ends_at }}</span>
              </span>
            </div>
            <div v-if="event.venue" class="flex items-center gap-2">
              <span class="text-lg">📍</span>
              <span>{{ event.venue }}<span v-if="event.venue_address"> &mdash; {{ event.venue_address }}</span></span>
            </div>
            <div v-if="event.organiser" class="flex items-center gap-2">
              <span class="text-lg">👤</span>
              <span>Organised by {{ event.organiser }}</span>
            </div>
          </div>

          <div v-if="event.description"
               class="prose prose-sm max-w-none text-gray-700 bg-white rounded-2xl border border-gray-200 p-6"
               v-html="event.description" />
        </div>

        <!-- Right — ticket selection + checkout -->
        <div class="lg:col-span-1 space-y-4">
          <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden sticky top-20">

            <div class="px-5 py-4 border-b border-gray-100">
              <h2 class="font-bold text-gray-900">Get Tickets</h2>
            </div>

            <!-- Sold out state -->
            <div v-if="event.is_sold_out" class="px-5 py-8 text-center">
              <p class="text-2xl mb-2">😔</p>
              <p class="font-semibold text-gray-900">Sold Out</p>
              <p class="text-sm text-gray-500 mt-1">All tickets have been sold.</p>
            </div>

            <!-- Ticket types -->
            <div v-else class="divide-y divide-gray-100">
              <div v-for="tt in event.ticket_types" :key="tt.id"
                   class="px-5 py-4"
                   :class="!tt.is_available ? 'opacity-50' : ''">
                <div class="flex items-start justify-between gap-3 mb-2">
                  <div>
                    <p class="font-semibold text-gray-900 text-sm">{{ tt.name }}</p>
                    <p v-if="tt.description" class="text-xs text-gray-500 mt-0.5">{{ tt.description }}</p>
                    <p v-if="tt.sale_ends_at" class="text-xs text-orange-500 mt-0.5">
                      Sale ends {{ tt.sale_ends_at }}
                    </p>
                  </div>
                  <div class="text-right flex-shrink-0">
                    <span class="font-bold text-gray-900">{{ currency(tt.price) }}</span>
                    <p class="text-xs text-gray-400">{{ tt.quantity_remaining }} left</p>
                  </div>
                </div>

                <!-- Quantity control -->
                <div v-if="tt.is_available" class="flex items-center gap-3 justify-end">
                  <button @click="decrement(tt.id)"
                          :disabled="(quantities[tt.id] ?? 0) === 0"
                          class="w-8 h-8 rounded-full border-2 border-gray-300 flex items-center justify-center font-bold text-gray-600 disabled:opacity-30 hover:border-gray-400 transition-colors text-lg leading-none">
                    −
                  </button>
                  <span class="w-6 text-center font-bold text-gray-900">
                    {{ quantities[tt.id] ?? 0 }}
                  </span>
                  <button @click="increment(tt.id, Math.min(tt.quantity_remaining, tt.max_per_order))"
                          :disabled="(quantities[tt.id] ?? 0) >= Math.min(tt.quantity_remaining, tt.max_per_order)"
                          class="w-8 h-8 rounded-full border-2 flex items-center justify-center font-bold disabled:opacity-30 transition-colors text-lg leading-none"
                          style="border-color: var(--color-primary, #1E3A5F); color: var(--color-primary, #1E3A5F);">
                    +
                  </button>
                </div>
                <p v-else class="text-xs text-red-500 text-right mt-1">Not available</p>
              </div>

              <!-- Customer form -->
              <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 max-h-0"
                enter-to-class="opacity-100 max-h-96"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 max-h-96"
                leave-to-class="opacity-0 max-h-0">
                <div v-if="showForm && totalTickets > 0"
                     class="px-5 py-4 space-y-3 bg-gray-50 border-t border-gray-100 overflow-hidden">
                  <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Your Details</p>

                  <div class="flex flex-col gap-1">
                    <input v-model="formData.name" type="text"
                           placeholder="Full Name *"
                           class="w-full px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           :class="errors.customer_name ? 'border-red-300' : 'border-gray-300'"
                           style="--tw-ring-color: var(--color-primary, #1E3A5F);" />
                    <p v-if="errors.customer_name" class="text-xs text-red-500">{{ errors.customer_name }}</p>
                  </div>

                  <div class="flex flex-col gap-1">
                    <input v-model="formData.email" type="email"
                           placeholder="Email Address *"
                           class="w-full px-3 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                           :class="errors.customer_email ? 'border-red-300' : 'border-gray-300'" />
                    <p v-if="errors.customer_email" class="text-xs text-red-500">{{ errors.customer_email }}</p>
                  </div>

                  <input v-model="formData.phone" type="tel"
                         placeholder="Phone Number (optional)"
                         class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:border-transparent" />
                </div>
              </Transition>

              <!-- Order summary + CTA -->
              <div v-if="totalTickets > 0" class="px-5 py-4 bg-white">
                <div class="flex items-center justify-between mb-3 text-sm">
                  <span class="text-gray-600">{{ totalTickets }} ticket(s)</span>
                  <span class="font-bold text-gray-900">{{ currency(totalPrice) }}</span>
                </div>
                <button @click="checkout"
                        :disabled="submitting || !formData.name || !formData.email"
                        class="w-full py-3.5 rounded-xl font-bold text-sm text-white disabled:opacity-50 transition-opacity"
                        style="background-color: var(--color-primary, #1E3A5F);">
                  {{ submitting ? 'Processing…' : 'Proceed to Payment' }}
                </button>
                <p class="text-xs text-gray-400 text-center mt-2">
                  Secure payment via PayFast
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
