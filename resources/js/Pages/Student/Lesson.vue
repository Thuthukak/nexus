<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import StudentLayout from '@shared/layouts/StudentLayout.vue'
defineOptions({ layout: StudentLayout })

const props = defineProps({
  enrollment: { type: Object, required: true },
  course:     { type: Object, required: true },
  lesson:     { type: Object, required: true },
  progress:   { type: Object, required: true },
  quiz:       { type: Object, default: null },
})

const marking = ref(false)

function markComplete() {
  marking.value = true
  router.post(
    `/student/courses/${props.enrollment.id}/lessons/${props.lesson.id}/complete`,
    { time_spent_seconds: 0 },
    { onFinish: () => { marking.value = false } }
  )
}
</script>

<template>
  <div>
    <div class="mb-4">
      <a :href="`/student/courses/${enrollment.id}`"
         class="text-sm text-blue-600 hover:underline">← {{ course.title }}</a>
      <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2">{{ lesson.title }}</h1>
    </div>

    <!-- Video embed -->
    <div v-if="lesson.type === 'video' && lesson.embed_url"
         class="mb-5 rounded-xl overflow-hidden aspect-video bg-black">
      <iframe :src="lesson.embed_url" class="w-full h-full"
              allowfullscreen frameborder="0" />
    </div>

    <!-- Text content -->
    <div v-if="lesson.content"
         class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-6 mb-5 prose prose-sm dark:prose-invert max-w-none"
         v-html="lesson.content" />

    <!-- Files -->
    <div v-if="lesson.files?.length"
         class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 mb-5">
      <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Downloads</h2>
      <div class="space-y-2">
        <a v-for="f in lesson.files" :key="f.id"
           :href="`/student/courses/${enrollment.id}/files/${f.id}`"
           class="flex items-center gap-2 text-sm text-blue-600 hover:underline">
          📎 {{ f.name }}
        </a>
      </div>
    </div>

    <!-- Quiz section -->
    <div v-if="lesson.type === 'quiz' && quiz"
         class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-6 mb-5">
      <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">{{ quiz.title }}</h2>
      <p v-if="quiz.instructions" class="text-sm text-gray-500 mb-4">{{ quiz.instructions }}</p>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5 text-center">
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
          <p class="text-xs text-gray-400 mb-1">Pass Mark</p>
          <p class="text-lg font-bold text-gray-900 dark:text-white">{{ quiz.pass_mark }}%</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
          <p class="text-xs text-gray-400 mb-1">Questions</p>
          <p class="text-lg font-bold text-gray-900 dark:text-white">{{ quiz.question_count }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
          <p class="text-xs text-gray-400 mb-1">Attempts Left</p>
          <p class="text-lg font-bold text-gray-900 dark:text-white">
            {{ quiz.can_attempt.attempts_left ?? '—' }}
          </p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
          <p class="text-xs text-gray-400 mb-1">Best Score</p>
          <p class="text-lg font-bold"
             :class="quiz.passed ? 'text-green-600' : 'text-gray-900 dark:text-white'">
            {{ quiz.best_score !== null ? quiz.best_score + '%' : '—' }}
          </p>
        </div>
      </div>

      <div v-if="quiz.passed" class="text-center py-4">
        <p class="text-green-600 font-semibold text-sm">✓ You have passed this quiz!</p>
      </div>

      <div v-else class="flex flex-col sm:flex-row gap-3 justify-center">
        <a v-if="quiz.can_attempt.allowed"
           :href="`/student/courses/${enrollment.id}/lessons/${lesson.id}/quiz`"
           class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition-colors">
          Start Quiz
        </a>
        <p v-else class="text-sm text-red-500 text-center">{{ quiz.can_attempt.reason }}</p>

        <a v-if="quiz.allow_practice"
           :href="`/student/courses/${enrollment.id}/lessons/${lesson.id}/quiz?practice=1`"
           class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 text-sm font-medium hover:border-blue-300 transition-colors">
          Practice Mode
        </a>
      </div>
    </div>

    <!-- Mark complete -->
    <div v-if="lesson.type !== 'quiz'"
         class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5">
      <div>
        <p v-if="progress.completed" class="text-sm text-green-600 font-medium">
          ✓ Completed {{ progress.completed_at }}
        </p>
        <p v-else class="text-sm text-gray-500">Mark this lesson as complete when you're done.</p>
      </div>
      <button v-if="!progress.completed"
              @click="markComplete"
              :disabled="marking"
              class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors">
        {{ marking ? 'Saving…' : 'Mark Complete' }}
      </button>
    </div>
  </div>
</template>
