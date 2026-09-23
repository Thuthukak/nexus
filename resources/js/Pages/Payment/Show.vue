<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useForm, router }                        from '@inertiajs/vue3'

const props = defineProps({
  invoice: { type: Object, required: true },
  gateway: { type: Object, required: true },
  bank:    { type: Object, required: true },
  app:     { type: Object, required: true },
  eft:     { type: Object, default: () => ({}) },
  is_free: { type: Boolean, default: false },
})

// ── Online payment ────────────────────────────────────────────
function pay() {
  window.location.href = `/pay/${props.invoice.token}/initiate`
}

// ── EFT countdown timer ───────────────────────────────────────
const timeLeft  = ref('')
const expired   = ref(false)
let   timerRef  = null

function updateTimer() {
  if (!props.eft?.hold_expires_at) return
  const diff = new Date(props.eft.hold_expires_at) - Date.now()
  if (diff <= 0) {
    expired.value  = true
    timeLeft.value = 'Expired'
    clearInterval(timerRef)
    return
  }
  const h = Math.floor(diff / 3600000)
  const m = Math.floor((diff % 3600000) / 60000)
  const s = Math.floor((diff % 60000) / 1000)
  timeLeft.value = `${h}h ${m}m ${s}s`
}

onMounted(() => {
  if (props.eft?.hold_expires_at) {
    updateTimer()
    timerRef = setInterval(updateTimer, 1000)
  }
})
onUnmounted(() => clearInterval(timerRef))

// ── PoP upload form ───────────────────────────────────────────
const popForm      = useForm({ pop: null, pop_notes: '' })
const popFileInput = ref(null)
const showPopForm  = ref(false)
const showReupload = ref(false)

const popStatus = computed(() => props.eft?.pop_status ?? 'none')

const popStatusConfig = {
  none:     null,
  pending:  { colour: 'yellow', label: 'Proof of payment received — awaiting verification' },
  approved: { colour: 'green',  label: 'Payment verified ✓' },
  rejected: { colour: 'red',    label: 'Proof of payment could not be verified' },
}

function selectFile(e) {
  popForm.pop = e.target.files[0]
}

function submitPop() {
  if (!popForm.pop) return
  popForm.post(`/pay/${props.invoice.token}/pop`, {
    forceFormData: true,
    onSuccess: () => { showPopForm.value = false },
  })
}

// ── Helpers ───────────────────────────────────────────────────
function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

function claimFree() {
  router.post(`/pay/${props.invoice.token}/claim-free`)
}

function copyRef() {
  navigator.clipboard.writeText(props.bank.reference)
}

const isEftOnly  = computed(() => !props.gateway.configured)
const showBank   = computed(() => props.bank?.account_number)
const holdActive = computed(() => props.eft?.hold_active && !expired.value)
</script>

