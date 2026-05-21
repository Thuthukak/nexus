<script setup>
import { ref, computed, watch, vModelCheckbox } from 'vue'
import { useForm }              from '@inertiajs/vue3'
import axios                    from 'axios'
import AppLayout from '@shared/layouts/AppLayout.vue'
import Input     from '@shared/components/form/Input.vue'
import Button    from '@shared/components/buttons/Button.vue'
import Checkbox  from '@shared/components/buttons/Checkbox.vue'
import RadioButton   from '@shared/components/buttons/RadioButton.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  customers: { type: Array,  default: () => [] },
  taxRates:  { type: Array,  default: () => [] },
  products:  { type: Array,  default: () => [] },
  netTerms:  { type: Object, default: () => ({}) },
  defaults:  { type: Object, default: () => ({}) },
})

// ─── Net Terms ────────────────────────────────────────────────
const today          = new Date().toISOString().split('T')[0]
const selectedTerm   = ref('net_30')

function applyNetTerm(termKey) {
  selectedTerm.value = termKey
  const term = props.netTerms[termKey]
  if (term && term.days !== null) {
    const dueDate = new Date(Date.now() + term.days * 864e5)
    form.due_date = dueDate.toISOString().split('T')[0]
  }
  form.net_terms = termKey
}

// ─── Customer ────────────────────────────────────────────────
const customerList    = ref([...props.customers])
const showNewCustomer = ref(false)
const savingCustomer  = ref(false)
const customerErrors  = ref({})

const newCustomer = ref({
  company_name: '', contact_name: '', email: '', phone: '', vat_number: '',
})

async function createCustomer() {
  savingCustomer.value = true
  customerErrors.value = {}
  try {
    const { data } = await axios.post('/financial/api/customers', newCustomer.value)
    customerList.value.push(data)
    form.customer_id      = data.id
    showNewCustomer.value = false
    newCustomer.value     = { company_name: '', contact_name: '', email: '', phone: '', vat_number: '' }
  } catch (err) {
    if (err.response?.status === 422) customerErrors.value = err.response.data.errors ?? {}
  } finally {
    savingCustomer.value = false
  }
}

// ─── Products ─────────────────────────────────────────────────
const productList    = ref([...props.products])
const showNewProduct = ref({ index: -1 })
const savingProduct  = ref(false)
const productErrors  = ref({})

const newProduct = ref({ name: '', default_price: '', default_tax_rate: '', unit: '' })

async function createProduct(lineIndex) {
  savingProduct.value = true
  productErrors.value = {}
  try {
    const { data } = await axios.post('/financial/api/products', {
      ...newProduct.value,
      default_tax_rate: newProduct.value.default_tax_rate || defaultTaxRate.value,
    })
    productList.value.push(data)
    applyProduct(lineIndex, data)
    showNewProduct.value = { index: -1 }
    newProduct.value     = { name: '', default_price: '', default_tax_rate: '', unit: '' }
  } catch (err) {
    if (err.response?.status === 422) productErrors.value = err.response.data.errors ?? {}
  } finally {
    savingProduct.value = false
  }
}

function applyProduct(lineIndex, product) {
  form.lines[lineIndex].description = product.name
  form.lines[lineIndex].unit_price  = Number(product.default_price)
  form.lines[lineIndex].tax_rate    = Number(product.default_tax_rate) || defaultTaxRate.value
}

function onProductSelect(lineIndex, productId) {
  if (productId === '__new__') {
    showNewProduct.value = { index: lineIndex }
    form.lines[lineIndex]._productId = ''
    return
  }
  if (!productId) return
  const product = productList.value.find(p => p.id === productId)
  if (product) applyProduct(lineIndex, product)
}

// ─── Form ─────────────────────────────────────────────────────
const urlParams      = new URLSearchParams(window.location.search)
const defaultTaxRate = computed(() => Number(props.taxRates.find(r => r.is_default)?.rate ?? 15))

const form = useForm({
  customer_id:        urlParams.get('customer_id') ?? '',
  issue_date:         today,
  due_date:           '',
  net_terms:          'net_30',
  notes:              '',
  deposit_required:   false,
  deposit_type:       'percentage',
  deposit_percentage: 50,
  deposit_amount:     0,
  lines: [
    { _productId: '', description: '', qty: 1, unit_price: 0, tax_rate: defaultTaxRate.value },
  ],
})

// Set default
applyNetTerm('net_30')

// Keep deposit_amount in sync with live total when using percentage
const subtotal    = computed(() => form.lines.reduce((s, l) => s + l.qty * l.unit_price, 0))
const taxTotal    = computed(() => form.lines.reduce((s, l) => s + l.qty * l.unit_price * l.tax_rate / 100, 0))
const grandTotal  = computed(() => subtotal.value + taxTotal.value)

