<script setup>
import StudentLayout from '@shared/layouts/StudentLayout.vue'
defineOptions({ layout: StudentLayout })
const props = defineProps({ enrollments: { type: Array, default: () => [] } })
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-xl font-bold text-gray-900 dark:text-white">My Courses</h1>
      <p class="text-sm text-gray-500 mt-1">{{ enrollments.length }} course(s) enrolled</p>
    </div>

    <div v-if="!enrollments.length"
         class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 px-6 py-16 text-center text-gray-400 text-sm">
      You are not enrolled in any courses yet.
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <a v-for="e in enrollments" :key="e.enrollment_id"
         :href="`/student/courses/${e.enrollment_id}`"
         class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden hover:shadow-md transition-shadow block">
        <div class="h-32 bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900/30 dark:to-gray-800 flex items-center justify-center">
          <img v-if="e.thumbnail_url" :src="e.thumbnail_url" class="h-full w-full object-cover" />
          <svg v-else class="w-10 h-10 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <div class="p-5">
          <h2 class="font-semibold text-gray-900 dark:text-white text-base mb-1">{{ e.course_title }}</h2>
          <p class="text-xs text-gray-400 mb-3">{{ e.cohort_name }}</p>

          <!-- Progress bar -->
          <div class="mb-2">
            <div class="flex justify-between text-xs text-gray-400 mb-1">
              <span>Progress</span>
              <span>{{ e.progress }}%</span>
            </div>
            <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
              <div class="h-full bg-blue-500 rounded-full transition-all"
                   :style="{ width: e.progress + '%' }" />
            </div>
          </div>

          <div class="flex items-center justify-between mt-3">
            <span v-if="e.status === 'completed'"
                  class="text-xs font-medium text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">
              ✓ Completed {{ e.completed_at }}
            </span>
            <span v-else class="text-xs text-gray-400">In progress</span>
            <span v-if="e.has_certificate"
                  class="text-xs text-blue-600 font-medium">🏆 Certificate</span>
          </div>
        </div>
      </a>
    </div>
  </div>
</template>
