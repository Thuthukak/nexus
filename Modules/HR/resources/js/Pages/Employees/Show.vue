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
  employee:  { type: Object, required: true },
  documents: { type: Array,  default: () => [] },
  payslips:  { type: Array,  default: () => [] },
  customers: { type: Array,  default: () => [] },
  docTypes:  { type: Object, default: () => ({}) },
})

const activeTab = ref('details')
const tabs = [
  { key: 'details',   label: 'Details' },
  { key: 'documents', label: 'Documents', count: props.documents.length },
  { key: 'payslips',  label: 'Payslips',  count: props.payslips.length },
  { key: 'leave',     label: 'Leave',     count: props.employee.leave?.length ?? 0 },
]

const statusType = {
  active:     'success',
  on_leave:   'warning',
  suspended:  'danger',
  terminated: 'neutral',
}

// ── Document upload ───────────────────────────────────────────
const showDocModal   = ref(false)
const docFileInput   = ref(null)
const docFileName    = ref('')

const docForm = useForm({
  name:        '',
  type:        'contract',
  file:        null,
  visibility:  'internal',
  expiry_date: '',
  customer_id: '',
  notes:       '',
})

function onDocFileChange(e) {
  const file = e.target.files[0]
  if (! file) return
  docForm.file    = file
  docFileName.value = file.name
  if (! docForm.name) docForm.name = file.name.replace(/\.[^/.]+$/, '')
}

function submitDoc() {
  docForm.post(`/hr/employees/${props.employee.id}/documents`, {
    forceFormData: true,
    onSuccess: () => {
      showDocModal.value = false
      docForm.reset()
      docFileName.value  = ''
    },
  })
}

// ── Document delete ───────────────────────────────────────────
const confirmDocDelete = ref(false)
const deletingDocId    = ref(null)

function promptDocDelete(id) {
  deletingDocId.value    = id
  confirmDocDelete.value = true
}

function handleDocDelete() {
  router.delete(
    `/hr/employees/${props.employee.id}/documents/${deletingDocId.value}`,
    { onFinish: () => { confirmDocDelete.value = false; deletingDocId.value = null } }
  )
}

// ── Payslip upload ────────────────────────────────────────────
const showPayslipModal = ref(false)
const payslipFileInput = ref(null)
const payslipFileName  = ref('')
const currentYear      = new Date().getFullYear()
const currentMonth     = new Date().getMonth() + 1

const payslipForm = useForm({
  period_year:  currentYear,
  period_month: currentMonth,
  file:         null,
  gross_amount: '',
  net_amount:   '',
  notes:        '',
})

function onPayslipFileChange(e) {
  const file = e.target.files[0]
  if (! file) return
  payslipForm.file    = file
  payslipFileName.value = file.name
}

function submitPayslip() {
  payslipForm.post(`/hr/employees/${props.employee.id}/payslips`, {
    forceFormData: true,
    onSuccess: () => {
      showPayslipModal.value = false
      payslipForm.reset()
      payslipFileName.value  = ''
    },
  })
}

// ── Payslip delete ────────────────────────────────────────────
const confirmPayslipDelete = ref(false)
const deletingPayslipId    = ref(null)

function promptPayslipDelete(id) {
  deletingPayslipId.value    = id
  confirmPayslipDelete.value = true
}

function handlePayslipDelete() {
  router.delete(
    `/hr/employees/${props.employee.id}/payslips/${deletingPayslipId.value}`,
    { onFinish: () => { confirmPayslipDelete.value = false; deletingPayslipId.value = null } }
  )
}

const months = [
  'January','February','March','April','May','June',
  'July','August','September','October','November','December',
]

