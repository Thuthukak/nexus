<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import Button        from '@shared/components/buttons/Button.vue'
import Badge         from '@shared/components/display/Badge.vue'
import ConfirmDialog from '@shared/components/feedback/ConfirmDialog.vue'
import Modal         from '@shared/components/feedback/Modal.vue'

defineOptions({ layout: AppLayout })
defineProps({ taxRates: { type: Array, default: () => [] } })

const showModal  = ref(false)
const editingId  = ref(null)
const confirmDelete = ref(false)
const deletingId    = ref(null)

const form = useForm({
  name:         '',
  rate:         15,
  is_inclusive: true,
  is_compound:  false,
  is_default:   false,
  is_active:    true,
})

function openCreate() {
  editingId.value   = null
  form.reset()
  form.rate         = 15
  form.is_inclusive = true
  form.is_active    = true
  showModal.value   = true
}

function openEdit(rate) {
  editingId.value   = rate.id
  form.name         = rate.name
  form.rate         = rate.rate
  form.is_inclusive = rate.is_inclusive ?? true
  form.is_compound  = rate.is_compound
  form.is_default   = rate.is_default
  form.is_active    = rate.is_active
  showModal.value   = true
}

function save() {
  if (editingId.value) {
    form.patch(`/financial/tax-rates/${editingId.value}`, {
      onSuccess: () => { showModal.value = false },
    })
  } else {
    form.post('/financial/tax-rates', {
      onSuccess: () => { showModal.value = false },
    })
  }
}

function promptDelete(id) { deletingId.value = id; confirmDelete.value = true }
function handleDelete() {
  router.delete(`/financial/tax-rates/${deletingId.value}`, {
    onFinish: () => { confirmDelete.value = false; deletingId.value = null },
  })
}

// Live example calculation
const examplePrice = 100
const exampleVat = computed(() => {
  const r = Number(form.rate) || 15
  if (form.is_inclusive) {
    return (examplePrice - examplePrice / (1 + r / 100)).toFixed(2)
  }
  return (examplePrice * r / 100).toFixed(2)
})
const exampleTotal = computed(() => {
  if (form.is_inclusive) return examplePrice.toFixed(2)
  return (examplePrice + Number(exampleVat.value)).toFixed(2)
})
const exampleNet = computed(() => {
  if (form.is_inclusive) return (examplePrice - Number(exampleVat.value)).toFixed(2)
  return examplePrice.toFixed(2)
})
</script>

