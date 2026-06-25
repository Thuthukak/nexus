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
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-30">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
        <a href="/events" class="flex items-center gap-2">
          <img v-if="app.logo_url" :src="app.logo_url" class="h-16 w-auto object-contain" />
          <span v-else class="font-bold text-gray-900 text-lg">{{ app.name }}</span>
        </a>
        <a href="/events"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
          All Events
        </a>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- ── Left: Event Info ── -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Hero banner -->
          <div class="relative rounded-3xl overflow-hidden shadow-lg">
            <img v-if="event.banner_url"
                  :src="event.banner_url"
                  class="w-full h-64 sm:h-80 object-cover" />
            <div v-else
                  class="h-64 sm:h-80 flex items-center justify-center"
                  style="background: linear-gradient(135deg, var(--color-primary, #1E3A5F) 0%, #2E86AB 100%);">
              <h1 class="text-3xl font-bold text-white text-center px-8 drop-shadow">{{ event.title }}</h1>
            </div>

            <!-- Sold out ribbon -->
            <div v-if="event.is_sold_out"
                  class="absolute inset-0 bg-black/50 flex items-center justify-center">
              <div class="bg-red-500 text-white font-black text-2xl tracking-widest uppercase
                          px-8 py-3 rotate-[-15deg] shadow-2xl rounded">
                Sold Out
              </div>
            </div>

            <!-- Featured badge -->
            <div v-if="event.is_featured"
                  class="absolute top-4 left-4">
              <span class="inline-flex items-center gap-1.5 bg-yellow-400 text-yellow-900
                            text-xs font-bold px-3 py-1.5 rounded-full shadow">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Featured Event
              </span>
            </div>
          </div>

          <!-- Title + meta -->
          <div>
            <h1 class="text-3xl font-black text-gray-900 leading-tight mb-4">{{ event.title }}</h1>

            <!-- Info pills -->
            <div class="flex flex-wrap gap-3">
              <div class="inline-flex items-center gap-2 bg-white border border-gray-200
                          rounded-xl px-4 py-2.5 text-sm text-gray-700 shadow-sm">
                <svg class="w-4 h-4 flex-shrink-0 text-primary" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"
                      style="color: var(--color-primary, #1E3A5F);">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>
                  {{ event.starts_at }}
                  <span v-if="event.starts_time" class="text-gray-500"> at {{ event.starts_time }}</span>
                  <span v-if="event.ends_at" class="text-gray-500"> – {{ event.ends_at }}</span>
                </span>
              </div>

              <div v-if="event.venue"
                    class="inline-flex items-center gap-2 bg-white border border-gray-200
                          rounded-xl px-4 py-2.5 text-sm text-gray-700 shadow-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"
                      style="color: var(--color-primary, #1E3A5F);">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>
                  {{ event.venue }}
                  <span v-if="event.venue_address" class="text-gray-500"> — {{ event.venue_address }}</span>
                </span>
              </div>

              <div v-if="event.organiser"
                    class="inline-flex items-center gap-2 bg-white border border-gray-200
                          rounded-xl px-4 py-2.5 text-sm text-gray-700 shadow-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"
                      style="color: var(--color-primary, #1E3A5F);">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>By {{ event.organiser }}</span>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div v-if="event.description"
                class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-900 mb-3">About this event</h2>
            <div class="prose prose-sm max-w-none text-gray-700" v-html="event.description" />
          </div>
        </div>

        <!-- ── Right: Ticket Widget ── -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden sticky top-20">

            <!-- Widget header -->
            <div class="px-5 py-4 border-b border-gray-200"
                  style="background: linear-gradient(135deg, var(--color-primary, #1E3A5F) 0%, #2E86AB 100%);">
              <h2 class="font-bold text-white text-base">Get Tickets</h2>
              <p v-if="!event.is_sold_out" class="text-white/70 text-xs mt-0.5">
                Select your tickets below
              </p>
            </div>

            <!-- Sold out -->
            <div v-if="event.is_sold_out" class="px-5 py-10 text-center">
              <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
              </div>
              <p class="font-bold text-gray-900">Sold Out</p>
              <p class="text-sm text-gray-500 mt-1">All tickets have been sold.</p>
            </div>

            <!-- Ticket types -->
            <div v-else class="divide-y divide-gray-100">
              <div v-for="tt in event.ticket_types" :key="tt.id"
                    class="px-5 py-4 transition-colors"
                    :class="tt.is_available ? 'hover:bg-gray-50' : 'opacity-50 bg-gray-50'">

                <div class="flex items-start justify-between gap-3 mb-3">
                  <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-900 text-sm">{{ tt.name }}</p>
                    <p v-if="tt.description" class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                      {{ tt.description }}
                    </p>
                    <p v-if="tt.sale_ends_at"
                        class="inline-flex items-center gap-1 text-xs text-orange-500 mt-1 font-medium">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      Sale ends {{ tt.sale_ends_at }}
                    </p>
                  </div>
                  <div class="text-right flex-shrink-0">
                    <p class="font-black text-gray-900 text-base">{{ currency(tt.price) }}</p>
                    <p class="text-xs mt-0.5"
                        :class="tt.quantity_remaining <= 10 ? 'text-orange-500 font-medium' : 'text-gray-400'">
                      {{ tt.quantity_remaining <= 10
                          ? `Only ${tt.quantity_remaining} left!`
                          : `${tt.quantity_remaining} available` }}
                    </p>
                  </div>
                </div>

                <!-- Quantity stepper -->
                <div v-if="tt.is_available"
                      class="flex items-center justify-between">
                  <span class="text-xs text-gray-400">
                    Max {{ tt.max_per_order }} per order
                  </span>
                  <div class="flex items-center gap-3">
                    <button @click="decrement(tt.id)"
                            :disabled="(quantities[tt.id] ?? 0) === 0"
                            class="w-8 h-8 rounded-full border-2 border-gray-200 flex items-center
                                    justify-center text-gray-500 disabled:opacity-30
                                    hover:border-gray-400 hover:text-gray-700 transition-all font-bold text-base">
                      −
                    </button>
                    <span class="w-5 text-center font-black text-gray-900 text-sm tabular-nums">
                      {{ quantities[tt.id] ?? 0 }}
                    </span>
                    <button @click="increment(tt.id, Math.min(tt.quantity_remaining, tt.max_per_order))"
                            :disabled="(quantities[tt.id] ?? 0) >= Math.min(tt.quantity_remaining, tt.max_per_order)"
                            class="w-8 h-8 rounded-full border-2 flex items-center justify-center
                                    font-bold disabled:opacity-30 transition-all text-base"
                            style="border-color: var(--color-primary, #1E3A5F); color: var(--color-primary, #1E3A5F);">
                      +
                    </button>
                  </div>
                </div>
                <p v-else class="text-xs text-red-400 font-medium text-right">Not available</p>
              </div>

              <!-- Customer details form -->
              <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 max-h-0"
                enter-to-class="opacity-100 max-h-[500px]"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 max-h-[500px]"
                leave-to-class="opacity-0 max-h-0">
                <div v-if="showForm && totalTickets > 0"
                      class="px-5 py-4 space-y-3 bg-gray-50 overflow-hidden">
                  <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Your Details</p>

                  <div class="flex flex-col gap-1">
                    <input v-model="formData.name" type="text" placeholder="Full Name *"
                            class="w-full px-3 py-2.5 rounded-xl border text-sm bg-white
                                  focus:outline-none focus:ring-2 focus:border-transparent transition-shadow"
                            :class="errors.customer_name ? 'border-red-300 ring-red-200' : 'border-gray-200'" />
                    <p v-if="errors.customer_name" class="text-xs text-red-500 flex items-center gap-1">
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                          clip-rule="evenodd"/>
                      </svg>
                      {{ errors.customer_name }}
                    </p>
                  </div>

                  <div class="flex flex-col gap-1">
                    <input v-model="formData.email" type="email" placeholder="Email Address *"
                            class="w-full px-3 py-2.5 rounded-xl border text-sm bg-white
                                  focus:outline-none focus:ring-2 focus:border-transparent transition-shadow"
                            :class="errors.customer_email ? 'border-red-300 ring-red-200' : 'border-gray-200'" />
                    <p v-if="errors.customer_email" class="text-xs text-red-500 flex items-center gap-1">
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                          clip-rule="evenodd"/>
                      </svg>
                      {{ errors.customer_email }}
                    </p>
                  </div>

                  <input v-model="formData.phone" type="tel" placeholder="Phone Number (optional)"
                          class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm bg-white
                                focus:outline-none focus:ring-2 focus:border-transparent transition-shadow" />
                </div>
              </Transition>

              <!-- Order summary + CTA -->
              <div v-if="totalTickets > 0" class="px-5 py-4">
                <div class="flex items-center justify-between mb-4">
                  <span class="text-sm text-gray-600">
                    {{ totalTickets }} ticket{{ totalTickets !== 1 ? 's' : '' }}
                  </span>
                  <span class="text-lg font-black text-gray-900">{{ currency(totalPrice) }}</span>
                </div>
                <button @click="checkout"
                        :disabled="submitting || !formData.name || !formData.email"
                        class="w-full py-3.5 rounded-xl font-bold text-sm text-white
                                disabled:opacity-50 transition-all hover:opacity-90 active:scale-[0.98]
                                shadow-md"
                        style="background: linear-gradient(135deg, var(--color-primary, #1E3A5F) 0%, #2E86AB 100%);">
                  <span v-if="submitting" class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Processing…
                  </span>
                  <span v-else>Proceed to Payment →</span>
                </button>

                <div class="flex items-center justify-center gap-1.5 text-xs text-gray-400 mt-3">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                  Secured by PayFast
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>

    <footer class="text-center text-xs text-gray-400 py-8 mt-5">
      Powered by {{ app.name }}
    </footer>
  </div>
</template>