function currency(val) {
  if (! val) return '—'
  return 'R ' + Number(val).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

const expiringDocs = computed(() =>
  props.documents.filter(d => d.is_expiring || d.is_expired)
)

const leaveColumns = [
  { key: 'type',       label: 'Leave Type', sortable: true },
  { key: 'start_date', label: 'From',       sortable: true },
  { key: 'end_date',   label: 'To',         sortable: true },
  { key: 'days',       label: 'Days' },
  { key: 'status',     label: 'Status' },
]
</script>

<template>
  <div class="max-w-5xl">
    <!-- Header -->
    <div class="mb-6">
      <a href="/hr/employees" class="text-sm text-primary hover:underline">← Employees</a>
      <div class="flex items-start justify-between mt-3 gap-4 flex-wrap">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
            <span class="text-white text-xl font-bold">
              {{ employee.name?.charAt(0)?.toUpperCase() }}
            </span>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-app-text">{{ employee.name }}</h1>
            <div class="flex items-center gap-3 mt-1 flex-wrap">
              <Badge :type="statusType[employee.status]" dot>{{ employee.status }}</Badge>
              <span class="text-sm text-app-text/50">{{ employee.employee_number }}</span>
              <span v-if="employee.job_title" class="text-sm text-app-text/50">
                {{ employee.job_title }}
              </span>
            </div>
          </div>
        </div>
        <a :href="`/hr/employees/${employee.id}/edit`"
            class="px-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/70 hover:text-app-text transition-colors">
          Edit Employee
        </a>
      </div>
    </div>

    <!-- Expiry warning banner -->
    <div v-if="expiringDocs.length"
          class="mb-4 px-4 py-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl">
      <p class="text-sm font-semibold text-orange-700 dark:text-orange-400 mb-1">
        Document expiry warning
      </p>
      <ul class="text-xs text-orange-600 dark:text-orange-500 space-y-0.5">
        <li v-for="d in expiringDocs" :key="d.id">
          <span v-if="d.is_expired">⚠ {{ d.name }} — expired {{ d.expiry_date }}</span>
          <span v-else>⏰ {{ d.name }} — expires {{ d.expiry_date }}</span>
        </li>
      </ul>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 bg-gray-100 border border-gray-200 dark:bg-gray-800 rounded-xl p-1 w-fit">
      <button v-for="tab in tabs" :key="tab.key"
              @click="activeTab = tab.key"
              class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="activeTab === tab.key
                ? 'bg-primary text-white shadow-sm'
                : 'text-app-text/50 hover:text-app-text'">
        {{ tab.label }}
        <span v-if="tab.count"
              class="text-xs bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded-full">
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- ── DETAILS TAB ──────────────────────────────────────── -->
    <div v-if="activeTab === 'details'"
          class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Contact</h2>
        <dl class="space-y-2 text-sm">
          <div v-if="employee.email">
            <dt class="text-xs text-app-text/40">Email</dt>
            <dd class="font-medium text-app-text mt-0.5 truncate">{{ employee.email }}</dd>
          </div>
          <div v-if="employee.phone">
            <dt class="text-xs text-app-text/40">Phone</dt>
            <dd class="font-medium text-app-text mt-0.5">{{ employee.phone }}</dd>
          </div>
        </dl>
      </div>
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Employment</h2>
        <dl class="space-y-2 text-sm">
          <div>
            <dt class="text-xs text-app-text/40">Type</dt>
            <dd class="font-medium text-app-text mt-0.5 capitalize">
              {{ employee.employment_type?.replace('_', ' ') }}
            </dd>
          </div>
          <div>
            <dt class="text-xs text-app-text/40">Start Date</dt>
            <dd class="font-medium text-app-text mt-0.5">{{ employee.start_date }}</dd>
          </div>
        </dl>
      </div>
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">Organisation</h2>
        <dl class="space-y-2 text-sm">
          <div v-if="employee.department">
            <dt class="text-xs text-app-text/40">Department</dt>
            <dd class="font-medium text-app-text mt-0.5">{{ employee.department }}</dd>
          </div>
          <div v-if="employee.job_title">
            <dt class="text-xs text-app-text/40">Job Title</dt>
            <dd class="font-medium text-app-text mt-0.5">{{ employee.job_title }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- ── DOCUMENTS TAB ───────────────────────────────────── -->
    <div v-if="activeTab === 'documents'">
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-app-text/60">{{ documents.length }} document(s)</p>
        <Button size="sm" @click="showDocModal = true">
          <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Upload Document
        </Button>
      </div>

      <div v-if="!documents.length"
            class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-6 py-12 text-center text-app-text/40 text-sm">
        No documents uploaded yet.
      </div>

      <div v-else class="space-y-3">
        <div v-for="doc in documents" :key="doc.id"
              class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-4"
              :class="doc.is_expired ? 'border-red-200 dark:border-red-900/40' :
                    doc.is_expiring ? 'border-orange-200 dark:border-orange-900/40' : ''">
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3 flex-1 min-w-0">
              <!-- File icon -->
              <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                  <p class="font-medium text-app-text text-sm">{{ doc.name }}</p>
                  <span class="text-xs bg-gray-100 dark:bg-gray-800 text-app-text/60 px-2 py-0.5 rounded-full">
                    {{ doc.type_label }}
                  </span>
                  <span class="text-xs px-2 py-0.5 rounded-full"
                        :class="doc.visibility === 'customer'
                          ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                          : 'bg-gray-100 text-gray-500 dark:bg-gray-800'">
                    {{ doc.visibility === 'customer' ? 'Customer visible' : 'Internal' }}
                  </span>
                </div>
                <div class="flex items-center gap-3 text-xs text-app-text/40 flex-wrap">
                  <span>{{ doc.file_name }}</span>
                  <span>{{ doc.file_size }}</span>
                  <span>Uploaded {{ doc.created_at }} by {{ doc.uploaded_by }}</span>
                  <span v-if="doc.expiry_date"
                        :class="doc.is_expired ? 'text-red-500 font-medium' :
                                doc.is_expiring ? 'text-orange-500 font-medium' : ''">
                    {{ doc.is_expired ? 'Expired' : 'Expires' }} {{ doc.expiry_date }}
                  </span>
                </div>
                <p v-if="doc.notes" class="text-xs text-app-text/50 mt-1">{{ doc.notes }}</p>
              </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
              <a :href="`/hr/employees/${employee.id}/documents/${doc.id}/download`"
                  class="px-3 py-1.5 text-xs font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors">
                Download
              </a>
              <button @click="promptDocDelete(doc.id)"
                      class="px-3 py-1.5 text-xs font-medium border border-red-200 text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── PAYSLIPS TAB ─────────────────────────────────────── -->
    <div v-if="activeTab === 'payslips'">
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-app-text/60">{{ payslips.length }} payslip(s)</p>
        <Button size="sm" @click="showPayslipModal = true">
          <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Upload Payslip
        </Button>
      </div>

      <div v-if="!payslips.length"
            class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-6 py-12 text-center text-app-text/40 text-sm">
        No payslips uploaded yet.
      </div>

      <div v-else class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 bg-primary text-white dark:border-gray-800">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Period</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Gross</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Net</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Uploaded</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-for="slip in payslips" :key="slip.id"
                class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
              <td class="px-4 py-3 font-medium text-app-text">{{ slip.period_label }}</td>
              <td class="px-4 py-3 text-right text-app-text/70">{{ currency(slip.gross_amount) }}</td>
              <td class="px-4 py-3 text-right font-medium text-app-text">{{ currency(slip.net_amount) }}</td>
              <td class="px-4 py-3 text-xs text-app-text/40">{{ slip.created_at }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <a :href="`/hr/employees/${employee.id}/payslips/${slip.id}/download`"
                      class="text-xs text-primary hover:underline">Download</a>
                  <button @click="promptPayslipDelete(slip.id)"
                          class="text-xs text-red-400 hover:text-red-600 transition-colors">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── LEAVE TAB ────────────────────────────────────────── -->
    <div v-if="activeTab === 'leave'">
      <div v-if="!employee.leave?.length"
            class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 px-6 py-12 text-center text-app-text/40 text-sm">
        No leave history.
      </div>
      <div v-else class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 bg-primary text-white dark:border-gray-800">
            <tr>
              <th v-for="col in leaveColumns" :key="col.key"
                  class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">
                {{ col.label }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-for="leave in employee.leave" :key="leave.id">
              <td class="px-4 py-3 text-app-text">{{ leave.type }}</td>
              <td class="px-4 py-3 text-app-text/70">{{ leave.start_date }}</td>
              <td class="px-4 py-3 text-app-text/70">{{ leave.end_date }}</td>
              <td class="px-4 py-3 text-app-text/70">{{ leave.days }}</td>
              <td class="px-4 py-3">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                      :class="{
                        'bg-green-100 text-green-700': leave.status === 'approved',
                        'bg-yellow-100 text-yellow-700': leave.status === 'pending',
                        'bg-red-100 text-red-700': leave.status === 'rejected',
                        'bg-gray-100 text-gray-500': leave.status === 'cancelled',
                      }">
                  {{ leave.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- ── Upload Document Modal ─────────────────────────────── -->
  <Modal :show="showDocModal" title="Upload Document" size="lg" @close="showDocModal = false">
    <form @submit.prevent="submitDoc" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2 flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Document Name <span class="text-red-500">*</span></label>
          <input v-model="docForm.name" type="text"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
          <p v-if="docForm.errors.name" class="text-xs text-red-500">{{ docForm.errors.name }}</p>
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Document Type</label>
          <select v-model="docForm.type"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option v-for="(label, key) in docTypes" :key="key" :value="key">{{ label }}</option>
          </select>
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Visibility</label>
          <select v-model="docForm.visibility"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="internal">Internal Only</option>
            <option value="customer">Customer Visible</option>
          </select>
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Expiry Date <span class="text-app-text/40 font-normal">(optional)</span></label>
          <input v-model="docForm.expiry_date" type="date"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>

        <div v-if="docForm.visibility === 'customer'" class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Link to Customer <span class="text-app-text/40 font-normal">(optional)</span></label>
          <select v-model="docForm.customer_id"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="">None</option>
            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.company_name }}</option>
          </select>
        </div>

        <div class="col-span-2 flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">File <span class="text-red-500">*</span></label>
          <div class="flex items-center gap-3">
            <input ref="docFileInput" type="file"
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                    class="hidden" @change="onDocFileChange" />
            <button type="button" @click="docFileInput.click()"
                    class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-app-text/60 hover:text-app-text transition-colors">
              Choose File
            </button>
            <span class="text-sm text-app-text/50 truncate">
              {{ docFileName || 'No file selected' }}
            </span>
          </div>
          <p class="text-xs text-app-text/40">PDF, DOC, DOCX, JPG, PNG — max 20MB</p>
          <p v-if="docForm.errors.file" class="text-xs text-red-500">{{ docForm.errors.file }}</p>
        </div>

        <div class="col-span-2 flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Notes</label>
          <textarea v-model="docForm.notes" rows="2"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>
      </div>
    </form>
    <template #footer>
      <button @click="showDocModal = false"
              class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</button>
      <Button @click="submitDoc" :loading="docForm.processing">Upload Document</Button>
    </template>
  </Modal>

  <!-- ── Upload Payslip Modal ──────────────────────────────── -->
  <Modal :show="showPayslipModal" title="Upload Payslip" size="md" @close="showPayslipModal = false">
    <form @submit.prevent="submitPayslip" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Year</label>
          <input v-model.number="payslipForm.period_year" type="number"
                  :min="2000" :max="2099"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Month</label>
          <select v-model.number="payslipForm.period_month"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option v-for="(m, i) in months" :key="i + 1" :value="i + 1">{{ m }}</option>
          </select>
        </div>

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Gross Amount <span class="text-app-text/40 font-normal">(optional)</span></label>
          <input v-model.number="payslipForm.gross_amount" type="number" step="0.01" min="0"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Net Amount <span class="text-app-text/40 font-normal">(optional)</span></label>
          <input v-model.number="payslipForm.net_amount" type="number" step="0.01" min="0"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>

        <div class="col-span-2 flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">PDF File <span class="text-red-500">*</span></label>
          <div class="flex items-center gap-3">
            <input ref="payslipFileInput" type="file" accept=".pdf"
                    class="hidden" @change="onPayslipFileChange" />
            <button type="button" @click="payslipFileInput.click()"
                    class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-app-text/60 hover:text-app-text transition-colors">
              Choose PDF
            </button>
            <span class="text-sm text-app-text/50 truncate">
              {{ payslipFileName || 'No file selected' }}
            </span>
          </div>
          <p v-if="payslipForm.errors.file" class="text-xs text-red-500">{{ payslipForm.errors.file }}</p>
        </div>
      </div>
    </form>
    <template #footer>
      <button @click="showPayslipModal = false"
              class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</button>
      <Button @click="submitPayslip" :loading="payslipForm.processing">Upload Payslip</Button>
    </template>
  </Modal>

  <!-- Confirm deletes -->
  <ConfirmDialog :show="confirmDocDelete" title="Delete Document"
    message="This document will be permanently deleted from storage."
    confirm-label="Delete" danger
    @confirm="handleDocDelete" @cancel="confirmDocDelete = false" />

  <ConfirmDialog :show="confirmPayslipDelete" title="Delete Payslip"
    message="This payslip will be permanently deleted."
    confirm-label="Delete" danger
    @confirm="handlePayslipDelete" @cancel="confirmPayslipDelete = false" />
</template>
