<script setup>
import { computed }  from 'vue'
import { useForm }   from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import Input         from '@shared/components/form/Input.vue'
import Button        from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  invoice:   { type: Object, required: true },
  customers: { type: Array,  default: () => [] },
  taxRates:  { type: Array,  default: () => [] },
})

const form = useForm({
  customer_id: props.invoice.customer_id,
  issue_date:  props.invoice.issue_date,
  due_date:    props.invoice.due_date,
  notes:       props.invoice.notes ?? '',
  lines:       props.invoice.lines.map(l => ({
    description: l.description,
    qty:         Number(l.qty),
    unit_price:  Number(l.unit_price),
    tax_rate:    Number(l.tax_rate),
  })),
})

function addLine() {
  const defaultTax = props.taxRates.find(r => r.is_default)?.rate ?? 15
  form.lines.push({ description: '', qty: 1, unit_price: 0, tax_rate: defaultTax })
}

function removeLine(i) {
  form.lines.splice(i, 1)
}

const subtotal = computed(() =>
  form.lines.reduce((s, l) => s + l.qty * l.unit_price, 0)
)
const taxTotal = computed(() =>
  form.lines.reduce((s, l) => s + (l.qty * l.unit_price * l.tax_rate / 100), 0)
)
const grandTotal = computed(() => subtotal.value + taxTotal.value)

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

function submit() {
  form.put(`/financial/invoices/${props.invoice.id}`)
}
</script>

<template>
  <div class="max-w-4xl">
    <div class="mb-6">
      <a :href="`/financial/invoices/${invoice.id}`"
         class="text-sm text-primary hover:underline">← Invoice</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Edit Invoice</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <!-- Customer + dates -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Invoice Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="sm:col-span-1 flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Customer <span class="text-red-500">*</span></label>
            <select v-model="form.customer_id"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
              <option value="">Select customer…</option>
              <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.company_name }}</option>
            </select>
          </div>
          <Input v-model="form.issue_date" label="Issue Date" type="date" required :error="form.errors.issue_date" />
          <Input v-model="form.due_date"   label="Due Date"   type="date" required :error="form.errors.due_date" />
        </div>
      </div>

      <!-- Lines -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Line Items</h2>
        </div>
        <div class="p-6 space-y-3">
          <div v-for="(line, i) in form.lines" :key="i" class="grid grid-cols-12 gap-3 items-start">
            <div class="col-span-12 sm:col-span-5">
              <Input v-model="line.description" :label="i === 0 ? 'Description' : ''" placeholder="Item description" />
            </div>
            <div class="col-span-4 sm:col-span-2">
              <Input v-model.number="line.qty" :label="i === 0 ? 'Qty' : ''" type="number" min="0.01" step="0.01" />
            </div>
            <div class="col-span-4 sm:col-span-2">
              <Input v-model.number="line.unit_price" :label="i === 0 ? 'Unit Price' : ''" type="number" min="0" step="0.01" />
            </div>
            <div class="col-span-3 sm:col-span-2">
              <div class="flex flex-col gap-1">
                <label v-if="i === 0" class="text-sm font-medium text-app-text">Tax %</label>
                <select v-model.number="line.tax_rate"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
                  <option v-for="t in taxRates" :key="t.id" :value="Number(t.rate)">
                    {{ t.name }} ({{ t.rate }}%)
                  </option>
                </select>
              </div>
            </div>
            <div class="col-span-1 flex items-end pb-1">
              <button type="button" @click="removeLine(i)"
                      v-if="form.lines.length > 1"
                      class="p-1.5 text-red-400 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <button type="button" @click="addLine"
                  class="text-sm text-primary hover:underline flex items-center gap-1 mt-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add line item
          </button>
        </div>

        <!-- Totals -->
        <div class="border-t border-gray-100 dark:border-gray-800 px-6 py-4 space-y-2 bg-gray-50 dark:bg-gray-900/30">
          <div class="flex justify-between text-sm text-app-text/60">
            <span>Subtotal</span><span>{{ currency(subtotal) }}</span>
          </div>
          <div class="flex justify-between text-sm text-app-text/60">
            <span>Tax</span><span>{{ currency(taxTotal) }}</span>
          </div>
          <div class="flex justify-between text-base font-bold text-app-text">
            <span>Total</span><span>{{ currency(grandTotal) }}</span>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Notes</label>
          <textarea v-model="form.notes" rows="3"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>
      </div>

      <div class="flex items-center justify-end gap-3">
        <a :href="`/financial/invoices/${invoice.id}`"
           class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Save Changes</Button>
      </div>
    </form>
  </div>
</template>
