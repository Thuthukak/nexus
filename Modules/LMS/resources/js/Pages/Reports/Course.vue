<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

defineProps({
  course:       { type: Object, required: true },
  stats:        { type: Object, required: true },
  cohort_stats: { type: Array,  default: () => [] },
})
</script>

<template>
  <div class="max-w-4xl">
    <div class="mb-6">
      <a :href="`/lms/courses/${course.id}/edit`"
         class="text-sm text-primary hover:underline">← {{ course.title }}</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">Course Report</h1>
    </div>

    <!-- Overall stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-4 text-center">
        <p class="text-3xl font-bold text-app-text">{{ stats.total_enrollments }}</p>
        <p class="text-xs text-app-text/50 mt-1">Total Enrolled</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-4 text-center">
        <p class="text-3xl font-bold text-green-600">{{ stats.completed }}</p>
        <p class="text-xs text-app-text/50 mt-1">Completed</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-4 text-center">
        <p class="text-3xl font-bold text-app-text">{{ stats.completion_rate }}%</p>
        <p class="text-xs text-app-text/50 mt-1">Completion Rate</p>
      </div>
    </div>

    <!-- Per-cohort breakdown -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-sm font-semibold text-app-text">Cohort Breakdown</h2>
      </div>
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900/50">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Cohort</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-app-text/50">Enrolled</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-app-text/50">Completed</th>
            <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-app-text/50">Avg Progress</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
          <tr v-for="c in cohort_stats" :key="c.cohort_name"
              class="hover:bg-gray-50/50">
            <td class="px-5 py-3 font-medium text-app-text">{{ c.cohort_name }}</td>
            <td class="px-5 py-3 text-center text-app-text/70">{{ c.total }}</td>
            <td class="px-5 py-3 text-center text-green-600 font-medium">{{ c.completed }}</td>
            <td class="px-5 py-3 text-center">
              <div class="flex items-center justify-center gap-2">
                <div class="w-20 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                  <div class="h-full bg-primary rounded-full"
                       :style="{ width: (c.avg_progress ?? 0) + '%' }" />
                </div>
                <span class="text-xs text-app-text/60">{{ Math.round(c.avg_progress ?? 0) }}%</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
