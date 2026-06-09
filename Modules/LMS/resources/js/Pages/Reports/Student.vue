<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  enrollment:  { type: Object, required: true },
  lessons:     { type: Array,  default: () => [] },
  assignments: { type: Array,  default: () => [] },
})
</script>

<template>
  <div class="max-w-3xl">
    <div class="mb-6">
      <a href="/lms/courses" class="text-sm text-primary hover:underline">← Courses</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">
        {{ enrollment.student }} — {{ enrollment.course }}
      </h1>
      <p class="text-sm text-app-text/60 mt-1">{{ enrollment.cohort }}</p>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3 text-center">
        <p class="text-2xl font-bold text-app-text">{{ enrollment.progress }}%</p>
        <p class="text-xs text-app-text/50 mt-1">Progress</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3 text-center">
        <p class="text-sm font-bold capitalize"
           :class="enrollment.status === 'completed' ? 'text-green-600' : 'text-app-text'">
          {{ enrollment.status }}
        </p>
        <p class="text-xs text-app-text/50 mt-1">Status</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3 text-center">
        <p class="text-sm font-bold text-yellow-600">{{ enrollment.has_certificate ? 'Yes' : 'No' }}</p>
        <p class="text-xs text-app-text/50 mt-1">Certificate</p>
      </div>
    </div>

    <!-- Lesson progress -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden mb-6">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-sm font-semibold text-app-text">Lesson Progress</h2>
      </div>
      <div class="divide-y divide-gray-50 dark:divide-gray-800">
        <div v-for="l in lessons" :key="l.title"
             class="flex items-center justify-between px-5 py-3 text-sm">
          <div class="flex items-center gap-3">
            <span :class="l.completed ? 'text-green-500' : 'text-gray-300'">
              {{ l.completed ? '✓' : '○' }}
            </span>
            <div>
              <p class="font-medium text-app-text">{{ l.title }}</p>
              <p class="text-xs text-app-text/40">{{ l.completed_at ?? 'Not started' }}</p>
            </div>
          </div>
          <div v-if="l.quiz" class="text-right">
            <span class="text-xs font-medium"
                  :class="l.quiz.passed ? 'text-green-600' : 'text-red-500'">
              {{ l.quiz.passed ? 'Passed' : l.quiz.attempts > 0 ? 'Failed' : 'Pending' }}
            </span>
            <p class="text-xs text-app-text/40">{{ l.quiz.attempts }} attempt(s)</p>
            <p v-if="l.quiz.best_score !== null" class="text-xs text-app-text/40">
              Best: {{ l.quiz.best_score }}%
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Assignments -->
    <div v-if="assignments.length"
         class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-sm font-semibold text-app-text">Assignments</h2>
      </div>
      <div class="divide-y divide-gray-50 dark:divide-gray-800">
        <div v-for="a in assignments" :key="a.title"
             class="flex items-center justify-between px-5 py-3 text-sm">
          <div>
            <p class="font-medium text-app-text">{{ a.title }}</p>
            <p class="text-xs text-app-text/40">Submitted {{ a.submitted_at }}</p>
          </div>
          <div class="text-right">
            <p v-if="a.graded" class="font-semibold text-app-text">
              {{ a.grade }}/{{ a.max_marks }}
            </p>
            <p v-else class="text-xs text-yellow-600">Awaiting grade</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
