<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { router }    from '@inertiajs/vue3'
import axios         from 'axios'
import StudentLayout from '@shared/layouts/StudentLayout.vue'

defineOptions({ layout: StudentLayout })

const props = defineProps({
  enrollment:    { type: Object, required: true },
  lesson:        { type: Object, required: true },
  quiz:          { type: Object, default: null },
  section_title: { type: String, default: '' },
  course_title:  { type: String, default: '' },
})

// ── Progress tracking ─────────────────────────────────────────
let startTime   = Date.now()
const completed = ref(false)

async function markComplete() {
  const timeSpent = Math.round((Date.now() - startTime) / 1000)
  await axios.post(
    `/student/learn/${props.enrollment.id}/lesson/${props.lesson.id}/complete`,
    { time_spent_seconds: timeSpent }
  )
  completed.value = true
}

// ── Quiz state ────────────────────────────────────────────────
const quizMode    = ref(null)  // null | 'practice' | 'real'
const answers     = ref({})
const submitting  = ref(false)
const quizResult  = ref(null)

function startQuiz(mode) {
  quizMode.value = mode
  answers.value  = {}
  quizResult.value = null
}

function selectAnswer(questionId, answer) {
  answers.value[questionId] = answer
}

const allAnswered = computed(() =>
  props.quiz?.questions?.every(q => answers.value[q.id] !== undefined) ?? false
)

async function submitQuiz() {
  submitting.value = true
  try {
    const { data } = await axios.post(
      `/student/learn/${props.enrollment.id}/lesson/${props.lesson.id}/quiz`,
      {
        answers:     answers.value,
        is_practice: quizMode.value === 'practice',
      }
    )
    quizResult.value = data
    if (data.attempt.passed && quizMode.value === 'real') {
      completed.value = true
    }
  } finally {
    submitting.value = false
  }
}

function retryQuiz() {
  quizResult.value = null
  answers.value    = {}
}
</script>

