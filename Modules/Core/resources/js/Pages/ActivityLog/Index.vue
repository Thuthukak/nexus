<script setup>
import { ref }      from 'vue'
import { router }   from '@inertiajs/vue3'
import AppLayout    from '@shared/layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  activities: { type: Object, required: true },
  modules:    { type: Array,  default: () => [] },
  users:      { type: Array,  default: () => [] },
  filters:    { type: Object, default: () => ({}) },
})

const search    = ref(props.filters.search   ?? '')
const module    = ref(props.filters.module   ?? '')
const userId    = ref(props.filters.user_id  ?? '')
const fromDate  = ref(props.filters.from     ?? '')
const toDate    = ref(props.filters.to       ?? '')

function applyFilters() {
  router.get('/activity', {
    search:  search.value   || undefined,
    module:  module.value   || undefined,
    user_id: userId.value   || undefined,
    from:    fromDate.value || undefined,
    to:      toDate.value   || undefined,
  }, { preserveState: true, replace: true })
}

function clearFilters() {
  search.value   = ''
  module.value   = ''
  userId.value   = ''
  fromDate.value = ''
  toDate.value   = ''
  router.get('/activity', {}, { preserveState: true, replace: true })
}

function goToPage(url) {
  if (url) router.visit(url)
}

const moduleColour = {
  invoice:   'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
  quotation: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
  customer:  'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
  employee:  'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
  booking:   'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
  user:      'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
  module:    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
  system:    'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
  default:   'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
}

function colour(logName) {
  return moduleColour[logName] ?? moduleColour.default
}

const expandedId = ref(null)
function toggleExpand(id) {
  expandedId.value = expandedId.value === id ? null : id
}

const hasFilters = ref(
  !!(props.filters.search || props.filters.module || props.filters.user_id || props.filters.from || props.filters.to)
)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">Activity Log</h1>
      <p class="text-sm text-app-text/60 mt-1">
        A complete audit trail of all actions taken in the platform
      </p>
    </div>

    <!-- Filters -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-4 mb-6 space-y-3">
      <div class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-44">
          <label class="text-xs font-medium text-app-text/50 mb-1 block">Search</label>
          <input v-model="search" @keyup.enter="applyFilters"
                 placeholder="Search descriptions…"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div>
          <label class="text-xs font-medium text-app-text/50 mb-1 block">Module</label>
          <select v-model="module"
                  class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="">All modules</option>
            <option v-for="m in modules" :key="m" :value="m" class="capitalize">{{ m }}</option>
          </select>
        </div>
        <div>
          <label class="text-xs font-medium text-app-text/50 mb-1 block">User</label>
          <select v-model="userId"
                  class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="">All users</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div>
          <label class="text-xs font-medium text-app-text/50 mb-1 block">From</label>
          <input v-model="fromDate" type="date"
                 class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div>
          <label class="text-xs font-medium text-app-text/50 mb-1 block">To</label>
          <input v-model="toDate" type="date"
                 class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <button @click="applyFilters"
                class="px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
          Filter
        </button>
        <button v-if="hasFilters" @click="clearFilters"
                class="px-4 py-2 rounded-lg text-sm text-app-text/60 hover:text-app-text border border-gray-200 dark:border-gray-700">
          Clear
        </button>
      </div>
    </div>

    <!-- Activity list -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
      <div v-if="!activities.data?.length"
           class="px-6 py-16 text-center text-app-text/40">
        <svg class="w-10 h-10 mx-auto mb-3 text-app-text/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p class="text-sm">No activity found.</p>
      </div>

      <div v-else>
        <div v-for="(activity, i) in activities.data" :key="activity.id">
          <!-- Main row -->
          <div
            class="flex items-start gap-4 px-5 py-4 transition-colors"
            :class="[
              i > 0 ? 'border-t border-gray-50 dark:border-gray-800' : '',
              activity.changes ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/40' : '',
            ]"
            @click="activity.changes ? toggleExpand(activity.id) : null"
          >
            <!-- Log name badge -->
            <div class="flex-shrink-0 pt-0.5">
              <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full capitalize"
                    :class="colour(activity.log_name)">
                {{ activity.log_name }}
              </span>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-app-text">{{ activity.description }}</p>
              <div class="flex items-center gap-3 mt-1 flex-wrap">
                <span v-if="activity.causer"
                      class="text-xs text-app-text/50 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ activity.causer.name }}
                </span>
                <span v-else class="text-xs text-app-text/30 italic">System</span>
                <span class="text-xs text-app-text/30">·</span>
                <span class="text-xs text-app-text/40" :title="activity.created_at_full">
                  {{ activity.created_at }}
                </span>
              </div>
            </div>

            <!-- Expand chevron if there are changes -->
            <div v-if="activity.changes" class="flex-shrink-0 pt-1">
              <svg class="w-4 h-4 text-app-text/30 transition-transform duration-200"
                   :class="expandedId === activity.id ? 'rotate-180' : ''"
                   fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>

          <!-- Expanded changes -->
          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
          >
            <div v-if="expandedId === activity.id && activity.changes"
                 class="px-5 pb-4 ml-24 border-t border-gray-50 dark:border-gray-800">
              <div class="mt-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg overflow-hidden">
                <table class="w-full text-xs">
                  <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                      <th class="px-3 py-2 text-left font-semibold text-app-text/50 uppercase tracking-wide">Field</th>
                      <th class="px-3 py-2 text-left font-semibold text-app-text/50 uppercase tracking-wide">Before</th>
                      <th class="px-3 py-2 text-left font-semibold text-app-text/50 uppercase tracking-wide">After</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="change in activity.changes" :key="change.field"
                        class="border-t border-gray-200 dark:border-gray-700">
                      <td class="px-3 py-2 font-medium text-app-text/70 capitalize">{{ change.field }}</td>
                      <td class="px-3 py-2 font-mono text-red-600 dark:text-red-400 line-through max-w-xs truncate">
                        {{ change.old || '—' }}
                      </td>
                      <td class="px-3 py-2 font-mono text-green-600 dark:text-green-400 max-w-xs truncate">
                        {{ change.new || '—' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="activities.last_page > 1"
         class="mt-4 flex items-center justify-between text-sm">
      <p class="text-app-text/50">
        Showing {{ activities.from }}–{{ activities.to }} of {{ activities.total }} entries
      </p>
      <div class="flex items-center gap-1">
        <button
          v-for="link in activities.links" :key="link.label"
          @click="goToPage(link.url)"
          :disabled="!link.url"
          v-html="link.label"
          class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          :class="link.active
            ? 'bg-primary text-primary-text'
            : 'text-app-text/60 hover:text-app-text border border-gray-200 dark:border-gray-700'"
        />
      </div>
    </div>
  </div>
</template>
