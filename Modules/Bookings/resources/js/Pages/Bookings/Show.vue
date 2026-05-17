<script setup>
import { ref, computed } from 'vue'
import { router }        from '@inertiajs/vue3'
import AppLayout         from '@shared/layouts/AppLayout.vue'
import Badge             from '@shared/components/display/Badge.vue'
import Button            from '@shared/components/buttons/Button.vue'
import ConfirmDialog     from '@shared/components/feedback/ConfirmDialog.vue'

defineOptions({ layout: AppLayout })
const props = defineProps({ booking: { type: Object, required: true } })

const statusType = {
  confirmed: 'success', pending: 'warning', cancelled: 'danger',
  completed: 'neutral', in_progress: 'info', no_show: 'danger',
}

// Computed so buttons update without page reload
const canConfirm  = computed(() => props.booking.status === 'pending')
const canComplete = computed(() => props.booking.status === 'confirmed')
const canCancel   = computed(() => ['pending', 'confirmed'].includes(props.booking.status))

const confirmingCancel = ref(false)

function confirm()  { router.patch(`/bookings/bookings/${props.booking.id}/confirm`) }
function complete() { router.patch(`/bookings/bookings/${props.booking.id}/complete`) }
function cancel() {
  router.patch(`/bookings/bookings/${props.booking.id}/cancel`, {}, {
    onFinish: () => confirmingCancel.value = false,
  })
}
</script>

<template>
  <div class="max-w-4xl">
    <!-- Header -->
    <div class="mb-6">
      <a href="/bookings/bookings" class="text-sm text-primary hover:underline">← Bookings</a>

      <div class="flex items-start justify-between mt-3 gap-4 flex-wrap">
        <div>
          <h1 class="text-2xl font-bold text-app-text">{{ booking.reference }}</h1>
          <div class="flex items-center gap-3 mt-2">
            <Badge :type="statusType[booking.status]" dot>{{ booking.status }}</Badge>
            <span class="text-sm text-app-text/50">{{ booking.start_at }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <Button v-if="canConfirm"  variant="secondary" size="sm" @click="confirm">Confirm Booking</Button>
          <Button v-if="canComplete" size="sm"           @click="complete">Mark Complete</Button>
          <Button v-if="canCancel"   variant="danger"    size="sm" @click="confirmingCancel = true">Cancel</Button>
        </div>
      </div>
    </div>

    <!-- Details row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
      <!-- Customer -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Customer</h2>
        <p class="font-semibold text-app-text text-base">{{ booking.customer_name }}</p>
        <p class="text-sm text-app-text/60 mt-1">{{ booking.customer_email }}</p>
        <p v-if="booking.customer_phone" class="text-sm text-app-text/60">{{ booking.customer_phone }}</p>
      </div>

      <!-- Booking info -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Booking Details</h2>
        <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
          <div>
            <dt class="text-xs text-app-text/50">Service</dt>
            <dd class="text-sm font-medium text-app-text mt-0.5">{{ booking.service ?? '—' }}</dd>
          </div>
          <div v-if="booking.resource">
            <dt class="text-xs text-app-text/50">Resource</dt>
            <dd class="text-sm font-medium text-app-text mt-0.5">{{ booking.resource }}</dd>
          </div>
          <div>
            <dt class="text-xs text-app-text/50">Start</dt>
            <dd class="text-sm font-medium text-app-text mt-0.5">{{ booking.start_at }}</dd>
          </div>
          <div>
            <dt class="text-xs text-app-text/50">End</dt>
            <dd class="text-sm font-medium text-app-text mt-0.5">{{ booking.end_at }}</dd>
          </div>
          <div>
            <dt class="text-xs text-app-text/50">Duration</dt>
            <dd class="text-sm font-medium text-app-text mt-0.5">{{ booking.duration }} min</dd>
          </div>
          <div v-if="booking.deposit_amount > 0">
            <dt class="text-xs text-app-text/50">Deposit</dt>
            <dd class="text-sm font-medium mt-0.5 flex items-center gap-2">
              R {{ booking.deposit_amount }}
              <Badge :type="booking.deposit_paid ? 'success' : 'warning'">
                {{ booking.deposit_paid ? 'Paid' : 'Outstanding' }}
              </Badge>
            </dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Notes -->
    <div v-if="booking.notes"
         class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
      <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-2">Notes</h2>
      <p class="text-sm text-app-text/70 leading-relaxed">{{ booking.notes }}</p>
    </div>

    <ConfirmDialog
      :show="confirmingCancel"
      title="Cancel Booking"
      message="This booking will be marked as cancelled. The customer will need to rebook."
      confirm-label="Cancel Booking"
      danger
      @confirm="cancel"
      @cancel="confirmingCancel = false"
    />
  </div>
</template>