<template>
  <div class="max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Tax Rates</h1>
        <p class="text-sm text-app-text/60 mt-1">Manage VAT and tax rates applied to invoices and quotations</p>
      </div>
      <Button @click="openCreate">+ Add Tax Rate</Button>
    </div>

    <!-- Tax rates table -->
    <div class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden mb-4">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Name</th>
            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Rate</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-app-text/50">Pricing Mode</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-app-text/50">Default</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-app-text/50">Status</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
          <tr v-if="!taxRates.length">
            <td colspan="6" class="px-5 py-12 text-center text-app-text/40 text-sm">No tax rates yet.</td>
          </tr>
          <tr v-for="rate in taxRates" :key="rate.id"
              class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
            <td class="px-5 py-3.5">
              <p class="font-medium text-app-text">{{ rate.name }}</p>
            </td>
            <td class="px-5 py-3.5 text-right font-bold text-app-text">{{ rate.rate }}%</td>
            <td class="px-5 py-3.5 text-center">
              <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                    :class="(rate.is_inclusive ?? true)
                      ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                      : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'">
                {{ (rate.is_inclusive ?? true) ? 'Tax Inclusive' : 'Tax Exclusive' }}
              </span>
            </td>
            <td class="px-5 py-3.5 text-center">
              <span v-if="rate.is_default" class="text-primary font-bold text-sm">✓</span>
              <span v-else class="text-app-text/20 text-sm">—</span>
            </td>
            <td class="px-5 py-3.5 text-center">
              <Badge :type="rate.is_active ? 'success' : 'neutral'" dot>
                {{ rate.is_active ? 'Active' : 'Inactive' }}
              </Badge>
            </td>
            <td class="px-5 py-3.5 text-right">
              <div class="flex items-center justify-end gap-3">
                <button @click="openEdit(rate)" class="text-xs text-primary hover:underline font-medium">Edit</button>
                <button v-if="!rate.is_default" @click="promptDelete(rate.id)"
                        class="text-xs text-red-400 hover:text-red-600">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Explanation -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4">
      <p class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-1.5">
        South African VAT (Tax Inclusive) explained
      </p>
      <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">
        In South Africa, prices are displayed and charged <strong>inclusive of 15% VAT</strong> —
        meaning the price your customer sees already contains the tax.
        When <strong>Tax Inclusive</strong> is selected, a line item of <strong>R100</strong> at 15%
        will show a VAT portion of <strong>R13.04</strong> (extracted from the price),
        not added on top. The invoice total stays <strong>R100</strong>.
        Use <strong>Tax Exclusive</strong> only if your pricing is stated before tax
        (e.g. B2B quotes where prices are ex-VAT).
      </p>
    </div>
  </div>

  <!-- Create / Edit Modal -->
  <Modal :show="showModal"
         :title="editingId ? 'Edit Tax Rate' : 'New Tax Rate'"
         size="sm"
         @close="showModal = false">
    <div class="space-y-5">

      <!-- Name -->
      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Name</label>
        <input v-model="form.name" type="text" placeholder="e.g. VAT 15%"
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600
                      bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
      </div>

      <!-- Rate -->
      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Rate (%)</label>
        <input v-model.number="form.rate" type="number" min="0" max="100" step="0.01"
               class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600
                      bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        <p v-if="form.errors.rate" class="text-xs text-red-500">{{ form.errors.rate }}</p>
      </div>

      <!-- Inclusive toggle -->
      <div class="rounded-xl border-2 p-4 transition-colors"
           :class="form.is_inclusive
             ? 'border-green-200 bg-green-50 dark:bg-green-900/10 dark:border-green-800'
             : 'border-orange-200 bg-orange-50 dark:bg-orange-900/10 dark:border-orange-800'">

        <label class="flex items-start gap-3 cursor-pointer mb-3">
          <!-- Custom toggle -->
          <button type="button" @click="form.is_inclusive = !form.is_inclusive"
                  class="relative flex-shrink-0 w-11 h-6 rounded-full transition-colors mt-0.5"
                  :class="form.is_inclusive ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'">
            <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow
                         transition-transform duration-200"
                  :class="form.is_inclusive ? 'translate-x-5' : 'translate-x-0'" />
          </button>
          <div>
            <p class="text-sm font-bold"
               :class="form.is_inclusive ? 'text-green-800 dark:text-green-300' : 'text-orange-800 dark:text-orange-300'">
              {{ form.is_inclusive ? 'Tax Inclusive' : 'Tax Exclusive' }}
            </p>
            <p class="text-xs mt-0.5 leading-relaxed"
               :class="form.is_inclusive ? 'text-green-700 dark:text-green-400' : 'text-orange-700 dark:text-orange-400'">
              <template v-if="form.is_inclusive">
                Price entered already contains the tax. VAT is extracted, not added on top.
                <strong>Standard for South African VAT.</strong>
              </template>
              <template v-else>
                Tax will be added on top of the entered price.
                Use for ex-VAT / B2B pricing.
              </template>
            </p>
          </div>
        </label>

        <!-- Live example -->
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <p class="text-xs font-semibold text-app-text/50 uppercase tracking-wide">
              Live Example — Price entered: R100.00
            </p>
          </div>
          <div class="px-3 py-2 space-y-1.5 text-xs">
            <div v-if="form.is_inclusive">
              <div class="flex justify-between text-app-text/60">
                <span>Gross (price entered)</span>
                <span class="font-mono font-semibold text-app-text">R 100.00</span>
              </div>
              <div class="flex justify-between text-app-text/60">
                <span>Net excl. VAT ({{ form.rate || 15 }}%)</span>
                <span class="font-mono text-app-text/60">R {{ exampleNet }}</span>
              </div>
              <div class="flex justify-between text-orange-600 dark:text-orange-400">
                <span>VAT portion (incl. in price)</span>
                <span class="font-mono">R {{ exampleVat }}</span>
              </div>
              <div class="flex justify-between font-bold text-app-text border-t border-gray-100 dark:border-gray-800 pt-1.5 mt-1">
                <span>Invoice total</span>
                <span class="font-mono">R {{ exampleTotal }}</span>
              </div>
            </div>
            <div v-else>
              <div class="flex justify-between text-app-text/60">
                <span>Net price (excl. tax)</span>
                <span class="font-mono font-semibold text-app-text">R 100.00</span>
              </div>
              <div class="flex justify-between text-orange-600 dark:text-orange-400">
                <span>VAT added ({{ form.rate || 15 }}%)</span>
                <span class="font-mono">+ R {{ exampleVat }}</span>
              </div>
              <div class="flex justify-between font-bold text-app-text border-t border-gray-100 dark:border-gray-800 pt-1.5 mt-1">
                <span>Invoice total</span>
                <span class="font-mono">R {{ exampleTotal }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Options row -->
      <div class="flex items-center gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
          <input v-model="form.is_default" type="checkbox"
                 class="w-4 h-4 rounded border-gray-300 text-primary" />
          <span class="text-sm font-medium text-app-text">Default rate</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input v-model="form.is_active" type="checkbox"
                 class="w-4 h-4 rounded border-gray-300 text-primary" />
          <span class="text-sm font-medium text-app-text">Active</span>
        </label>
      </div>
    </div>

    <template #footer>
      <button @click="showModal = false" class="px-4 py-2 text-sm text-app-text/60">Cancel</button>
      <Button @click="save" :loading="form.processing">
        {{ editingId ? 'Save Changes' : 'Create Tax Rate' }}
      </Button>
    </template>
  </Modal>

  <ConfirmDialog :show="confirmDelete" title="Delete Tax Rate"
    message="This tax rate will be deleted. Existing invoices will not be affected."
    confirm-label="Delete" danger
    @confirm="handleDelete" @cancel="confirmDelete = false" />
</template>
