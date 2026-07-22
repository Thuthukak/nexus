<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
defineOptions({ layout: AppLayout })
const props = defineProps({ courses: { type: Array, default: () => [] } })
</script>
<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">Reports</h1>
      <p class="text-sm text-app-text/60 mt-1">Course completion overview</p>
    </div>
    <div v-if="!courses.length"
         class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-6 py-14 text-center text-sm text-app-text/40">
      No published courses found.
    </div>
    <div v-else class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-800/50">
          <tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-app-text/50 uppercase tracking-wider">Course</th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-app-text/50 uppercase tracking-wider">Cohorts</th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-app-text/50 uppercase tracking-wider">Enrolled</th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-app-text/50 uppercase tracking-wider">Completed</th>
            <th class="text-center px-4 py-3 text-xs font-semibold text-app-text/50 uppercase tracking-wider">Rate</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
          <tr v-for="c in courses" :key="c.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
            <td class="px-5 py-3 font-medium text-app-text">{{ c.title }}</td>
            <td class="px-4 py-3 text-center text-app-text/60">{{ c.cohorts }}</td>
            <td class="px-4 py-3 text-center text-app-text/60">{{ c.total }}</td>
            <td class="px-4 py-3 text-center text-green-600 font-medium">{{ c.completed }}</td>
            <td class="px-4 py-3 text-center">
              <span class="text-app-text/60">
                {{ c.total > 0 ? Math.round(c.completed / c.total * 100) : 0 }}%
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <a :href="`/lms/courses/${c.id}/report`"
                 class="text-xs text-primary hover:underline">View →</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
