<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge     from '@shared/components/display/Badge.vue'
defineOptions({ layout: AppLayout })
const props = defineProps({
  course:       { type: Object, required: true },
  stats:        { type: Object, required: true },
  cohort_stats: { type: Array, default: () => [] },
})
</script>
<template>
  <div class="max-w-4xl">
    <div class="mb-6">
      <a href="/lms/reports" class="text-sm text-primary hover:underline">← Reports</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">{{ course.title }}</h1>
      <p class="text-sm text-app-text/60 mt-1">Course report</p>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <p class="text-xs text-app-text/50 mb-1">Total Enrolled</p>
        <p class="text-3xl font-bold text-app-text">{{ stats.total_enrollments }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <p class="text-xs text-app-text/50 mb-1">Completed</p>
        <p class="text-3xl font-bold text-green-600">{{ stats.completed }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <p class="text-xs text-app-text/50 mb-1">Completion Rate</p>
        <p class="text-3xl font-bold text-app-text">{{ stats.completion_rate }}%</p>
      </div>
    </div>

    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-sm font-semibold text-app-text">By Cohort</h2>
      </div>
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-app-text/50 uppercase tracking-wider">Cohort</th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-app-text/50 uppercase tracking-wider">Enrolled</th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-app-text/50 uppercase tracking-wider">Completed</th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-app-text/50 uppercase tracking-wider">Avg Progress</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
          <tr v-for="c in cohort_stats" :key="c.cohort_name"
              class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
            <td class="px-5 py-3 font-medium text-app-text">{{ c.cohort_name }}</td>
            <td class="px-4 py-3 text-center text-app-text/60">{{ c.total }}</td>
            <td class="px-4 py-3 text-center text-green-600 font-medium">{{ c.completed }}</td>
            <td class="px-4 py-3 text-center text-app-text/60">
              {{ c.avg_progress ? Math.round(c.avg_progress) : 0 }}%
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