<template>
  <div class="min-h-screen bg-gray-50" style="font-family: Arial, sans-serif;">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="max-w-2xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <img v-if="app.logo_url" :src="app.logo_url" class="h-16 w-auto object-contain" />
          <span v-else class="text-lg font-bold text-gray-800">{{ app.name }}</span>
        </div>
        <span v-if="gateway.test_mode"
              class="text-xs font-semibold text-yellow-700 bg-yellow-100 px-3 py-1 rounded-full">
          TEST MODE
        </span>
      </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-8 space-y-6">

      <!-- Invoice card -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="bg-gray-800 px-6 py-5">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-gray-400 text-sm">Invoice from</p>
              <p class="text-white font-bold text-lg">{{ app.name }}</p>
            </div>
            <div class="text-right">
              <p class="text-gray-400 text-sm">Invoice</p>
              <p class="text-white font-bold">{{ invoice.reference }}</p>
            </div>
          </div>
        </div>

        <div class="px-6 py-5">
          <div class="flex justify-between text-sm text-gray-500 mb-4">
            <span>Billed to</span>
            <span>Due {{ invoice.due_date }}</span>
          </div>
          <p class="font-semibold text-gray-900 text-base">{{ invoice.customer_name }}</p>
          <p class="text-gray-500 text-sm">{{ invoice.customer_email }}</p>
        </div>

        <!-- Line items -->
        <div class="border-t border-gray-200">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-400">Item</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-400">Qty</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-400">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="line in invoice.lines" :key="line.description" class="border-t border-gray-50">
                <td class="px-6 py-3 text-gray-700">{{ line.description }}</td>
                <td class="px-6 py-3 text-right text-gray-500">{{ line.qty }}</td>
                <td class="px-6 py-3 text-right font-medium text-gray-900">{{ currency(line.line_total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 space-y-2">
          <div class="flex justify-between text-sm text-gray-500">
            <span>Subtotal</span><span>{{ currency(invoice.subtotal) }}</span>
          </div>
          <div class="flex justify-between text-sm text-gray-500">
            <span>Tax</span><span>{{ currency(invoice.tax_total) }}</span>
          </div>
          <div v-if="invoice.paid_total > 0" class="flex justify-between text-sm text-green-600">
            <span>Paid</span><span>{{ currency(invoice.paid_total) }}</span>
          </div>
          <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-200">
            <span>Balance Due</span>
            <span>{{ currency(invoice.balance_due) }}</span>
          </div>
        </div>
      </div>

      <!-- ── Free ticket claim (zero amount, any gateway state) ── -->
      <div v-if="is_free"
           class="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-green-100 bg-green-50">
          <h2 class="font-semibold text-green-800">Free Ticket</h2>
          <p class="text-sm text-green-600 mt-1">No payment required — claim your ticket instantly.</p>
        </div>
        <div class="px-6 py-5">
          <button @click="claimFree"
                  class="w-full py-3.5 px-4 rounded-xl bg-green-600 text-white font-semibold text-sm hover:bg-green-700 transition-colors">
            Claim Your Free Ticket
          </button>
        </div>
      </div>

      <!-- ── Online payment gateway (paid tickets only) ── -->
      <div v-if="gateway.configured && !is_free"
           class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
          <h2 class="font-semibold text-gray-900">Pay Online</h2>
          <p class="text-sm text-gray-500 mt-1">Secure payment via {{ gateway.name }}</p>
        </div>
        <div class="px-6 py-5 space-y-3">
          <div v-if="invoice.deposit_required && !invoice.deposit_paid_at"
               class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 text-sm text-blue-700">
            A deposit of <strong>{{ currency(invoice.deposit_amount) }}</strong>
            ({{ invoice.deposit_percentage }}%) is required to confirm your order.
          </div>
          <button @click="pay"
                  class="w-full py-3.5 px-4 rounded-xl bg-gray-900 text-white font-semibold text-sm hover:bg-gray-800 transition-colors">
            {{ invoice.payment_stage }} — {{ currency(invoice.amount_due_now) }}
          </button>
          <p class="text-xs text-gray-400 text-center">Redirected to {{ gateway.name }} for secure payment.</p>
        </div>
      </div>

      <!-- ── EFT / Manual payment section ── -->
      <div v-if="showBank && !is_free" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

        <!-- EFT hold banner -->
        <div v-if="isEftOnly && holdActive"
             class="bg-amber-50 border-b border-amber-100 px-6 py-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-amber-800">Tickets reserved for you</p>
              <p class="text-xs text-amber-600 mt-0.5">Complete payment before the hold expires</p>
            </div>
            <div class="text-right">
              <p class="text-xl font-bold font-mono text-amber-700">{{ timeLeft }}</p>
              <p class="text-xs text-amber-500">remaining</p>
            </div>
          </div>
        </div>

        <div v-if="isEftOnly && expired"
             class="bg-red-50 border-b border-red-100 px-6 py-4">
          <p class="text-sm font-semibold text-red-700">Your reservation has expired.</p>
          <p class="text-xs text-red-500 mt-0.5">
            The tickets have been released. Please start a new order.
          </p>
        </div>

        <div class="px-6 py-5 border-b border-gray-200">
          <h2 class="font-semibold text-gray-900">
            {{ gateway.configured ? 'Or pay via EFT / Bank Transfer' : 'Pay via EFT / Bank Transfer' }}
          </h2>
          <p v-if="isEftOnly" class="text-sm text-gray-500 mt-1">
            Use the banking details below and include your reference number.
          </p>
        </div>

        <!-- Bank details -->
        <div class="px-6 py-5">
          <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm mb-5">
            <div>
              <dt class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Account Name</dt>
              <dd class="font-medium text-gray-900">{{ bank.account_name }}</dd>
            </div>
            <div>
              <dt class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Bank</dt>
              <dd class="font-medium text-gray-900">{{ bank.bank_name }}</dd>
            </div>
            <div>
              <dt class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Account Number</dt>
              <dd class="font-semibold text-gray-900 font-mono">{{ bank.account_number }}</dd>
            </div>
            <div>
              <dt class="text-gray-400 text-xs uppercase tracking-wide mb-0.5">Branch Code</dt>
              <dd class="font-medium text-gray-900 font-mono">{{ bank.branch_code }}</dd>
            </div>
          </dl>

          <!-- Reference — highlighted CTA -->
          <div class="bg-gray-900 rounded-xl px-5 py-4 flex items-center justify-between mb-5">
            <div>
              <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Payment Reference</p>
              <p class="text-white font-bold font-mono text-xl tracking-widest">{{ bank.reference }}</p>
              <p class="text-gray-500 text-xs mt-1">Use exactly as shown</p>
            </div>
            <button @click="copyRef"
                    class="text-xs text-gray-400 hover:text-white border border-gray-700 hover:border-gray-500 px-3 py-1.5 rounded-lg transition-colors">
              Copy
            </button>
          </div>

          <p v-if="bank.instructions"
             class="text-sm text-gray-500 bg-gray-50 rounded-lg p-3 mb-5">
            {{ bank.instructions }}
          </p>

          <!-- PoP upload section — shown for EFT-only and gateway+EFT cases -->
          <template v-if="(holdActive || popStatus !== 'none') && !is_free">

            <!-- Status banners -->
            <div v-if="popStatus === 'pending'"
                 class="bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-4 mb-4">
              <div class="flex items-start gap-3">
                <span class="text-yellow-500 text-lg mt-0.5">⏳</span>
                <div>
                  <p class="text-sm font-semibold text-yellow-800">Proof of payment received</p>
                  <p class="text-xs text-yellow-600 mt-0.5">
                    Uploaded {{ eft.pop_uploaded_at }} — {{ eft.pop_file_name }}<br/>
                    We're verifying your payment. Tickets will be sent once confirmed (usually within 1 business day).
                  </p>
                </div>
              </div>
              <button @click="showReupload = true"
                      class="mt-3 text-xs text-yellow-700 hover:underline">
                Re-upload if incorrect →
              </button>
            </div>

            <!-- Re-upload form when customer clicks "Re-upload if incorrect" -->
            <div v-if="showReupload" class="border border-yellow-200 dark:border-yellow-800 rounded-xl p-5 space-y-4 mt-4">
              <p class="text-sm font-semibold text-gray-900 dark:text-white">Re-upload Proof of Payment</p>
              <div>
                <label class="text-xs text-gray-500 mb-1 block">New bank receipt <span class="text-red-400">*</span></label>
                <input ref="popFileInput" type="file"
                       accept=".pdf,.jpg,.jpeg,.png,.webp"
                       @change="selectFile"
                       class="text-sm text-gray-500 w-full" />
              </div>
              <div>
                <label class="text-xs text-gray-500 mb-1 block">Notes (optional)</label>
                <textarea v-model="popForm.pop_notes" rows="2"
                          placeholder="e.g. Correct reference used, payment made on 14 Sep"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400 resize-none" />
              </div>
              <div class="flex items-center gap-3">
                <button @click="submitPop"
                        :disabled="!popForm.pop || popForm.processing"
                        class="px-6 py-2.5 bg-gray-900 text-white text-sm font-semibold rounded-xl hover:bg-gray-800 disabled:opacity-50 transition-colors">
                  {{ popForm.processing ? 'Uploading…' : 'Re-submit Proof of Payment' }}
                </button>
                <button @click="showReupload = false; popForm.reset()"
                        class="text-sm text-gray-400 hover:text-gray-600">Cancel</button>
              </div>
            </div>

            <div v-else-if="popStatus === 'approved'"
                 class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 mb-4">
              <p class="text-sm font-semibold text-green-700">✓ Payment verified — your tickets are confirmed!</p>
            </div>

            <div v-else-if="popStatus === 'rejected'"
                 class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-4">
              <p class="text-sm font-semibold text-red-700">⚠ Proof of payment could not be verified</p>
              <p class="text-xs text-red-500 mt-1">Please upload a clear image or PDF of your bank receipt.</p>
            </div>

            <!-- Upload CTA (show when no pending/approved pop) -->
            <template v-if="['none','rejected'].includes(popStatus)">
              <div v-if="!showPopForm"
                   class="border-2 border-dashed border-gray-200 rounded-xl px-6 py-8 text-center">
                <p class="text-sm font-semibold text-gray-700 mb-1">Upload Proof of Payment</p>
                <p class="text-xs text-gray-400 mb-4">
                  After completing your EFT, upload your bank receipt here to speed up verification.
                </p>
                <button @click="showPopForm = true"
                        class="px-6 py-2.5 bg-gray-900 text-white text-sm font-semibold rounded-xl hover:bg-gray-800 transition-colors">
                  Upload Receipt / PoP
                </button>
              </div>

              <div v-else class="border border-gray-200 rounded-xl p-5 space-y-4">
                <p class="text-sm font-semibold text-gray-900">Upload Proof of Payment</p>

                <div>
                  <label class="text-xs text-gray-500 mb-1 block">Bank receipt or screenshot <span class="text-red-400">*</span></label>
                  <input ref="popFileInput" type="file"
                         accept=".pdf,.jpg,.jpeg,.png,.webp"
                         @change="selectFile"
                         class="text-sm text-gray-500 w-full" />
                  <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG or WebP — max 10MB</p>
                </div>

                <div>
                  <label class="text-xs text-gray-500 mb-1 block">Notes (optional)</label>
                  <textarea v-model="popForm.pop_notes" rows="2"
                            placeholder="e.g. Payment made on 13 Sep, reference used: INV-0042"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400 resize-none" />
                </div>

                <div class="flex items-center gap-3">
                  <button @click="submitPop"
                          :disabled="!popForm.pop || popForm.processing"
                          class="px-6 py-2.5 bg-gray-900 text-white text-sm font-semibold rounded-xl hover:bg-gray-800 disabled:opacity-50 transition-colors">
                    {{ popForm.processing ? 'Uploading…' : 'Submit Proof of Payment' }}
                  </button>
                  <button @click="showPopForm = false; popForm.reset()"
                          class="text-sm text-gray-400 hover:text-gray-600">
                    Cancel
                  </button>
                </div>
              </div>
            </template>

          </template>
        </div>
      </div>

      <!-- No payment method configured and no bank details -->
      <div v-if="!gateway.configured && !showBank"
           class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-8 text-center">
        <p class="text-gray-500 text-sm">
          Please contact us to complete your payment.<br/>
          Quote reference: <strong class="font-mono">{{ invoice.reference }}</strong>
        </p>
      </div>

      <p class="text-center text-xs text-gray-400">
        Invoice {{ invoice.reference }} · {{ app.name }}
      </p>
    </div>
  </div>
</template>
