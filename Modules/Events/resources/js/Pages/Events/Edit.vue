<script setup>
import { ref }             from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout           from '@shared/layouts/AppLayout.vue'
import Input               from '@shared/components/form/Input.vue'
import Button              from '@shared/components/buttons/Button.vue'
import Modal               from '@shared/components/feedback/Modal.vue'
import Badge               from '@shared/components/display/Badge.vue'
import ConfirmDialog       from '@shared/components/feedback/ConfirmDialog.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  event: { type: Object, required: true },
})

const activeTab     = ref('details')
const bannerInput   = ref(null)
const bannerPreview = ref(props.event.banner_url ?? null)

const form = useForm({
  title:           props.event.title,
  description:     props.event.description ?? '',
  venue:           props.event.venue ?? '',
  venue_address:   props.event.venue_address ?? '',
  starts_at:       props.event.starts_at ?? '',
  ends_at:         props.event.ends_at ?? '',
  status:          props.event.status,
  is_featured:     props.event.is_featured,
  organiser_name:  props.event.organiser_name ?? '',
  organiser_email: props.event.organiser_email ?? '',
  banner:          null,
})

function onBannerChange(e) {
  const file = e.target.files[0]
  if (!file) return
  form.banner      = file
  bannerPreview.value = URL.createObjectURL(file)
}

function saveEvent() {
  form.post(`/events-admin/events/${props.event.id}?_method=PATCH`, {
    forceFormData: true,
  })
}

// ── Ticket Types ──────────────────────────────────────────────
const showTicketModal  = ref(false)
const editingTicketType= ref(null)

const ticketForm = useForm({
  name:           '',
  description:    '',
  price:          0,
  quantity_total: 100,
  max_per_order:  10,
  sale_starts_at: '',
  sale_ends_at:   '',
  is_active:      true,
})

function openAddTicketType() {
  editingTicketType.value = null
  ticketForm.reset()
  ticketForm.quantity_total = 100
  ticketForm.max_per_order  = 10
  ticketForm.is_active      = true
  showTicketModal.value     = true
}

function openEditTicketType(tt) {
  editingTicketType.value    = tt
  ticketForm.name            = tt.name
  ticketForm.description     = tt.description ?? ''
  ticketForm.price           = tt.price
  ticketForm.quantity_total  = tt.quantity_total
  ticketForm.max_per_order   = tt.max_per_order
  ticketForm.sale_starts_at  = tt.sale_starts_at ?? ''
  ticketForm.sale_ends_at    = tt.sale_ends_at ?? ''
  ticketForm.is_active       = tt.is_active
  showTicketModal.value      = true
}

function saveTicketType() {
  const url = editingTicketType.value
    ? `/events-admin/events/${props.event.id}/ticket-types/${editingTicketType.value.id}`
    : `/events-admin/events/${props.event.id}/ticket-types`

  const opts = {
    onSuccess: () => {
      showTicketModal.value   = false
      editingTicketType.value = null
    },
  }

  if (editingTicketType.value) {
    ticketForm.patch(url, opts)
  } else {
    ticketForm.post(url, opts)
  }
}

const confirmDeleteTT = ref(false)
const deletingTTId    = ref(null)

function promptDeleteTT(id) {
  deletingTTId.value    = id
  confirmDeleteTT.value = true
}

function handleDeleteTT() {
  router.delete(
    `/events-admin/events/${props.event.id}/ticket-types/${deletingTTId.value}`,
    { onFinish: () => { confirmDeleteTT.value = false; deletingTTId.value = null } }
  )
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}
</script>