const depositPreview = computed(() => {
  if (! form.deposit_required) return 0
  if (form.deposit_type === 'fixed') return form.deposit_amount
  return grandTotal.value * form.deposit_percentage / 100
})

const balanceAfterDeposit = computed(() => grandTotal.value - depositPreview.value)

// When type switches, reset values
watch(() => form.deposit_type, (type) => {
  if (type === 'percentage') {
    form.deposit_percentage = 50
    form.deposit_amount     = 0
  } else {
    form.deposit_percentage = 0
    form.deposit_amount     = Math.round(grandTotal.value / 2 * 100) / 100
  }
})

function addLine() {
  form.lines.push({ _productId: '', description: '', qty: 1, unit_price: 0, tax_rate: defaultTaxRate.value })
}

function removeLine(i) {
  if (form.lines.length > 1) form.lines.splice(i, 1)
}

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

function submit() {
  const payload = {
    ...form.data(),
    lines: form.lines.map(({ _productId, ...line }) => line),
  }
  form.transform(() => payload).post('/financial/invoices')
}
</script>

<template>
  <div class="max-w-4xl">
    <div class="mb-6">
      <a href="/financial/invoices" class="text-sm text-primary hover:underline">← Invoices</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">New Invoice</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- ── Invoice Details ──────────────────────────────────── -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Invoice Details</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Customer selector -->
          <div class="sm:col-span-2 flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Customer <span class="text-red-500">*</span></label>
            <div class="flex gap-2">
              <select v-model="form.customer_id"
                      class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                      :class="form.errors.customer_id ? 'border-red-400' : ''">
                <option value="">Select a customer…</option>
                <option v-for="c in customerList" :key="c.id" :value="c.id">{{ c.company_name }}</option>
              </select>
              <button type="button" @click="showNewCustomer = !showNewCustomer"
                      class="flex-shrink-0 px-3 py-2 rounded-lg border text-sm transition-colors"
                      :class="showNewCustomer
                        ? 'border-primary bg-primary/10 text-primary'
                        : 'border-gray-300 dark:border-gray-600 text-app-text/60 hover:text-app-text'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    :d="showNewCustomer ? 'M6 18L18 6M6 6l12 12' : 'M12 4v16m8-8H4'" />
                </svg>
              </button>
            </div>
            <p v-if="form.errors.customer_id" class="text-xs text-red-500">{{ form.errors.customer_id }}</p>
          </div>

          <!-- Inline new customer -->
          <Transition enter-active-class="transition-all duration-200 ease-out"
                      enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
            <div v-if="showNewCustomer"
                 class="sm:col-span-2 p-4 rounded-xl border-2 border-primary/20 bg-primary/5 dark:bg-primary/10 p-5">
              <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-primary">New Customer</p>
                <button type="button" @click="showNewCustomer = false"
                        class="text-xs text-app-text/50 hover:text-app-text">Cancel</button>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2 flex flex-col gap-1">
                  <label class="text-sm font-medium text-app-text">Company Name <span class="text-red-500">*</span></label>
                  <input v-model="newCustomer.company_name" type="text" placeholder="Acme Corporation"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                         :class="customerErrors.company_name ? 'border-red-400' : ''" />
                  <p v-if="customerErrors.company_name" class="text-xs text-red-500">{{ customerErrors.company_name?.[0] }}</p>
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-app-text">Contact Person</label>
                  <input v-model="newCustomer.contact_name" type="text" placeholder="John Smith"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-app-text">Email</label>
                  <input v-model="newCustomer.email" type="email"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                         :class="customerErrors.email ? 'border-red-400' : ''" />
                  <p v-if="customerErrors.email" class="text-xs text-red-500">{{ customerErrors.email?.[0] }}</p>
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-app-text">Phone</label>
                  <input v-model="newCustomer.phone" type="text"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-app-text">VAT Number</label>
                  <input v-model="newCustomer.vat_number" type="text"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
              </div>
              <div class="mt-4 flex justify-end gap-2">
                <button type="button" @click="showNewCustomer = false"
                        class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</button>
                <Button type="button" size="sm" :loading="savingCustomer" @click="createCustomer">
                  Save Customer
                </Button>
              </div>
            </div>
          </Transition>

          <!-- Issue date -->
          <Input v-model="form.issue_date" label="Issue Date" type="date" required
                 :error="form.errors.issue_date" />

          <!-- Net terms + due date -->
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Payment Terms and Due Date</label>
            <div class="flex gap-2">
              <select @change="applyNetTerm($event.target.value)"
                      :value="selectedTerm"
                      class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
                <option v-for="(term, key) in netTerms" :key="key" :value="key">
                  {{ term.label }}
                </option>
              </select>
              <input v-model="form.due_date" type="date"
                     class="w-36 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                     :class="form.errors.due_date ? 'border-red-400' : ''" />
            </div>
            <p v-if="form.errors.due_date" class="text-xs text-red-500">{{ form.errors.due_date }}</p>
          </div>
        </div>
      </div>

      <!-- ── Line Items ─────────────────────────────────────── -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Line Items</h2>
        </div>

        <div class="p-6 space-y-4">
          <div v-for="(line, i) in form.lines" :key="i" class="space-y-2">
            <div class="grid grid-cols-12 gap-2 items-end">
              <!-- Description + product picker -->
              <div class="col-span-12 sm:col-span-5">
                <label v-if="i === 0" class="text-sm font-medium text-app-text block mb-1">Product / Description</label>
                <div class="flex gap-1">
                  <select v-model="line._productId"
                          @change="onProductSelect(i, line._productId)"
                          class="w-28 flex-shrink-0 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-xs focus:outline-none focus:ring-2 focus:ring-primary/50">
                    <option value="">— Pick —</option>
                    <option v-for="p in productList" :key="p.id" :value="p.id">{{ p.name }}</option>
                    <option value="__new__">+ New…</option>
                  </select>
                  <input v-model="line.description" type="text" placeholder="Description"
                         class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
              </div>
              <!-- Qty -->
              <div class="col-span-3 sm:col-span-2">
                <label v-if="i === 0" class="text-sm font-medium text-app-text block mb-1">Qty</label>
                <input v-model.number="line.qty" type="number" min="0.01" step="0.01"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 text-right" />
              </div>
              <!-- Unit price -->
              <div class="col-span-4 sm:col-span-2">
                <label v-if="i === 0" class="text-sm font-medium text-app-text block mb-1">Unit Price</label>
                <input v-model.number="line.unit_price" type="number" min="0" step="0.01"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 text-right" />
              </div>
              <!-- Tax -->
              <div class="col-span-4 sm:col-span-2">
                <label v-if="i === 0" class="text-sm font-medium text-app-text block mb-1">Tax</label>
                <select v-model.number="line.tax_rate"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
                  <option v-for="t in taxRates" :key="t.id" :value="Number(t.rate)">{{ t.name }}</option>
                </select>
              </div>
              <!-- Line total + remove -->
              <div class="col-span-1 flex items-end justify-end pb-0.5 gap-1">
                <span class="text-xs font-medium text-app-text/40 hidden sm:block whitespace-nowrap">
                  {{ currency(line.qty * line.unit_price) }}
                </span>
                <button v-if="form.lines.length > 1" type="button" @click="removeLine(i)"
                        class="p-1.5 text-red-400 hover:text-red-600 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Inline new product -->
            <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0">
              <div v-if="showNewProduct.index === i"
                   class="ml-4 rounded-xl border-2 border-accent/30 bg-accent/5 p-4">
                <div class="flex items-center justify-between mb-3">
                  <p class="text-sm font-semibold text-app-text">New Product / Service</p>
                  <button type="button" @click="showNewProduct = { index: -1 }"
                          class="text-xs text-app-text/50 hover:text-app-text">Cancel</button>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                  <div class="col-span-2 flex flex-col gap-1">
                    <label class="text-sm font-medium text-app-text">Name <span class="text-red-500">*</span></label>
                    <input v-model="newProduct.name" type="text" placeholder="e.g. Consulting (hourly)"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                           :class="productErrors.name ? 'border-red-400' : ''" />
                  </div>
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-app-text">Default Price</label>
                    <input v-model.number="newProduct.default_price" type="number" min="0" step="0.01"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                  </div>
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-app-text">Unit</label>
                    <input v-model="newProduct.unit" type="text" placeholder="hour, item, day…"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                  </div>
                </div>
                <div class="mt-3 flex justify-end gap-2">
                  <button type="button" @click="showNewProduct = { index: -1 }"
                          class="px-3 py-1.5 text-sm text-app-text/60 hover:text-app-text">Cancel</button>
                  <Button type="button" size="sm" :loading="savingProduct" @click="createProduct(i)">
                    Save Product
                  </Button>
                </div>
              </div>
            </Transition>
          </div>

          <button type="button" @click="addLine"
                  class="flex items-center gap-1.5 text-sm text-primary hover:underline mt-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add line item
          </button>
        </div>

        <!-- Totals -->
        <div class="border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/30">
          <div class="flex justify-end px-6 py-4">
            <div class="w-64 space-y-2">
              <div class="flex justify-between text-sm text-app-text/60">
                <span>Subtotal</span><span>{{ currency(subtotal) }}</span>
              </div>
              <div class="flex justify-between text-sm text-app-text/60">
                <span>Tax</span><span>{{ currency(taxTotal) }}</span>
              </div>
              <div class="flex justify-between text-base font-bold text-app-text pt-2 border-t border-gray-200 dark:border-gray-700">
                <span>Total</span><span>{{ currency(grandTotal) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Deposit ─────────────────────────────────────────── -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Deposit Required?</h2>
            <p class="text-xs text-app-text/40 mt-0.5">
              Customer pays deposit first, remainder on completion
            </p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <Checkbox v-model="form.deposit_required"/>
          </label>
        </div>

        <Transition enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
          <div v-if="form.deposit_required" class="space-y-4">

            <!-- Type toggle -->
            <div class="flex gap-3">
  <button
    type="button"
    @click="form.deposit_type = 'percentage'"
    class="flex-1 flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all text-left"
    :style="form.deposit_type === 'percentage'
      ? { borderColor: 'var(--color-primary)', backgroundColor: 'color-mix(in srgb, var(--color-primary) 5%, transparent)' }
      : {}"
    :class="form.deposit_type !== 'percentage' && 'border-gray-200 dark:border-gray-700 hover:border-gray-300'"
  >
    <RadioButton :modelValue="form.deposit_type === 'percentage'" @update:modelValue="form.deposit_type = 'percentage'" />
    <span class="text-sm font-medium text-app-text">Percentage (%)</span>
  </button>

  <button
    type="button"
    @click="form.deposit_type = 'fixed'"
    class="flex-1 flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all text-left"
    :style="form.deposit_type === 'fixed'
      ? { borderColor: 'var(--color-primary)', backgroundColor: 'color-mix(in srgb, var(--color-primary) 5%, transparent)' }
      : {}"
    :class="form.deposit_type !== 'fixed' && 'border-gray-200 dark:border-gray-700 hover:border-gray-300'"
  >
    <RadioButton :modelValue="form.deposit_type === 'fixed'" @update:modelValue="form.deposit_type = 'fixed'" />
    <span class="text-sm font-medium text-app-text">Fixed Amount (R)</span>
  </button>
</div>

            <!-- Input + live preview -->
            <div class="grid grid-cols-2 gap-4 items-end">
              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium text-app-text">
                  {{ form.deposit_type === 'percentage' ? 'Deposit Percentage' : 'Deposit Amount' }}
                </label>
                <div class="flex items-center gap-2">
                  <span v-if="form.deposit_type === 'fixed'"
                        class="text-sm font-medium text-app-text/50 flex-shrink-0">R</span>
                  <input v-if="form.deposit_type === 'percentage'"
                         v-model.number="form.deposit_percentage"
                         type="number" min="1" max="99" step="1"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 text-center" />
                  <input v-else
                         v-model.number="form.deposit_amount"
                         type="number" min="0" step="0.01"
                         :max="grandTotal"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 text-right" />
                  <span v-if="form.deposit_type === 'percentage'"
                        class="text-sm font-medium text-app-text/50 flex-shrink-0">%</span>
                </div>
              </div>

              <!-- Live calculation -->
              <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg px-4 py-3 space-y-1.5">
                <div class="flex justify-between text-xs text-app-text/50">
                  <span>Invoice Total</span>
                  <span>{{ currency(grandTotal) }}</span>
                </div>
                <div class="flex justify-between text-sm font-semibold text-primary">
                  <span>Deposit Due</span>
                  <span>{{ currency(depositPreview) }}</span>
                </div>
                <div class="flex justify-between text-xs text-app-text/50 pt-1 border-t border-gray-200 dark:border-gray-700">
                  <span>Balance After Deposit</span>
                  <span>{{ currency(balanceAfterDeposit) }}</span>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>

      <!-- ── Notes ───────────────────────────────────────────── -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Notes</label>
          <textarea v-model="form.notes" rows="3"
                    placeholder="Payment terms, bank details, or any additional notes for the customer…"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>
      </div>

      <!-- ── Actions ─────────────────────────────────────────── -->
      <div class="flex items-center justify-between">
        <a href="/financial/invoices"
           class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <div class="flex items-center gap-3">
          <span class="text-sm text-app-text/40">
            Total: <strong class="text-app-text">{{ currency(grandTotal) }}</strong>
            <span v-if="form.deposit_required" class="ml-2 text-primary">
              · Deposit: <strong>{{ currency(depositPreview) }}</strong>
            </span>
          </span>
          <Button type="submit" :loading="form.processing">Create Invoice</Button>
        </div>
      </div>

    </form>
  </div>
</template>
