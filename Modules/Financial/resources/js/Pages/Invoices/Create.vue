<script setup>
import { ref, computed, watch } from 'vue'
import { useForm }              from '@inertiajs/vue3'
import axios                    from 'axios'
import AppLayout from '@shared/layouts/AppLayout.vue'
import Input     from '@shared/components/form/Input.vue'
import Button    from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  customers: { type: Array,  default: () => [] },
  taxRates:  { type: Array,  default: () => [] },
  products:  { type: Array,  default: () => [] },
  defaults:  { type: Object, default: () => ({}) },
})

// ─── Customer ────────────────────────────────────────────────
const customerList      = ref([...props.customers])
const showNewCustomer   = ref(false)
const savingCustomer    = ref(false)
const customerErrors    = ref({})

const newCustomer = ref({
  company_name: '', contact_name: '', email: '', phone: '', vat_number: '',
})

async function createCustomer() {
  savingCustomer.value = true
  customerErrors.value = {}
  try {
    const { data } = await axios.post('/financial/api/customers', newCustomer.value)
    customerList.value.push(data)
    form.customer_id     = data.id
    showNewCustomer.value = false
    newCustomer.value    = { company_name: '', contact_name: '', email: '', phone: '', vat_number: '' }
  } catch (err) {
    if (err.response?.status === 422) {
      customerErrors.value = err.response.data.errors ?? {}
    }
  } finally {
    savingCustomer.value = false
  }
}

function cancelNewCustomer() {
  showNewCustomer.value = false
  customerErrors.value  = {}
  newCustomer.value     = { company_name: '', contact_name: '', email: '', phone: '', vat_number: '' }
}

// ─── Products catalogue ───────────────────────────────────────
const productList       = ref([...props.products])
const showNewProduct    = ref({ index: -1 })   // which line is expanding
const savingProduct     = ref(false)
const productErrors     = ref({})

const newProduct = ref({
  name: '', default_price: '', default_tax_rate: '', unit: '',
})

async function createProduct(lineIndex) {
  savingProduct.value  = true
  productErrors.value  = {}
  try {
    const { data } = await axios.post('/financial/api/products', {
      ...newProduct.value,
      default_tax_rate: newProduct.value.default_tax_rate || defaultTaxRate.value,
    })
    productList.value.push(data)
    // Auto-fill the line with the new product
    applyProduct(lineIndex, data)
    showNewProduct.value = { index: -1 }
    newProduct.value     = { name: '', default_price: '', default_tax_rate: '', unit: '' }
  } catch (err) {
    if (err.response?.status === 422) {
      productErrors.value = err.response.data.errors ?? {}
    }
  } finally {
    savingProduct.value = false
  }
}

