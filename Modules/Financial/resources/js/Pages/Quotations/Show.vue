<script setup>
import { ref, computed } from 'vue'
import { router }        from '@inertiajs/vue3'
import AppLayout         from '@shared/layouts/AppLayout.vue'
import Badge             from '@shared/components/display/Badge.vue'
import Button            from '@shared/components/buttons/Button.vue'
import ConfirmDialog     from '@shared/components/feedback/ConfirmDialog.vue'
import ActivityTimeline  from '@shared/components/display/ActivityTimeline.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  quotation: { type: Object, required: true },
})

const statusType = {
  draft:     'neutral',
  sent:      'info',
  accepted:  'success',
  declined:  'danger',
  expired:   'neutral',
  converted: 'warning',
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

// Computed permissions — react to prop updates
const canEdit    = computed(() => props.quotation.status === 'draft')
const canSend    = computed(() => props.quotation.status === 'draft')
const canAccept  = computed(() => props.quotation.status === 'sent')
const canDecline = computed(() => props.quotation.status === 'sent')
const canConvert = computed(() => props.quotation.status === 'accepted')
const canDelete  = computed(() => props.quotation.status !== 'converted')

// Kebab
const kebabOpen = ref(false)

// Actions
const sendLoading    = ref(false)
const convertLoading = ref(false)
const confirmCancel  = ref(false)

const copied = ref(false)
function copyPublicLink() {
  navigator.clipboard.writeText(props.quotation.quote_url).then(() => {
    copied.value = true
    kebabOpen.value = false
    setTimeout(() => copied.value = false, 2000)
  })
}

function send() {
  sendLoading.value = true
  kebabOpen.value   = false
  router.post(`/financial/quotations/${props.quotation.id}/send`, {}, {
    onFinish: () => sendLoading.value = false,
  })
}

function accept() {
  router.patch(`/financial/quotations/${props.quotation.id}/accept`)
}

function decline() {
  router.patch(`/financial/quotations/${props.quotation.id}/decline`)
}

function convert() {
  convertLoading.value = true
  router.post(`/financial/quotations/${props.quotation.id}/convert`, {}, {
    onFinish: () => convertLoading.value = false,
  })
}

const confirmDelete = ref(false)
function handleDelete() {
  router.delete(`/financial/quotations/${props.quotation.id}`, {}, {
    onFinish: () => confirmDelete.value = false,
  })
}
</script>

<template>
  <div class="max-w-5xl" v-click-outside="() => kebabOpen = false">
    <!-- Header -->
    <div class="mb-6">
      <a href="/financial/quotations" class="text-sm text-primary hover:underline">← Quotations</a>

      <div class="flex items-start justify-between mt-3 gap-4 flex-wrap">
        <div>
          <h1 class="text-2xl font-bold text-app-text">{{ quotation.reference }}</h1>
          <div class="flex items-center gap-3 mt-2 flex-wrap">
            <Badge :type="statusType[quotation.status]" dot>{{ quotation.status }}</Badge>
            <span class="text-sm text-app-text/50">
              Issued {{ quotation.issue_date }} · Valid until {{ quotation.valid_until }}
            </span>
            <span v-if="quotation.is_expired && quotation.status === 'sent'"
                  class="text-xs font-medium text-red-500 bg-red-50 px-2 py-0.5 rounded-full">
              Expired
            </span>
          </div>
        </div>

        <!-- Action toolbar -->
        <div class="flex items-center gap-2">
          <!-- Primary workflow actions -->
          <Button v-if="canSend" size="sm" variant="secondary"
                  :loading="sendLoading" @click="send">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Send to Customer
          </Button>

          <Button v-if="canAccept" size="sm" @click="accept">
            ✓ Accept
          </Button>
          <Button v-if="canDecline" size="sm" variant="danger" @click="decline">
            ✗ Decline
          </Button>

          <Button v-if="canConvert" size="sm"
                  :loading="convertLoading" @click="convert">
            Convert to Invoice
          </Button>

          <!-- Converted — link to invoice -->
          <a v-if="quotation.status === 'converted' && quotation.converted_invoice"
              :href="`/financial/invoices/${quotation.converted_invoice.id}`"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-primary border border-primary/30 rounded-lg hover:bg-primary/5 transition-colors">
            View Invoice {{ quotation.converted_invoice.reference }} →
          </a>

          <!-- Kebab -->
          <div class="relative">
            <button @click="kebabOpen = !kebabOpen"
                    class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 text-app-text/50 hover:text-app-text transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="5"  r="1.5"/>
                <circle cx="12" cy="12" r="1.5"/>
                <circle cx="12" cy="19" r="1.5"/>
              </svg>
            </button>

            <Transition enter-active-class="transition-all duration-150 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-1"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition-all duration-100 ease-in"
                        leave-from-class="opacity-100" leave-to-class="opacity-0 scale-95">
              <div v-if="kebabOpen"
                   class="absolute right-0 top-full mt-1 w-52 bg-surface rounded-xl shadow-lg border border-gray-100 dark:border-gray-800 py-1 z-30">

                <!-- Download PDF -->
                <a :href="`/financial/quotations/${quotation.id}/download-pdf`"
                   target="_blank" @click="kebabOpen = false"
                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800">
                  <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Download PDF
                </a>

                <!-- Edit -->
                <a v-if="canEdit" :href="`/financial/quotations/${quotation.id}/edit`"
                    @click="kebabOpen = false"
                    class="flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800">
                  <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Edit Quotation
                </a>

                <!-- Copy public link -->
                <button v-if="quotation.quote_url"
                    @click="copyPublicLink"
                    class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800">
                  <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  Copy Public Link
                </button>

                <div v-if="canDelete" class="my-1 border-t border-gray-100 dark:border-gray-800" />

                <!-- Delete -->
                <button v-if="canDelete"
                        @click="kebabOpen = false; confirmDelete = true"
                        class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Delete
                </button>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary bar -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Subtotal</p>
        <p class="text-sm font-semibold text-app-text">{{ currency(quotation.subtotal) }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Tax</p>
        <p class="text-sm font-semibold text-app-text">{{ currency(quotation.tax_total) }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Total</p>
        <p class="text-sm font-bold text-app-text">{{ currency(quotation.total) }}</p>
      </div>
    </div>

    <div class="space-y-6">
      <!-- Customer + Details -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Prepared For</h2>
          <a :href="`/financial/customers/${quotation.customer_detail?.id}`"
             class="font-semibold text-primary hover:underline text-base">
            {{ quotation.customer_detail?.company_name }}
          </a>
          <p v-if="quotation.customer_detail?.contact_name" class="text-sm text-app-text/60 mt-1">
            {{ quotation.customer_detail.contact_name }}
          </p>
          <p v-if="quotation.customer_detail?.email" class="text-sm text-app-text/60">
            {{ quotation.customer_detail.email }}
          </p>
        </div>

        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Details</h2>
          <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
            <div>
              <dt class="text-xs text-app-text/50">Issue Date</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ quotation.issue_date }}</dd>
            </div>
            <div>
              <dt class="text-xs text-app-text/50">Valid Until</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ quotation.valid_until }}</dd>
            </div>
            <div>
              <dt class="text-xs text-app-text/50">Created By</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ quotation.created_by }}</dd>
            </div>
            <div>
              <dt class="text-xs text-app-text/50">Currency</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ quotation.currency }}</dd>
            </div>
            <div v-if="quotation.sent_at">
              <dt class="text-xs text-app-text/50">Sent</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ quotation.sent_at }}</dd>
            </div>
            <div v-if="quotation.accepted_at">
              <dt class="text-xs text-app-text/50">Accepted</dt>
              <dd class="text-sm font-medium text-green-600 mt-0.5">{{ quotation.accepted_at }}</dd>
            </div>
            <div v-if="quotation.declined_at">
              <dt class="text-xs text-app-text/50">Declined</dt>
              <dd class="text-sm font-medium text-red-500 mt-0.5">{{ quotation.declined_at }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Line items -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Line Items</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Description</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50 w-20">Qty</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50 w-32">Unit Price</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50 w-20">Tax %</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50 w-32">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-for="line in quotation.lines" :key="line.id"
                  class="hover:bg-gray-50/50">
                <td class="px-4 py-3 text-app-text">{{ line.description }}</td>
                <td class="px-4 py-3 text-right text-app-text/70">{{ line.qty }}</td>
                <td class="px-4 py-3 text-right text-app-text/70">{{ currency(line.unit_price) }}</td>
                <td class="px-4 py-3 text-right text-app-text/70">{{ line.tax_rate }}%</td>
                <td class="px-4 py-3 text-right font-medium text-app-text">{{ currency(line.line_total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Activity -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <ActivityTimeline type="quotation" :id="quotation.id" />
      </div>

      <!-- Notes + Terms -->
      <div v-if="quotation.notes || quotation.terms"
           class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div v-if="quotation.notes"
             class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-2">Notes</h2>
          <p class="text-sm text-app-text/70 leading-relaxed whitespace-pre-line">{{ quotation.notes }}</p>
        </div>
        <div v-if="quotation.terms"
             class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-2">Terms & Conditions</h2>
          <p class="text-sm text-app-text/70 leading-relaxed whitespace-pre-line">{{ quotation.terms }}</p>
        </div>
      </div>
    </div>

    <ConfirmDialog
      :show="confirmDelete"
      title="Delete Quotation"
      message="This quotation will be permanently deleted."
      confirm-label="Delete"
      danger
      @confirm="handleDelete"
      @cancel="confirmDelete = false"
    />
  </div>
</template>