<template>
  <div class="max-w-2xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
      <a :href="`/student/learn/${enrollment.id}`"
         class="text-primary hover:underline">{{ course_title }}</a>
      <span>›</span>
      <span class="text-gray-400">{{ section_title }}</span>
      <span>›</span>
      <span class="text-gray-700 dark:text-gray-300 truncate">{{ lesson.title }}</span>
    </div>

    <!-- Lesson title -->
    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ lesson.title }}</h1>

    <!-- ── VIDEO LESSON ───────────────────────────────────────── -->
    <div v-if="lesson.type === 'video'" class="space-y-4">
      <!-- Embed video -->
      <div v-if="lesson.video_type === 'embed' && lesson.embed_url"
           class="relative aspect-video rounded-2xl overflow-hidden bg-black">
        <iframe :src="lesson.embed_url"
                class="absolute inset-0 w-full h-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen />
      </div>

      <!-- Uploaded video -->
      <div v-else-if="lesson.video_type === 'upload'"
           class="rounded-2xl overflow-hidden bg-black">
        <video controls class="w-full" preload="metadata">
          <source :src="`/student/learn/${enrollment.id}/files/${lesson.id}`" />
          Your browser does not support HTML5 video.
        </video>
      </div>

      <div v-if="lesson.content"
           class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-200 dark:border-gray-800"
           v-html="lesson.content" />
    </div>

    <!-- ── TEXT LESSON ────────────────────────────────────────── -->
    <div v-else-if="lesson.type === 'text'"
         class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
      <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300"
           v-html="lesson.content" />
    </div>

    <!-- ── FILE LESSON ────────────────────────────────────────── -->
    <div v-else-if="lesson.type === 'file'"
         class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
      <p class="text-sm text-gray-500 mb-4">{{ lesson.content }}</p>
      <div v-if="lesson.files?.length" class="space-y-2">
        <a v-for="file in lesson.files" :key="file.id"
           :href="`/student/learn/${enrollment.id}/files/${file.id}`"
           class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary/30 hover:bg-primary/5 transition-colors">
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ file.name }}</p>
            <p class="text-xs text-gray-400">{{ file.file_size }}</p>
          </div>
        </a>
      </div>
    </div>

    <!-- ── QUIZ LESSON ────────────────────────────────────────── -->
    <div v-else-if="lesson.type === 'quiz' && quiz">

      <!-- Quiz start screen -->
      <div v-if="!quizMode && !quizResult"
           class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ quiz.title }}</h2>
        <p v-if="quiz.instructions" class="text-sm text-gray-500 mb-4">{{ quiz.instructions }}</p>

        <div class="grid grid-cols-2 gap-3 mb-6 text-sm">
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
            <p class="text-xs text-gray-400 mb-0.5">Questions</p>
            <p class="font-bold text-gray-900 dark:text-white">{{ quiz.questions?.length }}</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
            <p class="text-xs text-gray-400 mb-0.5">Pass Mark</p>
            <p class="font-bold text-gray-900 dark:text-white">{{ quiz.pass_mark }}%</p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
            <p class="text-xs text-gray-400 mb-0.5">Time Limit</p>
            <p class="font-bold text-gray-900 dark:text-white">
              {{ quiz.time_limit_minutes ? quiz.time_limit_minutes + ' min' : 'No limit' }}
            </p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3">
            <p class="text-xs text-gray-400 mb-0.5">Attempts Left</p>
            <p class="font-bold text-gray-900 dark:text-white">
              {{ quiz.can_attempt_real ? quiz.attempts_left : '0' }}
            </p>
          </div>
        </div>

        <!-- Past attempts -->
        <div v-if="quiz.past_attempts?.length" class="mb-5">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Past Attempts</p>
          <div class="space-y-1.5">
            <div v-for="a in quiz.past_attempts" :key="a.id"
                 class="flex items-center justify-between px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm">
              <span class="text-gray-500">{{ a.completed_at }}</span>
              <span class="font-bold" :class="a.passed ? 'text-green-600' : 'text-red-500'">
                {{ a.score }}% {{ a.passed ? '✓ Passed' : '✗ Failed' }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex gap-3">
          <button v-if="quiz.can_attempt_real"
                  @click="startQuiz('real')"
                  class="flex-1 py-3 rounded-xl font-semibold text-sm text-white"
                  style="background-color: var(--color-primary);">
            Start Real Attempt ({{ quiz.attempts_left }} left)
          </button>
          <button v-else-if="quiz.cant_attempt_reason"
                  disabled
                  class="flex-1 py-3 rounded-xl font-semibold text-sm bg-gray-100 text-gray-400 cursor-not-allowed">
            {{ quiz.cant_attempt_reason }}
          </button>
          <button v-if="quiz.allow_practice && quiz.can_attempt_practice"
                  @click="startQuiz('practice')"
                  class="flex-1 py-3 rounded-xl font-semibold text-sm border-2 border-gray-200 text-gray-600 hover:border-gray-300 transition-colors">
            Practice Mode
          </button>
        </div>
      </div>

      <!-- Quiz questions -->
      <div v-else-if="quizMode && !quizResult"
           class="space-y-4">
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-xl px-4 py-3 border border-gray-200 dark:border-gray-800">
          <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            {{ quizMode === 'practice' ? '🔵 Practice Mode' : '📝 Real Attempt' }}
          </span>
          <span class="text-xs text-gray-400">
            {{ Object.keys(answers).length }}/{{ quiz.questions?.length }} answered
          </span>
        </div>

        <div v-for="(q, qi) in quiz.questions" :key="q.id"
             class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
          <p class="text-sm font-semibold text-gray-900 dark:text-white mb-4">
            {{ qi + 1 }}. {{ q.question }}
          </p>

          <!-- Options -->
          <div class="space-y-2">
            <template v-if="q.type === 'true_false'">
              <button v-for="(label, val) in { true: 'True', false: 'False' }" :key="val"
                      @click="selectAnswer(q.id, val)"
                      class="w-full text-left px-4 py-3 rounded-xl border-2 text-sm transition-all"
                      :class="answers[q.id] === val
                        ? 'border-primary bg-primary/10 font-semibold'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                {{ label }}
              </button>
            </template>

            <template v-else>
              <button v-for="(option, idx) in q.options" :key="idx"
                      @click="selectAnswer(q.id, String(idx))"
                      class="w-full text-left px-4 py-3 rounded-xl border-2 text-sm transition-all"
                      :class="answers[q.id] === String(idx)
                        ? 'border-primary bg-primary/10 font-semibold'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                {{ option }}
              </button>
            </template>
          </div>
        </div>

        <button @click="submitQuiz"
                :disabled="!allAnswered || submitting"
                class="w-full py-3.5 rounded-xl font-semibold text-sm text-white disabled:opacity-50 transition-opacity"
                style="background-color: var(--color-primary);">
          {{ submitting ? 'Submitting…' : 'Submit Quiz' }}
        </button>
      </div>

      <!-- Quiz results -->
      <div v-else-if="quizResult" class="space-y-4">
        <!-- Score card -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6 text-center"
             :class="quizResult.attempt.passed
               ? 'border-green-200 dark:border-green-800'
               : 'border-red-200 dark:border-red-800'">
          <div class="text-5xl font-bold mb-2"
               :class="quizResult.attempt.passed ? 'text-green-600' : 'text-red-500'">
            {{ quizResult.attempt.score }}%
          </div>
          <p class="text-lg font-semibold"
             :class="quizResult.attempt.passed ? 'text-green-700' : 'text-red-600'">
            {{ quizResult.attempt.passed ? '🎉 Passed!' : '✗ Not Passed' }}
          </p>
          <p class="text-sm text-gray-500 mt-1">
            {{ quizResult.attempt.marks_earned }}/{{ quizResult.attempt.marks_total }} marks
            · Pass mark: {{ quizResult.attempt.pass_mark }}%
          </p>
          <p v-if="quizResult.attempt.is_practice"
             class="text-xs text-blue-600 mt-2 font-medium">Practice attempt — not counted</p>
        </div>

        <!-- Question results -->
        <div v-if="quizResult.results?.length" class="space-y-3">
          <div v-for="(r, i) in quizResult.results" :key="i"
               class="bg-white dark:bg-gray-900 rounded-xl border p-4"
               :class="r.is_correct
                 ? 'border-green-200 dark:border-green-800/50'
                 : 'border-red-200 dark:border-red-800/50'">
            <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">
              {{ i + 1 }}. {{ r.question }}
            </p>
            <div class="text-xs space-y-1">
              <p>
                Your answer:
                <span :class="r.is_correct ? 'text-green-600 font-semibold' : 'text-red-500 font-semibold'">
                  {{ Array.isArray(r.options)
                    ? r.options[parseInt(r.given_answer)] ?? r.given_answer
                    : r.given_answer === 'true' ? 'True' : 'False' }}
                  {{ r.is_correct ? '✓' : '✗' }}
                </span>
              </p>
              <p v-if="r.correct_answer && !r.is_correct" class="text-green-600">
                Correct:
                {{ Array.isArray(r.options)
                  ? r.options[parseInt(r.correct_answer)] ?? r.correct_answer
                  : r.correct_answer === 'true' ? 'True' : 'False' }}
              </p>
              <p v-if="r.explanation" class="text-gray-500 italic mt-1">{{ r.explanation }}</p>
            </div>
          </div>
        </div>

        <div class="flex gap-3">
          <button @click="retryQuiz"
                  class="flex-1 py-3 rounded-xl font-semibold text-sm border-2 border-gray-200 text-gray-600 hover:border-gray-300 transition-colors">
            Try Again
          </button>
          <a :href="`/student/learn/${enrollment.id}`"
             class="flex-1 py-3 rounded-xl font-semibold text-sm text-white text-center"
             style="background-color: var(--color-primary);">
            Back to Course
          </a>
        </div>
      </div>
    </div>

    <!-- ── Files section (non-quiz lessons) ──────────────────── -->
    <div v-if="lesson.type !== 'quiz' && lesson.files?.length" class="mt-4">
      <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Attachments</p>
      <div class="space-y-2">
        <a v-for="file in lesson.files" :key="file.id"
           :href="`/student/learn/${enrollment.id}/files/${file.id}`"
           class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 hover:border-primary/30 transition-colors text-sm">
          📎 {{ file.name }}
          <span class="text-xs text-gray-400 ml-auto">{{ file.file_size }}</span>
        </a>
      </div>
    </div>

    <!-- ── Complete button (non-quiz) ────────────────────────── -->
    <div v-if="lesson.type !== 'quiz'" class="mt-6">
      <button v-if="!completed"
              @click="markComplete"
              class="w-full py-3.5 rounded-xl font-semibold text-sm text-white"
              style="background-color: var(--color-primary);">
        Mark as Complete ✓
      </button>
      <div v-else
           class="flex items-center justify-between bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-4 py-3">
        <span class="text-sm font-semibold text-green-700 dark:text-green-400">
          ✓ Lesson completed
        </span>
        <a :href="`/student/learn/${enrollment.id}`"
           class="text-sm text-primary hover:underline font-medium">
          Back to course →
        </a>
      </div>
    </div>
  </div>
</template>
