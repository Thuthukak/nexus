<script setup>
import { ref, computed } from 'vue'
import { router }        from '@inertiajs/vue3'
import AppLayout         from '@shared/layouts/AppLayout.vue'
import Badge             from '@shared/components/display/Badge.vue'
import ConfirmDialog     from '@shared/components/feedback/ConfirmDialog.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  schedules: { type: Array, default: () => [] },
})

const statusType = {
  active:    'success',
  paused:    'warning',
  completed: 'neutral',
  cancelled: 'danger',
}

const activeCount    = computed(() => props.schedules.filter(s => s.status === 'active').length)
const pausedCount    = computed(() => props.schedules.filter(s => s.status === 'paused').length)
const completedCount = computed(() => props.schedules.filter(s => s.status === 'completed').length)

// Filter
const filterStatus = ref('all')
const filtered = computed(() =>
  filterStatus.value === 'all'
    ? props.schedules
    : props.schedules.filter(s => s.status === filterStatus.value)
)

// Actions
function pause(id)  { router.patch(`/financial/recurring/${id}/pause`) }
function resume(id) { router.patch(`/financial/recurring/${id}/resume`) }
function cancel(id) { router.patch(`/financial/recurring/${id}/cancel`) }

const confirmDelete  = ref(false)
const deletingId     = ref(null)

function promptDelete(id) {
  deletingId.value    = id
  confirmDelete.value = true
}

function handleDelete() {
  router.delete(`/financial/recurring/${deletingId.value}`, {
    onFinish: () => {
      confirmDelete.value = false
      deletingId.value    = null
    },
  })
}
</script>

<template>
  <div class="max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Recurring Invoices</h1>
        <p class="text-sm text-app-text/60 mt-1">Automated invoice schedules</p>
      </div>
      <a href="/financial/invoices"
         class="text-sm text-primary hover:underline">← Back to Invoices</a>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Active</p>
        <p class="text-2xl font-bold text-green-600">{{ activeCount }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Paused</p>
        <p class="text-2xl font-bold text-yellow-600">{{ pausedCount }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Completed</p>
        <p class="text-2xl font-bold text-app-text/40">{{ completedCount }}</p>
      </div>
    </div>

    <!-- Filter tabs -->
    <div class="flex gap-1 mb-4 bg-gray-100 dark:bg-gray-800 rounded-lg p-1 w-fit">
      <button v-for="f in ['all', 'active', 'paused', 'completed', 'cancelled']" :key="f"
              @click="filterStatus = f"
              class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors capitalize"
              :class="filterStatus === f
                ? 'bg-surface text-app-text shadow-sm'
                : 'text-app-text/50 hover:text-app-text'">
        {{ f }}
      </button>
    </div>

    <!-- Schedules list -->
    <div v-if="!filtered.length"
        class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-6 py-12 text-center">
      
      <!-- Added explicit height/width attributes and style overrides -->
      <svg width="24" height="24" style="width: 24px; height: 24px;" class="text-app-text/20 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
      </svg>
      
      <p class="text-sm text-app-text/40">No recurring schedules found.</p>
      <p class="text-xs text-app-text/30 mt-1">
        Open an invoice and choose "Make Recurring" from the menu.
      </p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="s in filtered" :key="s.id"
          class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <div class="flex items-start justify-between gap-4 flex-wrap p-4">

          <!-- Left: core info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2 flex-wrap">
              <a :href="`/financial/invoices/${s.source_invoice_id}`"
                class="font-semibold text-primary hover:underline">
                {{ s.source_reference }}
              </a>
              <Badge :type="statusType[s.status]" dot>{{ s.status }}</Badge>
              <span class="text-xs font-medium text-app-text/60 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">
                {{ s.frequency_label }}
              </span>
            </div>

            <p class="text-sm text-app-text/70 mb-3">{{ s.customer }}</p>

            <!-- Schedule details grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-2 text-xs">
              <div>
                <span class="text-app-text/40 block">Next Run</span>
                <span class="font-medium text-app-text">
                  {{ s.status === 'active' ? s.next_run_date : '—' }}
                </span>
              </div>
              <div>
                <span class="text-app-text/40 block">Last Run</span>
                <span class="font-medium text-app-text">{{ s.last_run_date ?? 'Never' }}</span>
              </div>
              <div>
                <span class="text-app-text/40 block">Occurrences</span>
                <span class="font-medium text-app-text">
                  {{ s.occurrences_count }}
                  <span v-if="s.max_occurrences"> / {{ s.max_occurrences }}</span>
                </span>
              </div>
              <div>
                <span class="text-app-text/40 block">End Date</span>
                <span class="font-medium text-app-text">{{ s.end_date ?? 'Indefinite' }}</span>
              </div>
              <div>
                <span class="text-app-text/40 block">Due Days</span>
                <span class="font-medium text-app-text">{{ s.due_days }} days after issue</span>
              </div>
              <div>
                <span class="text-app-text/40 block">Auto Send</span>
                <span class="font-medium" :class="s.auto_send ? 'text-green-600' : 'text-app-text/40'">
                  {{ s.auto_send ? 'Yes' : 'No' }}
                </span>
              </div>
              <div>
                <span class="text-app-text/40 block">Sends To</span>
                <span class="font-medium text-app-text truncate block">
                  {{ s.customer_email ?? '—' }}
                </span>
              </div>
              <div v-if="s.notes">
                <span class="text-app-text/40 block">Notes</span>
                <span class="font-medium text-app-text">{{ s.notes }}</span>
              </div>
            </div>
          </div>

          <!-- Right: action buttons -->
          <div class="flex items-center gap-2 flex-shrink-0">
            <button v-if="s.status === 'active'"
                    @click="pause(s.id)"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border border-yellow-300 text-yellow-700 dark:border-yellow-700 dark:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors">
              Pause
            </button>
            <button v-if="s.status === 'paused'"
                    @click="resume(s.id)"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border border-green-300 text-green-700 dark:border-green-700 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
              Resume
            </button>
            <button v-if="['active', 'paused'].includes(s.status)"
                    @click="cancel(s.id)"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border border-red-200 text-red-500 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
              Cancel
            </button>
            <button @click="promptDelete(s.id)"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 text-app-text/40 hover:text-red-500 hover:border-red-200 transition-colors">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <ConfirmDialog
      :show="confirmDelete"
      title="Delete Schedule"
      message="This recurring schedule will be permanently deleted. No future invoices will be generated."
      confirm-label="Delete"
      danger
      @confirm="handleDelete"
      @cancel="confirmDelete = false"
    />
  </div>
</template>