function cancelNewProduct() {
  showNewProduct.value = { index: -1 }
  productErrors.value  = {}
  newProduct.value     = { name: '', default_price: '', default_tax_rate: '', unit: '' }
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
const urlParams           = new URLSearchParams(window.location.search)
const defaultTaxRate      = computed(() => props.taxRates.find(r => r.is_default)?.rate ?? 15)
const today               = new Date().toISOString().split('T')[0]
const defaultDueDate      = new Date(Date.now() + (props.defaults.due_days ?? 30) * 864e5).toISOString().split('T')[0]

const form = useForm({
  customer_id: urlParams.get('customer_id') ?? '',
  issue_date:  today,
  due_date:    defaultDueDate,
  notes:       '',
  lines: [
    { _productId: '', description: '', qty: 1, unit_price: 0, tax_rate: defaultTaxRate.value },
  ],
})

function addLine() {
  form.lines.push({ _productId: '', description: '', qty: 1, unit_price: 0, tax_rate: defaultTaxRate.value })
}

function removeLine(i) {
  if (form.lines.length > 1) {
    form.lines.splice(i, 1)
    if (showNewProduct.value.index === i) showNewProduct.value = { index: -1 }
  }
}

const subtotal   = computed(() => form.lines.reduce((s, l) => s + l.qty * l.unit_price, 0))
const taxTotal   = computed(() => form.lines.reduce((s, l) => s + l.qty * l.unit_price * l.tax_rate / 100, 0))
const grandTotal = computed(() => subtotal.value + taxTotal.value)

function currency(val) {
  return 'R ' + Number(val ?? 0).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

function submit() {
  // Strip the internal _productId before posting
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
            <label class="text-sm font-medium text-app-text">
              Customer <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-2">
              <select
                v-model="form.customer_id"
                class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                :class="form.errors.customer_id ? 'border-red-400' : ''"
              >
                <option value="">Select a customer…</option>
                <option v-for="c in customerList" :key="c.id" :value="c.id">
                  {{ c.company_name }}
                </option>
              </select>
              <button
                type="button"
                @click="showNewCustomer = !showNewCustomer"
                class="flex-shrink-0 px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                :class="showNewCustomer
                  ? 'border-primary bg-primary/10 text-primary'
                  : 'border-gray-300 dark:border-gray-600 text-app-text/60 hover:text-app-text hover:border-gray-400'"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    :d="showNewCustomer ? 'M6 18L18 6M6 6l12 12' : 'M12 4v16m8-8H4'" />
                </svg>
              </button>
            </div>
            <p v-if="form.errors.customer_id" class="text-xs text-red-500">{{ form.errors.customer_id }}</p>
          </div>

          <!-- Inline new customer form -->
          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
          >
            <div v-if="showNewCustomer"
                 class="sm:col-span-2 rounded-xl border-2 border-primary/20 bg-primary/5 dark:bg-primary/10 p-5">
              <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-primary">New Customer</p>
                <button type="button" @click="cancelNewCustomer"
                        class="text-xs text-app-text/50 hover:text-app-text">Cancel</button>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2 flex flex-col gap-1">
                  <label class="text-sm font-medium text-app-text">Company Name <span class="text-red-500">*</span></label>
                  <input v-model="newCustomer.company_name" type="text" placeholder="Acme Corporation"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                         :class="customerErrors.company_name ? 'border-red-400' : ''" />
                  <p v-if="customerErrors.company_name" class="text-xs text-red-500">
                    {{ customerErrors.company_name?.[0] }}
                  </p>
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-app-text">Contact Person</label>
                  <input v-model="newCustomer.contact_name" type="text" placeholder="John Smith"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-app-text">Email</label>
                  <input v-model="newCustomer.email" type="email" placeholder="john@acme.co.za"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                         :class="customerErrors.email ? 'border-red-400' : ''" />
                  <p v-if="customerErrors.email" class="text-xs text-red-500">{{ customerErrors.email?.[0] }}</p>
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-app-text">Phone</label>
                  <input v-model="newCustomer.phone" type="text" placeholder="011 555 0100"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
                <div class="flex flex-col gap-1">
                  <label class="text-sm font-medium text-app-text">VAT Number</label>
                  <input v-model="newCustomer.vat_number" type="text" placeholder="4120123456"
                         class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                </div>
              </div>
              <div class="mt-4 flex justify-end gap-2">
                <button type="button" @click="cancelNewCustomer"
                        class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text transition-colors">
                  Cancel
                </button>
                <Button type="button" size="sm" :loading="savingCustomer" @click="createCustomer">
                  Save Customer
                </Button>
              </div>
            </div>
          </Transition>

          <!-- Dates — side by side -->
          <Input v-model="form.issue_date" label="Issue Date" type="date" required :error="form.errors.issue_date" />
          <Input v-model="form.due_date"   label="Due Date"   type="date" required :error="form.errors.due_date" />
        </div>
      </div>

      <!-- ── Line Items ───────────────────────────────────────── -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Line Items</h2>
        </div>

        <div class="p-6 space-y-4">
          <div v-for="(line, i) in form.lines" :key="i" class="space-y-2">

            <!-- Line row -->
            <div class="grid grid-cols-12 gap-2 items-end">
              <!-- Product selector + description -->
              <div class="col-span-12 sm:col-span-5">
                <label v-if="i === 0" class="text-sm font-medium text-app-text block mb-1">
                  Product / Description
                </label>
                <div class="flex gap-1">
                  <select
                    v-model="line._productId"
                    @change="onProductSelect(i, line._productId)"
                    class="w-32 flex-shrink-0 px-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-xs focus:outline-none focus:ring-2 focus:ring-primary/50"
                  >
                    <option value="">— Pick —</option>
                    <option v-for="p in productList" :key="p.id" :value="p.id">{{ p.name }}</option>
                    <option value="__new__">+ New product…</option>
                  </select>
                  <input
                    v-model="line.description"
                    type="text"
                    placeholder="Description"
                    class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                  />
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
              <div class="col-span-1 flex items-end gap-1 justify-end pb-0.5">
                <span class="text-xs font-medium text-app-text/50 whitespace-nowrap hidden sm:block">
                  {{ currency(line.qty * line.unit_price) }}
                </span>
                <button v-if="form.lines.length > 1" type="button" @click="removeLine(i)"
                        class="p-1.5 text-red-400 hover:text-red-600 transition-colors flex-shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Inline new product panel -->
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="opacity-0 -translate-y-1"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition-all duration-150 ease-in"
              leave-from-class="opacity-100"
              leave-to-class="opacity-0"
            >
              <div v-if="showNewProduct.index === i"
                   class="ml-4 rounded-xl border-2 border-accent/30 bg-accent/5 dark:bg-accent/10 p-4">
                <div class="flex items-center justify-between mb-3">
                  <p class="text-sm font-semibold text-app-text">New Product / Service</p>
                  <button type="button" @click="cancelNewProduct"
                          class="text-xs text-app-text/50 hover:text-app-text">Cancel</button>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                  <div class="col-span-2 flex flex-col gap-1">
                    <label class="text-sm font-medium text-app-text">Name <span class="text-red-500">*</span></label>
                    <input v-model="newProduct.name" type="text" placeholder="e.g. Consulting (hourly)"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
                           :class="productErrors.name ? 'border-red-400' : ''" />
                    <p v-if="productErrors.name" class="text-xs text-red-500">{{ productErrors.name?.[0] }}</p>
                  </div>
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-app-text">Default Price</label>
                    <input v-model.number="newProduct.default_price" type="number" min="0" step="0.01" placeholder="0.00"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                  </div>
                  <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-app-text">Unit</label>
                    <input v-model="newProduct.unit" type="text" placeholder="hour, item, day…"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
                  </div>
                </div>
                <div class="mt-3 flex justify-end gap-2">
                  <button type="button" @click="cancelNewProduct"
                          class="px-3 py-1.5 text-sm text-app-text/60 hover:text-app-text">Cancel</button>
                  <Button type="button" size="sm" :loading="savingProduct" @click="createProduct(i)">
                    Save Product
                  </Button>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Add line -->
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

      <!-- ── Notes ────────────────────────────────────────────── -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Notes</label>
          <textarea v-model="form.notes" rows="3"
                    placeholder="Payment terms, bank details, or any additional notes for the customer…"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>
      </div>

      <!-- ── Actions ──────────────────────────────────────────── -->
      <div class="flex items-center justify-between">
        <a href="/financial/invoices" class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">
          Cancel
        </a>
        <div class="flex items-center gap-3">
          <span class="text-sm text-app-text/40">Total: <strong class="text-app-text">{{ currency(grandTotal) }}</strong></span>
          <Button type="submit" :loading="form.processing">Create Invoice</Button>
        </div>
      </div>

    </form>
  </div>
</template>
