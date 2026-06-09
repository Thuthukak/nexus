<script setup>
import StudentLayout from '@shared/layouts/StudentLayout.vue'

defineOptions({ layout: StudentLayout })

defineProps({
  enrollments: { type: Array, default: () => [] },
  stats:       { type: Object, default: () => ({}) },
})
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-xl font-bold text-gray-900 dark:text-white">My Learning</h1>
      <p class="text-sm text-gray-500 mt-0.5">Your enrolled courses</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-3 mb-6">
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-3 text-center">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Total</p>
      </div>
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-3 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ stats.active }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Active</p>
      </div>
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-3 text-center">
        <p class="text-2xl font-bold text-green-600">{{ stats.completed }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Completed</p>
      </div>
    </div>

    <!-- Course cards -->
    <div v-if="!enrollments.length"
         class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-12 text-center text-gray-400 text-sm">
      You are not enrolled in any courses yet.
    </div>

    <div v-else class="space-y-4">
      <div v-for="e in enrollments" :key="e.id"
           class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <!-- Course thumbnail banner -->
        <div class="h-24 flex items-center justify-center relative overflow-hidden"
             style="background: linear-gradient(135deg, var(--color-primary, #1E3A5F) 0%, var(--color-secondary, #2E86AB) 100%);">
          <svg class="w-10 h-10 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
          <div v-if="e.has_certificate"
               class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-0.5 rounded-full">
            ✓ Certified
          </div>
        </div>

        <div class="p-4">
          <h2 class="font-bold text-gray-900 dark:text-white mb-0.5">{{ e.course_title }}</h2>
          <p class="text-xs text-gray-500 mb-3">{{ e.cohort_name }}</p>

          <!-- Progress bar -->
          <div class="mb-3">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1.5">
              <span>Progress</span>
              <span class="font-semibold">{{ e.progress }}%</span>
            </div>
            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div class="h-full rounded-full transition-all duration-500"
                   style="background-color: var(--color-primary);"
                   :style="{ width: e.progress + '%' }" />
            </div>
          </div>

          <div class="flex items-center justify-between">
            <div class="text-xs text-gray-400">
              <span v-if="e.status === 'completed'" class="text-green-600 font-medium">
                ✓ Completed
              </span>
              <span v-else>Due: {{ e.end_date ?? 'Self-paced' }}</span>
            </div>
            <div class="flex items-center gap-2">
              <a v-if="e.has_certificate"
                 :href="`/student/learn/${e.id}/certificate`"
                 class="text-xs text-yellow-600 hover:underline font-medium">
                Certificate
              </a>
              <a :href="`/student/learn/${e.id}`"
                 class="px-4 py-2 rounded-lg text-sm font-semibold text-white"
                 style="background-color: var(--color-primary);">
                {{ e.status === 'completed' ? 'Review' : e.progress > 0 ? 'Continue' : 'Start' }} →
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
