<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge from '@shared/components/display/Badge.vue'
import Button from '@shared/components/buttons/Button.vue'
import { router } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })
const props = defineProps({ booking: { type: Object, required: true } })

const statusType = {
  confirmed: 'success', pending: 'warning', cancelled: 'danger',
  completed: 'neutral', in_progress: 'info',
}

function confirm()  { router.patch(`/bookings/bookings/${props.booking.id}/confirm`) }
function cancel()   { router.patch(`/bookings/bookings/${props.booking.id}/cancel`) }
function complete() { router.patch(`/bookings/bookings/${props.booking.id}/complete`) }
</script>

<template>
  <div>
    <div class="mb-6">
      <a href="/bookings/bookings" class="text-sm text-primary hover:underline">← Bookings</a>
      <div class="flex items-start justify-between mt-2">
        <div>
          <h1 class="text-2xl font-bold text-app-text">{{ booking.reference }}</h1>
          <div class="flex items-center gap-3 mt-2">
            <Badge :type="statusType[booking.status]" dot>{{ booking.status }}</Badge>
            <span class="text-sm text-app-text/50">{{ booking.start_at }}</span>
          </div>
        </div>
        <div class="flex gap-2">
          <Button v-if="booking.status === 'pending'" @click="confirm" variant="primary" size="sm">
            Confirm
          </Button>
          <Button v-if="booking.status === 'confirmed'" @click="complete" variant="secondary" size="sm">
            Complete
          </Button>
          <Button v-if="['pending','confirmed'].includes(booking.status)" @click="cancel" variant="danger" size="sm">
            Cancel
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Customer</h2>
          <p class="font-semibold text-app-text">{{ booking.customer_name }}</p>
          <p class="text-sm text-app-text/60">{{ booking.customer_email }}</p>
          <p v-if="booking.customer_phone" class="text-sm text-app-text/60">{{ booking.customer_phone }}</p>
        </div>

        <div v-if="booking.notes" class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-2">Notes</h2>
          <p class="text-sm text-app-text/70">{{ booking.notes }}</p>
        </div>
      </div>

      <div class="space-y-6">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Booking Details</h2>
          <dl class="space-y-3">
            <div><dt class="text-xs text-app-text/50">Service</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ booking.service }}</dd></div>
            <div v-if="booking.resource"><dt class="text-xs text-app-text/50">Resource</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ booking.resource }}</dd></div>
            <div><dt class="text-xs text-app-text/50">Start</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ booking.start_at }}</dd></div>
            <div><dt class="text-xs text-app-text/50">End</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ booking.end_at }}</dd></div>
            <div><dt class="text-xs text-app-text/50">Duration</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ booking.duration }} min</dd></div>
            <div v-if="booking.deposit_amount > 0">
              <dt class="text-xs text-app-text/50">Deposit</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">
                R {{ booking.deposit_amount }}
                <Badge :type="booking.deposit_paid ? 'success' : 'warning'" class="ml-2">
                  {{ booking.deposit_paid ? 'Paid' : 'Outstanding' }}
                </Badge>
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>
