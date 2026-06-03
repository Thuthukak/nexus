<script setup>
import { ref, onMounted } from 'vue'
import axios              from 'axios'

const props = defineProps({
  type: { type: String, required: true }, // 'invoice', 'quotation', 'employee' etc
  id:   { type: String, required: true },
})

const activities = ref([])
const loading    = ref(true)
const expanded   = ref(null)

onMounted(async () => {
  try {
    const { data } = await axios.get(`/activity/${props.type}/${props.id}`)
    activities.value = data
  } catch {}
  finally {
    loading.value = false
  }
})

function toggleExpand(id) {
  expanded.value = expanded.value === id ? null : id
}

const logColour = {
  created:  'bg-green-100 dark:bg-green-900/30 text-green-600 border-green-200 dark:border-green-800',
  updated:  'bg-blue-100 dark:bg-blue-900/30 text-blue-600 border-blue-200 dark:border-blue-800',
  deleted:  'bg-red-100 dark:bg-red-900/30 text-red-600 border-red-200 dark:border-red-800',
  default:  'bg-gray-100 dark:bg-gray-800 text-gray-500 border-gray-200 dark:border-gray-700',
}

function colour(event) {
  return logColour[event] ?? logColour.default
}
</script>

<template>
  <div>
    <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Activity</h2>

    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i"
           class="h-12 bg-gray-100 dark:bg-gray-800 rounded-lg animate-pulse" />
    </div>

    <div v-else-if="!activities.length"
         class="text-sm text-app-text/40 py-4 text-center">
      No activity recorded yet.
    </div>

    <div v-else class="relative">
      <!-- Vertical line -->
      <div class="absolute left-3.5 top-0 bottom-0 w-px bg-gray-200 dark:bg-gray-700" />

      <div class="space-y-4">
        <div v-for="activity in activities" :key="activity.id" class="relative pl-10">
          <!-- Dot -->
          <div class="absolute left-0 top-1.5 w-7 h-7 rounded-full border-2 flex items-center justify-center bg-surface"
               :class="colour(activity.event)">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="activity.event === 'created'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              <path v-else-if="activity.event === 'updated'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              <path v-else-if="activity.event === 'deleted'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <!-- Content -->
          <div
            class="bg-surface rounded-lg border border-gray-100 dark:border-gray-800 px-4 py-3"
            :class="activity.changes ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/40' : ''"
            @click="activity.changes ? toggleExpand(activity.id) : null"
          >
            <div class="flex items-start justify-between gap-2">
              <p class="text-sm font-medium text-app-text">{{ activity.description }}</p>
              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-xs text-app-text/40" :title="activity.created_at_full">
                  {{ activity.created_at }}
                </span>
                <svg v-if="activity.changes"
                     class="w-3.5 h-3.5 text-app-text/30 transition-transform duration-200 flex-shrink-0"
                     :class="expanded === activity.id ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
            <p v-if="activity.causer" class="text-xs text-app-text/50 mt-0.5">
              by {{ activity.causer.name }}
            </p>

            <!-- Changes table -->
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="opacity-0 -translate-y-1"
              enter-to-class="opacity-100 translate-y-0"
            >
              <div v-if="expanded === activity.id && activity.changes"
                   class="mt-3 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                <table class="w-full text-xs">
                  <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                      <th class="px-3 py-1.5 text-left text-app-text/50 font-semibold uppercase tracking-wide">Field</th>
                      <th class="px-3 py-1.5 text-left text-app-text/50 font-semibold uppercase tracking-wide">Before</th>
                      <th class="px-3 py-1.5 text-left text-app-text/50 font-semibold uppercase tracking-wide">After</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="change in activity.changes" :key="change.field"
                        class="border-t border-gray-100 dark:border-gray-700">
                      <td class="px-3 py-1.5 font-medium text-app-text/70 capitalize">{{ change.field }}</td>
                      <td class="px-3 py-1.5 font-mono text-red-600 dark:text-red-400 line-through max-w-24 truncate">
                        {{ change.old || '—' }}
                      </td>
                      <td class="px-3 py-1.5 font-mono text-green-600 dark:text-green-400 max-w-24 truncate">
                        {{ change.new || '—' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
