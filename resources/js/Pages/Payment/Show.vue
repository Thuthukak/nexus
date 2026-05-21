<script setup>
import { ref, computed } from 'vue'
import { useForm }       from '@inertiajs/vue3'

const props = defineProps({
  invoice: { type: Object, required: true },
  gateway: { type: Object, required: true },
  bank:    { type: Object, required: true },
  app:     { type: Object, required: true },
})

const form = useForm({})

function pay() {
  window.location.href = `/pay/${props.invoice.token}/initiate`
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
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
        <span v-if="gateway.test_mode"
              class="text-xs font-semibold text-yellow-700 bg-yellow-100 px-3 py-1 rounded-full">
          TEST MODE
        </span>
      </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-8 space-y-6">
      <!-- Invoice header -->
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
        <div class="border-t border-gray-100">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-400">Item</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-400">Qty</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-400">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="line in invoice.lines" :key="line.description"
                  class="border-t border-gray-50">
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

      <!-- Payment options -->
      <div v-if="gateway.configured" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Pay Online</h2>
          <p class="text-sm text-gray-500 mt-1">Secure payment via {{ gateway.name }}</p>
        </div>
        <div class="px-6 py-5 space-y-3">
          <!-- Deposit context message -->
          <div v-if="invoice.deposit_required && !invoice.deposit_paid_at"
               class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 text-sm text-blue-700">
            A deposit of <strong>{{ currency(invoice.deposit_amount) }}</strong>
            ({{ invoice.deposit_percentage }}%) is required to confirm your order.
            The remaining balance of
            <strong>{{ currency(invoice.total - invoice.deposit_amount) }}</strong>
            will be due on completion.
          </div>

          <div v-if="invoice.deposit_required && invoice.deposit_paid_at && invoice.balance_due > 0"
               class="bg-green-50 border border-green-100 rounded-lg px-4 py-3 text-sm text-green-700">
            ✓ Deposit paid. The outstanding balance of
            <strong>{{ currency(invoice.balance_due) }}</strong> is now due.
          </div>

          <!-- Single smart pay button — backend decides amount -->
          <button @click="pay"
                  :disabled="form.processing"
                  class="w-full py-3.5 px-4 rounded-xl bg-gray-900 text-white font-semibold text-sm hover:bg-gray-800 transition-colors disabled:opacity-50">
            <span v-if="form.processing">Redirecting to payment…</span>
            <span v-else>
              {{ invoice.payment_stage }} — {{ currency(invoice.amount_due_now) }}
            </span>
          </button>

          <p class="text-xs text-gray-400 text-center">
            Redirected to {{ gateway.name }} for secure payment.
          </p>
        </div>
      </div>

      <!-- Bank transfer details -->
      <div v-if="bank.account_number" class="bg-white rounded-2xl border border-gray-200 shadow-sm">
        <div class="px-6 py-5 border-b border-gray-100">
          <h2 class="font-semibold text-gray-900">Pay via EFT / Bank Transfer</h2>
        </div>
        <div class="px-6 py-5">
          <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div>
              <dt class="text-gray-400 text-xs uppercase tracking-wide">Account Name</dt>
              <dd class="font-medium text-gray-900 mt-0.5">{{ bank.account_name }}</dd>
            </div>
            <div>
              <dt class="text-gray-400 text-xs uppercase tracking-wide">Bank</dt>
              <dd class="font-medium text-gray-900 mt-0.5">{{ bank.bank_name }}</dd>
            </div>
            <div>
              <dt class="text-gray-400 text-xs uppercase tracking-wide">Account Number</dt>
              <dd class="font-semibold text-gray-900 mt-0.5 font-mono">{{ bank.account_number }}</dd>
            </div>
            <div>
              <dt class="text-gray-400 text-xs uppercase tracking-wide">Branch Code</dt>
              <dd class="font-medium text-gray-900 mt-0.5 font-mono">{{ bank.branch_code }}</dd>
            </div>
            <div class="col-span-2">
              <dt class="text-gray-400 text-xs uppercase tracking-wide">Payment Reference</dt>
              <dd class="font-bold text-gray-900 mt-0.5 font-mono text-base">{{ bank.reference }}</dd>
            </div>
          </dl>
          <p v-if="bank.instructions" class="mt-4 text-sm text-gray-500 bg-gray-50 rounded-lg p-3">
            {{ bank.instructions }}
          </p>
        </div>
      </div>

      <p class="text-center text-xs text-gray-400">
        Invoice {{ invoice.reference }} · {{ app.name }}
      </p>
    </div>
  </div>
</template>
