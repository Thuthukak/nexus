<script setup>
import StudentLayout from '@shared/layouts/StudentLayout.vue'
defineOptions({ layout: StudentLayout })
const props = defineProps({
  enrollment: { type: Object, required: true },
  lesson:     { type: Object, required: true },
  attempt:    { type: Object, required: true },
  results:    { type: Array, default: () => [] },
})
</script>

<template>
  <div>
    <div class="mb-5">
      <a :href="`/student/courses/${enrollment.id}/lessons/${lesson.id}`"
         class="text-sm text-blue-600 hover:underline">← {{ lesson.title }}</a>
      <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2">Quiz Result</h1>
    </div>

    <!-- Score card -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-8 mb-6 text-center">
      <div class="text-6xl font-bold mb-2"
           :class="attempt.passed ? 'text-green-600' : 'text-red-500'">
        {{ attempt.score }}%
      </div>
      <p class="text-lg font-semibold mb-1"
         :class="attempt.passed ? 'text-green-600' : 'text-red-500'">
        {{ attempt.passed ? '🎉 Passed!' : '✗ Not passed' }}
      </p>
      <p class="text-sm text-gray-400">
        {{ attempt.marks_earned }} / {{ attempt.marks_total }} marks ·
        Pass mark: {{ attempt.pass_mark }}%
      </p>
      <p v-if="attempt.is_practice"
         class="mt-2 text-xs text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20 px-3 py-1 rounded-full inline-block">
        Practice attempt — not recorded
      </p>
    </div>

    <!-- Per-question results -->
    <div v-if="results.length" class="space-y-4">
      <div v-for="(r, idx) in results" :key="idx"
           class="bg-white dark:bg-gray-900 rounded-xl border p-5"
           :class="r.is_correct ? 'border-green-100 dark:border-green-900/30' : 'border-red-100 dark:border-red-900/30'">
        <div class="flex items-start gap-3">
          <span class="text-sm mt-0.5">{{ r.is_correct ? '✅' : '❌' }}</span>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">
              {{ idx + 1 }}. {{ r.question }}
            </p>
            <div class="space-y-1 text-xs">
              <p class="text-gray-500">
                Your answer: <span :class="r.is_correct ? 'text-green-600 font-medium' : 'text-red-500 font-medium'">
                  {{ r.given_answer ?? '(not answered)' }}
                </span>
              </p>
              <p v-if="!r.is_correct && r.correct_answer" class="text-gray-500">
                Correct answer: <span class="text-green-600 font-medium">{{ r.correct_answer }}</span>
              </p>
              <p v-if="r.explanation" class="text-gray-400 italic">{{ r.explanation }}</p>
            </div>
            <p class="text-xs text-gray-400 mt-1">
              {{ r.marks_earned }} / {{ r.marks }} marks
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-6 flex gap-3 justify-center">
      <a :href="`/student/courses/${enrollment.id}/lessons/${lesson.id}`"
         class="px-5 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300 hover:border-blue-300 transition-colors">
        Back to Lesson
      </a>
      <a v-if="!attempt.passed && !attempt.is_practice"
         :href="`/student/courses/${enrollment.id}/lessons/${lesson.id}/quiz`"
         class="px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition-colors">
        Try Again
      </a>
    </div>
  </div>
</template>