<template>
  <div class="max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <a href="/events-admin/events" class="text-sm text-primary hover:underline">← Events</a>
        <h1 class="text-2xl font-bold text-app-text mt-2">{{ event.title }}</h1>
      </div>
      <div class="flex items-center gap-2">
        <a :href="`/events-admin/events/${event.id}/orders`"
           class="px-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-app-text transition-colors">
          View Orders
        </a>
        <a :href="`/events/${event.slug}`" target="_blank"
           class="px-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-app-text transition-colors">
          Public Page ↗
        </a>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 bg-gray-100 dark:bg-gray-800 rounded-xl p-1 w-fit">
      <button v-for="tab in [
        { key: 'details', label: 'Details' },
        { key: 'tickets', label: `Ticket Types (${event.ticket_types?.length ?? 0})` },
      ]" :key="tab.key"
              @click="activeTab = tab.key"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="activeTab === tab.key
                ? 'bg-surface text-app-text shadow-sm'
                : 'text-app-text/50 hover:text-app-text'">
        {{ tab.label }}
      </button>
    </div>

    <!-- ── DETAILS TAB ──────────────────────────────────────── -->
    <div v-if="activeTab === 'details'" class="space-y-6">

      <!-- Banner -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="relative h-44 bg-gradient-to-br from-primary/20 to-primary/5 cursor-pointer group"
             @click="bannerInput.click()">
          <img v-if="bannerPreview" :src="bannerPreview"
               class="absolute inset-0 w-full h-full object-cover" />
          <div class="absolute inset-0 flex items-center justify-center gap-2 text-sm font-medium"
               :class="bannerPreview
                 ? 'bg-black/40 text-white opacity-0 group-hover:opacity-100 transition-opacity'
                 : 'text-primary/50'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14" />
            </svg>
            {{ bannerPreview ? 'Change banner' : 'Upload banner image' }}
          </div>
        </div>
        <input ref="bannerInput" type="file" accept="image/*" class="hidden"
               @change="onBannerChange" />
      </div>

      <form @submit.prevent="saveEvent" class="space-y-4">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Event Details</h2>
          <Input v-model="form.title" label="Title" required :error="form.errors.title" />
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Description</label>
            <textarea v-model="form.description" rows="4"
                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <Input v-model="form.starts_at" label="Start" type="datetime-local" required />
            <Input v-model="form.ends_at"   label="End"   type="datetime-local" />
          </div>
          <Input v-model="form.venue"         label="Venue Name" />
          <Input v-model="form.venue_address" label="Venue Address" />
        </div>

        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Settings</h2>
          <div class="flex items-end gap-6">
            <div class="flex flex-col gap-1">
              <label class="text-sm font-medium text-app-text">Status</label>
              <select v-model="form.status"
                      class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="cancelled">Cancelled</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="form.is_featured" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              <span class="text-sm font-medium text-app-text">Featured event</span>
            </label>
          </div>
        </div>

        <div class="flex justify-end">
          <Button type="submit" :loading="form.processing">Save Changes</Button>
        </div>
      </form>
    </div>

    <!-- ── TICKET TYPES TAB ─────────────────────────────────── -->
    <div v-if="activeTab === 'tickets'">
      <div class="flex items-center justify-between mb-4">
        <div>
          <p class="text-sm text-app-text/60">{{ event.ticket_types?.length ?? 0 }} type(s)</p>
          <p class="text-xs text-app-text/40 mt-0.5">
            {{ event.total_sold }}/{{ event.total_capacity }} tickets sold ·
            R{{ Number(event.total_revenue ?? 0).toLocaleString() }} revenue
          </p>
        </div>
        <Button size="sm" @click="openAddTicketType">+ Add Ticket Type</Button>
      </div>

      <div v-if="!event.ticket_types?.length"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-6 py-10 text-center text-app-text/40 text-sm">
        No ticket types yet. Add at least one ticket type to allow purchases.
      </div>

      <div v-else class="space-y-3">
        <div v-for="tt in event.ticket_types" :key="tt.id"
             class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5 flex items-center justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1">
              <h3 class="font-semibold text-app-text">{{ tt.name }}</h3>
              <span class="text-xs font-bold text-primary">{{ currency(tt.price) }}</span>
              <span v-if="!tt.is_active"
                    class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">
                Inactive
              </span>
            </div>
            <p v-if="tt.description" class="text-xs text-app-text/50 mb-2">{{ tt.description }}</p>
            <div class="flex items-center gap-4 text-xs text-app-text/50">
              <span>Sold: <strong class="text-app-text">{{ tt.quantity_sold }}</strong>/{{ tt.quantity_total }}</span>
              <span>Remaining: <strong :class="tt.quantity_remaining < 10 ? 'text-orange-500' : 'text-app-text'">{{ tt.quantity_remaining }}</strong></span>
              <span>Max per order: {{ tt.max_per_order }}</span>
            </div>

            <!-- Progress bar -->
            <div class="mt-2 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden w-48">
              <div class="h-full bg-primary rounded-full"
                   :style="{ width: (tt.quantity_sold / tt.quantity_total * 100) + '%' }" />
            </div>
          </div>

          <div class="flex items-center gap-2 flex-shrink-0">
            <button @click="openEditTicketType(tt)"
                    class="px-3 py-1.5 text-xs font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary transition-colors">
              Edit
            </button>
            <button v-if="tt.quantity_sold === 0"
                    @click="promptDeleteTT(tt.id)"
                    class="px-3 py-1.5 text-xs font-medium border border-red-200 text-red-500 rounded-lg hover:bg-red-50 transition-colors">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Ticket Type Modal -->
  <Modal :show="showTicketModal"
         :title="editingTicketType ? 'Edit Ticket Type' : 'Add Ticket Type'"
         size="md"
         @close="showTicketModal = false">
    <div class="space-y-4">
      <Input v-model="ticketForm.name" label="Ticket Type Name"
             placeholder="e.g. General Admission, VIP, Early Bird"
             required :error="ticketForm.errors.name" />

      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Description</label>
        <textarea v-model="ticketForm.description" rows="2"
                  placeholder="What's included with this ticket…"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
      </div>

      <div class="grid grid-cols-3 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Price (R)</label>
          <input v-model.number="ticketForm.price" type="number" min="0" step="0.01"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Total Qty</label>
          <input v-model.number="ticketForm.quantity_total" type="number" min="1"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Max / Order</label>
          <input v-model.number="ticketForm.max_per_order" type="number" min="1" max="100"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Sale Opens</label>
          <input v-model="ticketForm.sale_starts_at" type="datetime-local"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Sale Closes</label>
          <input v-model="ticketForm.sale_ends_at" type="datetime-local"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
      </div>

      <label class="flex items-center gap-2 cursor-pointer">
        <input v-model="ticketForm.is_active" type="checkbox"
               class="w-4 h-4 rounded border-gray-300 text-primary" />
        <span class="text-sm font-medium text-app-text">Active (available for purchase)</span>
      </label>
    </div>
    <template #footer>
      <button @click="showTicketModal = false"
              class="px-4 py-2 text-sm text-app-text/60">Cancel</button>
      <Button @click="saveTicketType" :loading="ticketForm.processing">
        {{ editingTicketType ? 'Save Changes' : 'Add Ticket Type' }}
      </Button>
    </template>
  </Modal>

  <ConfirmDialog :show="confirmDeleteTT" title="Delete Ticket Type"
    message="This ticket type will be permanently deleted."
    confirm-label="Delete" danger
    @confirm="handleDeleteTT" @cancel="confirmDeleteTT = false" />
</template>
