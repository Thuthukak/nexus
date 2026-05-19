<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import Badge         from '@shared/components/display/Badge.vue'
import Button        from '@shared/components/buttons/Button.vue'
import Modal         from '@shared/components/feedback/Modal.vue'
import ConfirmDialog from '@shared/components/feedback/ConfirmDialog.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  invoice: { type: Object, required: true },
})

const statusType = {
  paid: 'success', draft: 'neutral', overdue: 'danger',
  sent: 'info', approved: 'warning', cancelled: 'neutral', part_paid: 'warning',
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

// ── Computed permissions ──────────────────────────────────────
const canEdit      = computed(() => !['paid', 'cancelled'].includes(props.invoice.status))
const canApprove   = computed(() => props.invoice.status === 'draft')
const canMarkSent  = computed(() => ['approved', 'draft'].includes(props.invoice.status))
const canSend      = computed(() => ['draft', 'approved'].includes(props.invoice.status))
const canPay       = computed(() => ['approved', 'sent', 'part_paid', 'overdue'].includes(props.invoice.status))
const canCancel    = computed(() => !['paid', 'cancelled'].includes(props.invoice.status))
const canRecur     = computed(() => !['cancelled'].includes(props.invoice.status))
const canReceipt   = computed(() => ['paid', 'part_paid'].includes(props.invoice.status))

// ── Kebab menu ────────────────────────────────────────────────
const kebabOpen = ref(false)

// ── Status transitions ────────────────────────────────────────
function approve()  { router.patch(`/financial/invoices/${props.invoice.id}/approve`) }
function markSent() { router.patch(`/financial/invoices/${props.invoice.id}/mark-sent`) }

const sendLoading = ref(false)
function sendInvoice() {
  sendLoading.value = true
  kebabOpen.value   = false
  router.post(`/financial/invoices/${props.invoice.id}/send`, {}, {
    onFinish: () => sendLoading.value = false,
  })
}

function duplicate() {
  kebabOpen.value = false
  router.post(`/financial/invoices/${props.invoice.id}/duplicate`)
}

const confirmCancel = ref(false)
function cancel() {
  router.patch(`/financial/invoices/${props.invoice.id}/cancel`, {}, {
    onFinish: () => confirmCancel.value = false,
  })
}

// ── Payment modal ─────────────────────────────────────────────
const showPayment = ref(false)
const paymentForm = useForm({
  amount:    '',
  method:    'bank_transfer',
  reference: '',
  notes:     '',
  paid_at:   new Date().toISOString().split('T')[0],
})

function submitPayment() {
  paymentForm.post(`/financial/invoices/${props.invoice.id}/record-payment`, {
    onSuccess: () => {
      showPayment.value = false
      paymentForm.reset()
    },
  })
}

const paymentMethods = [
  { value: 'bank_transfer', label: 'Bank Transfer' },
  { value: 'cash',          label: 'Cash' },
  { value: 'card',          label: 'Card' },
  { value: 'cheque',        label: 'Cheque' },
  { value: 'other',         label: 'Other' },
]

// ── Recurring modal ───────────────────────────────────────────
const showRecurring  = ref(false)
const recurringForm  = useForm({
  frequency:       'monthly',
  interval:        1,
  start_date:      new Date(Date.now() + 30 * 864e5).toISOString().split('T')[0],
  end_date:        '',
  max_occurrences: '',
  due_days:        30,
  auto_send:       true,
  notes:           '',
})

const frequencyOptions = [
  { value: 'daily',     label: 'Daily' },
  { value: 'weekly',    label: 'Weekly' },
  { value: 'monthly',   label: 'Monthly' },
  { value: 'quarterly', label: 'Quarterly' },
  { value: 'yearly',    label: 'Yearly' },
]

const recurringPreview = computed(() => {
  const labels = {
    daily: 'day', weekly: 'week', monthly: 'month', quarterly: '3 months', yearly: 'year',
  }
  const n    = recurringForm.interval
  const freq = labels[recurringForm.frequency] ?? recurringForm.frequency
  return n === 1 ? `Every ${freq}` : `Every ${n} ${freq}s`
})

function submitRecurring() {
  recurringForm.post(`/financial/invoices/${props.invoice.id}/make-recurring`, {
    onSuccess: () => {
      showRecurring.value = false
      recurringForm.reset()
    },
  })
}

const receiptLoading = ref(false)
function sendReceipt() {
  receiptLoading.value = true
  kebabOpen.value      = false
  router.post(`/financial/invoices/${props.invoice.id}/send-receipt`, {}, {
    onFinish: () => receiptLoading.value = false,
  })
}

function openRecurring() {
  kebabOpen.value     = false
  showRecurring.value = true
}
</script>

<template>
  <div class="max-w-5xl" v-click-outside="() => kebabOpen = false">
    <!-- ── Header ──────────────────────────────────────────── -->
    <div class="mb-6">
      <a href="/financial/invoices" class="text-sm text-primary hover:underline">← Invoices</a>

      <div class="flex items-start justify-between mt-3 gap-4 flex-wrap">
        <!-- Title + status -->
        <div>
          <h1 class="text-2xl font-bold text-app-text">{{ invoice.reference }}</h1>
          <div class="flex items-center gap-3 mt-2 flex-wrap">
            <Badge :type="statusType[invoice.status]" dot>{{ invoice.status }}</Badge>
            <span class="text-sm text-app-text/50">
              Issued {{ invoice.issue_date }} · Due {{ invoice.due_date }}
            </span>
          </div>
        </div>

        <!-- ── Action toolbar ──────────────────────────────── -->
        <div class="flex items-center gap-2">

          <!-- Workflow actions — shown contextually, not always visible -->
          <Button v-if="canApprove"  variant="secondary" size="sm" @click="approve">
            Approve
          </Button>
          <Button v-if="canMarkSent && !canApprove" variant="secondary" size="sm" @click="markSent">
            Mark Sent
          </Button>

          <!-- PRIMARY: Record Payment -->
          <Button v-if="canPay" size="sm" @click="showPayment = true">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
            </svg>
            Record Payment
          </Button>

          <!-- PRIMARY: Send -->
          <Button v-if="canSend" size="sm" variant="secondary" :loading="sendLoading" @click="sendInvoice">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Send
          </Button>

          <!-- Send Receipt — primary when paid -->
          <Button v-if="canReceipt" variant="secondary" size="sm" :loading="receiptLoading" @click="sendReceipt">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Send Receipt
          </Button>

          <!-- Kebab menu — everything else -->
          <div class="relative">
            <button
              @click="kebabOpen = !kebabOpen"
              class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 text-app-text/50 hover:text-app-text hover:border-gray-300 transition-colors"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="5"  r="1.5"/>
                <circle cx="12" cy="12" r="1.5"/>
                <circle cx="12" cy="19" r="1.5"/>
              </svg>
            </button>

            <Transition
              enter-active-class="transition-all duration-150 ease-out"
              enter-from-class="opacity-0 scale-95 translate-y-1"
              enter-to-class="opacity-100 scale-100 translate-y-0"
              leave-active-class="transition-all duration-100 ease-in"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0 scale-95"
            >
              <div v-if="kebabOpen"
                   class="absolute right-0 top-full mt-1 w-64 bg-white rounded-xl shadow-lg border border-gray-100 dark:border-gray-800 py-1 z-30">

                <!-- Download PDF -->
                <a :href="`/financial/invoices/${invoice.id}/download-pdf`"
                   target="_blank"
                   @click="kebabOpen = false"
                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                  <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Download PDF
                </a>

                <!-- Download Receipt -->
                <a v-if="canReceipt"
                   :href="`/financial/invoices/${invoice.id}/download-receipt`"
                   target="_blank"
                   @click="kebabOpen = false"
                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                  <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Download Receipt
                </a>

                <!-- Edit -->
                <a v-if="canEdit"
                   :href="`/financial/invoices/${invoice.id}/edit`"
                   @click="kebabOpen = false"
                   class="flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                  <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Edit Invoice
                </a>

                <!-- Duplicate -->
                <button @click="duplicate"
                        class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                  <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  Duplicate
                </button>

                <!-- Make Recurring -->
                <button v-if="canRecur" @click="openRecurring"
                        class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-app-text hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                  <svg class="w-4 h-4 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  Make Recurring
                </button>

                <div class="my-1 border-t border-gray-100 dark:border-gray-800" />

                <!-- Cancel -->
                <button v-if="canCancel"
                        @click="kebabOpen = false; confirmCancel = true"
                        class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Cancel Invoice
                </button>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Summary bar ─────────────────────────────────────── -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Subtotal</p>
        <p class="text-sm font-semibold text-app-text">{{ currency(invoice.subtotal) }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Tax</p>
        <p class="text-sm font-semibold text-app-text">{{ currency(invoice.tax_total) }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Total</p>
        <p class="text-sm font-bold text-app-text">{{ currency(invoice.total) }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3"
           :class="invoice.balance_due > 0 ? 'border-red-200 dark:border-red-900/40' : ''">
        <p class="text-xs text-app-text/50 mb-1">Balance Due</p>
        <p class="text-sm font-bold"
           :class="invoice.balance_due > 0 ? 'text-red-500' : 'text-green-600'">
          {{ currency(invoice.balance_due) }}
        </p>
      </div>
    </div>

    <!-- ── Content ─────────────────────────────────────────── -->
    <div class="space-y-6">

      <!-- Customer + Details row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Bill To</h2>
          <a :href="`/financial/customers/${invoice.customer?.id}`"
             class="font-semibold text-primary hover:underline text-base">
            {{ invoice.customer?.company_name }}
          </a>
          <p v-if="invoice.customer?.contact_name" class="text-sm text-app-text/60 mt-1">
            {{ invoice.customer.contact_name }}
          </p>
          <p v-if="invoice.customer?.email" class="text-sm text-app-text/60">
            {{ invoice.customer.email }}
          </p>
          <p v-if="invoice.customer?.vat_number" class="text-xs text-app-text/40 mt-1">
            VAT: {{ invoice.customer.vat_number }}
          </p>
        </div>

        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Details</h2>
          <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
            <div>
              <dt class="text-xs text-app-text/50">Issue Date</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ invoice.issue_date }}</dd>
            </div>
            <div>
              <dt class="text-xs text-app-text/50">Due Date</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ invoice.due_date }}</dd>
            </div>
            <div>
              <dt class="text-xs text-app-text/50">Created By</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ invoice.created_by }}</dd>
            </div>
            <div>
              <dt class="text-xs text-app-text/50">Currency</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ invoice.currency }}</dd>
            </div>
            <div v-if="invoice.last_sent_at">
              <dt class="text-xs text-app-text/50">Last Sent</dt>
              <dd class="text-sm font-medium text-app-text mt-0.5">{{ invoice.last_sent_at }}</dd>
            </div>
            <div v-if="invoice.receipt_sent_at">
              <dt class="text-xs text-app-text/50">Receipt Sent</dt>
              <dd class="text-sm font-medium text-green-600 mt-0.5">{{ invoice.receipt_sent_at }}</dd>
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
              <tr v-for="line in invoice.lines" :key="line.id"
                  class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
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

      <!-- Payments -->
      <div v-if="invoice.payments?.length"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Payment History</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Method</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Reference</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-for="p in invoice.payments" :key="p.id">
                <td class="px-4 py-3 text-app-text/70">{{ p.paid_at }}</td>
                <td class="px-4 py-3 text-app-text/70 capitalize">{{ p.method?.replace('_', ' ') }}</td>
                <td class="px-4 py-3 text-app-text/70">{{ p.reference ?? '—' }}</td>
                <td class="px-4 py-3 text-right font-medium text-green-600">{{ currency(p.amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="invoice.notes"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-2">Notes</h2>
        <p class="text-sm text-app-text/70 leading-relaxed whitespace-pre-line">{{ invoice.notes }}</p>
      </div>
    </div>
  </div>

  <!-- ── Payment Modal ──────────────────────────────────────── -->
  <Modal :show="showPayment" title="Record Payment" size="md" @close="showPayment = false">
    <form @submit.prevent="submitPayment" class="space-y-4">
      <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg px-4 py-3 flex justify-between text-sm">
        <span class="text-app-text/60">Balance Due</span>
        <span class="font-bold text-app-text">{{ currency(invoice.balance_due) }}</span>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1 col-span-2 sm:col-span-1">
          <label class="text-sm font-medium text-app-text">Amount <span class="text-red-500">*</span></label>
          <input v-model="paymentForm.amount" type="number" step="0.01"
                 :max="invoice.balance_due" min="0.01" placeholder="0.00"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
          <p v-if="paymentForm.errors.amount" class="text-xs text-red-500">{{ paymentForm.errors.amount }}</p>
        </div>

        <div class="flex flex-col gap-1 col-span-2 sm:col-span-1">
          <label class="text-sm font-medium text-app-text">Payment Date</label>
          <input v-model="paymentForm.paid_at" type="date"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>

        <div class="flex flex-col gap-1 col-span-2 sm:col-span-1">
          <label class="text-sm font-medium text-app-text">Method</label>
          <select v-model="paymentForm.method"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option v-for="m in paymentMethods" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
        </div>

        <div class="flex flex-col gap-1 col-span-2 sm:col-span-1">
          <label class="text-sm font-medium text-app-text">Reference</label>
          <input v-model="paymentForm.reference" type="text" placeholder="EFT ref, cheque no…"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
      </div>

      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Notes</label>
        <textarea v-model="paymentForm.notes" rows="2"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
      </div>
    </form>

    <template #footer>
      <button @click="showPayment = false"
              class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</button>
      <Button @click="submitPayment" :loading="paymentForm.processing">Record Payment</Button>
    </template>
  </Modal>

  <!-- ── Recurring Modal ────────────────────────────────────── -->
  <Modal :show="showRecurring" title="Make Recurring" size="lg" @close="showRecurring = false">
    <form @submit.prevent="submitRecurring" class="space-y-5">
      <!-- Preview pill -->
      <div class="bg-primary/10 dark:bg-primary/20 rounded-lg px-4 py-3 flex items-center gap-3">
        <svg class="w-5 h-5 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <div>
          <p class="text-sm font-semibold text-primary">{{ recurringPreview }}</p>
          <p class="text-xs text-primary/60">
            Invoice {{ invoice.reference }} will be used as the template
          </p>
        </div>
      </div>

      <!-- Frequency + interval -->
      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Frequency</label>
          <select v-model="recurringForm.frequency"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option v-for="f in frequencyOptions" :key="f.value" :value="f.value">{{ f.label }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Every</label>
          <div class="flex items-center gap-2">
            <input v-model.number="recurringForm.interval" type="number" min="1" max="12"
                   class="w-20 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 text-center" />
            <span class="text-sm text-app-text/60 capitalize">{{ recurringForm.frequency }}</span>
          </div>
        </div>
      </div>

      <!-- Start + due days -->
      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">First Invoice Date <span class="text-red-500">*</span></label>
          <input v-model="recurringForm.start_date" type="date"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
          <p v-if="recurringForm.errors.start_date" class="text-xs text-red-500">{{ recurringForm.errors.start_date }}</p>
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Payment Due (days)</label>
          <input v-model.number="recurringForm.due_days" type="number" min="1" max="365"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
      </div>

      <!-- End conditions -->
      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">End Date <span class="text-app-text/40 font-normal">(optional)</span></label>
          <input v-model="recurringForm.end_date" type="date"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Max Occurrences <span class="text-app-text/40 font-normal">(optional)</span></label>
          <input v-model.number="recurringForm.max_occurrences" type="number" min="1" placeholder="Unlimited"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
      </div>

      <!-- Auto send -->
      <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
        <input v-model="recurringForm.auto_send" type="checkbox" id="auto_send"
               class="mt-0.5 w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
        <div>
          <label for="auto_send" class="text-sm font-medium text-app-text cursor-pointer">
            Automatically send to customer
          </label>
          <p class="text-xs text-app-text/50 mt-0.5">
            When enabled, each generated invoice will be emailed to {{ invoice.customer?.email ?? 'the customer' }} immediately.
          </p>
        </div>
      </div>

      <!-- Notes -->
      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Notes <span class="text-app-text/40 font-normal">(optional)</span></label>
        <textarea v-model="recurringForm.notes" rows="2" placeholder="e.g. Monthly retainer for Q1 2026"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
      </div>
    </form>

    <template #footer>
      <button @click="showRecurring = false"
              class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</button>
      <Button @click="submitRecurring" :loading="recurringForm.processing">
        Create Schedule
      </Button>
    </template>
  </Modal>

  <!-- ── Cancel confirmation ────────────────────────────────── -->
  <ConfirmDialog
    :show="confirmCancel"
    title="Cancel Invoice"
    message="This invoice will be marked as cancelled. This action cannot be undone."
    confirm-label="Cancel Invoice"
    danger
    @confirm="cancel"
    @cancel="confirmCancel = false"
  />
</template>
