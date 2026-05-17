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

// Computed so they react to Inertia prop updates
const canEdit     = computed(() => !['paid', 'cancelled'].includes(props.invoice.status))
const canApprove  = computed(() => props.invoice.status === 'draft')
const canMarkSent = computed(() => ['approved', 'draft'].includes(props.invoice.status))
const canPay      = computed(() => ['approved', 'sent', 'part_paid', 'overdue'].includes(props.invoice.status))
const canCancel   = computed(() => !['paid', 'cancelled'].includes(props.invoice.status))

// Status transitions
function approve()  { router.patch(`/financial/invoices/${props.invoice.id}/approve`) }
function markSent() { router.patch(`/financial/invoices/${props.invoice.id}/mark-sent`) }
function duplicate(){ router.post(`/financial/invoices/${props.invoice.id}/duplicate`) }

const confirmCancel = ref(false)
function cancel() {
  router.patch(`/financial/invoices/${props.invoice.id}/cancel`, {}, {
    onFinish: () => confirmCancel.value = false,
  })
}

// Payment modal
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
</script>

<template>
  <div class="max-w-5xl">
    <!-- Header -->
    <div class="mb-6">
      <a href="/financial/invoices" class="text-sm text-primary hover:underline">← Invoices</a>

      <div class="flex items-start justify-between mt-3 gap-4 flex-wrap">
        <div>
          <h1 class="text-2xl font-bold text-app-text">{{ invoice.reference }}</h1>
          <div class="flex items-center gap-3 mt-2 flex-wrap">
            <Badge :type="statusType[invoice.status]" dot>{{ invoice.status }}</Badge>
            <span class="text-sm text-app-text/50">
              Issued {{ invoice.issue_date }} · Due {{ invoice.due_date }}
            </span>
            <span class="text-sm text-app-text/50">{{ invoice.currency }}</span>
          </div>
        </div>

        <!-- Top action bar -->
        <div class="flex items-center gap-2 flex-wrap">
          <a v-if="canEdit"
             :href="`/financial/invoices/${invoice.id}/edit`"
             class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-app-text/70 hover:text-app-text hover:border-gray-300 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </a>
          <button @click="duplicate"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-app-text/70 hover:text-app-text transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            Duplicate
          </button>
          <Button v-if="canApprove"  variant="secondary" size="sm" @click="approve">Approve</Button>
          <Button v-if="canMarkSent" variant="secondary" size="sm" @click="markSent">Mark as Sent</Button>
          <Button v-if="canPay"      size="sm"           @click="showPayment = true">Record Payment</Button>
          <Button v-if="canCancel"   variant="danger"    size="sm" @click="confirmCancel = true">Cancel</Button>
        </div>
      </div>
    </div>

    <!-- Summary bar -->
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
           :class="invoice.balance_due > 0 ? 'border-red-200 dark:border-red-900/50' : ''">
        <p class="text-xs text-app-text/50 mb-1">Balance Due</p>
        <p class="text-sm font-bold" :class="invoice.balance_due > 0 ? 'text-red-500' : 'text-green-600'">
          {{ currency(invoice.balance_due) }}
        </p>
      </div>
    </div>

    <!-- Main content — full width stacked -->
    <div class="space-y-6">

      <!-- Customer + Details row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <!-- Bill To -->
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
          <p v-if="invoice.customer?.vat_number" class="text-sm text-app-text/60">
            VAT: {{ invoice.customer.vat_number }}
          </p>
        </div>

        <!-- Invoice meta -->
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
          </dl>
        </div>
      </div>

      <!-- Line items — full width -->
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

      <!-- Payments history — full width -->
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
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Notes</th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Amount</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
              <tr v-for="p in invoice.payments" :key="p.id">
                <td class="px-4 py-3 text-app-text/70">{{ p.paid_at }}</td>
                <td class="px-4 py-3 text-app-text/70 capitalize">{{ p.method?.replace('_', ' ') }}</td>
                <td class="px-4 py-3 text-app-text/70">{{ p.reference ?? '—' }}</td>
                <td class="px-4 py-3 text-app-text/70">{{ p.notes ?? '—' }}</td>
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

  <!-- Payment Modal -->
  <Modal :show="showPayment" title="Record Payment" size="md" @close="showPayment = false">
    <form @submit.prevent="submitPayment" class="space-y-4">
      <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg px-4 py-3 flex justify-between text-sm">
        <span class="text-app-text/60">Balance Due</span>
        <span class="font-bold text-app-text">{{ currency(invoice.balance_due) }}</span>
      </div>

      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Amount <span class="text-red-500">*</span></label>
        <input v-model="paymentForm.amount" type="number" step="0.01"
               :max="invoice.balance_due" min="0.01" placeholder="0.00"
               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        <p v-if="paymentForm.errors.amount" class="text-xs text-red-500">{{ paymentForm.errors.amount }}</p>
      </div>

      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Payment Method</label>
        <select v-model="paymentForm.method"
                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
          <option v-for="m in paymentMethods" :key="m.value" :value="m.value">{{ m.label }}</option>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Payment Date</label>
          <input v-model="paymentForm.paid_at" type="date"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div class="flex flex-col gap-1">
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
              class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text transition-colors">
        Cancel
      </button>
      <Button @click="submitPayment" :loading="paymentForm.processing">Record Payment</Button>
    </template>
  </Modal>

  <!-- Cancel confirmation -->
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
