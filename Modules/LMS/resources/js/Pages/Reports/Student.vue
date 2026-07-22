<script setup>
import AppLayout from '@shared/layouts/AppLayout.vue'
import Badge     from '@shared/components/display/Badge.vue'
defineOptions({ layout: AppLayout })
const props = defineProps({
  enrollment:  { type: Object, required: true },
  lessons:     { type: Array, default: () => [] },
  assignments: { type: Array, default: () => [] },
})
const statusColour = { active: 'info', completed: 'success', withdrawn: 'neutral', suspended: 'danger' }
</script>
<template>
  <div class="max-w-4xl">
    <div class="mb-6">
      <a :href="`/lms/courses/${enrollment.course}/cohorts`"
         class="text-sm text-primary hover:underline">← Back</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">{{ enrollment.student }}</h1>
      <p class="text-sm text-app-text/60 mt-1">
        {{ enrollment.course }} · {{ enrollment.cohort }}
      </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-4">
        <p class="text-xs text-app-text/50 mb-1">Status</p>
        <Badge :type="statusColour[enrollment.status]">{{ enrollment.status }}</Badge>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-4">
        <p class="text-xs text-app-text/50 mb-1">Progress</p>
        <p class="text-2xl font-bold text-app-text">{{ enrollment.progress }}%</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-4">
        <p class="text-xs text-app-text/50 mb-1">Enrolled</p>
        <p class="text-sm font-medium text-app-text">{{ enrollment.enrolled_at }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-4">
        <p class="text-xs text-app-text/50 mb-1">Completed</p>
        <p class="text-sm font-medium text-app-text">{{ enrollment.completed_at ?? '—' }}</p>
      </div>
    </div>

    <!-- Lessons -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 mb-5 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-sm font-semibold text-app-text">Lessons</h2>
      </div>
      <div class="divide-y divide-gray-50 dark:divide-gray-800">
        <div v-for="l in lessons" :key="l.title"
             class="flex items-center justify-between px-5 py-3">
          <div>
            <p class="text-sm font-medium text-app-text">{{ l.title }}</p>
            <p class="text-xs text-app-text/40">{{ l.type }}</p>
          </div>
          <div class="flex items-center gap-4 text-xs">
            <span :class="l.completed ? 'text-green-600 font-medium' : 'text-app-text/40'">
              {{ l.completed ? '✓ ' + l.completed_at : 'Incomplete' }}
            </span>
            <span v-if="l.quiz" :class="l.quiz.passed ? 'text-green-600' : 'text-red-500'">
              Quiz: {{ l.quiz.best_score ?? '—' }}%
              ({{ l.quiz.attempts }} attempt{{ l.quiz.attempts !== 1 ? 's' : '' }})
            </span>
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
             class="flex items-center justify-between px-5 py-3">
          <div>
            <p class="text-sm font-medium text-app-text">{{ a.title }}</p>
            <p class="text-xs text-app-text/40">Submitted: {{ a.submitted_at ?? '—' }}</p>
          </div>
          <div class="text-xs">
            <span v-if="a.graded" class="text-app-text font-medium">
              {{ a.grade }} / {{ a.max_marks }}
            </span>
            <span v-else-if="a.submitted_at" class="text-yellow-600">Awaiting grade</span>
            <span v-else class="text-app-text/40">Not submitted</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
