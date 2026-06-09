<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout  from '@shared/layouts/AppLayout.vue'
import Input      from '@shared/components/form/Input.vue'
import Button     from '@shared/components/buttons/Button.vue'
import Modal      from '@shared/components/feedback/Modal.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  course:  { type: Object, required: true },
  section: { type: Object, required: true },
  lesson:  { type: Object, required: true },
})

// ── Lesson form ───────────────────────────────────────────────
const lessonForm = useForm({
  title:            props.lesson.title,
  content:          props.lesson.content ?? '',
  video_url:        props.lesson.video_url ?? '',
  duration_minutes: props.lesson.duration_minutes ?? '',
  is_free_preview:  props.lesson.is_free_preview ?? false,
})

function saveLesson() {
  lessonForm.patch(
    `/lms/courses/${props.course.id}/sections/${props.section.id}/lessons/${props.lesson.id}`
  )
}

// ── File upload ───────────────────────────────────────────────
const fileInput   = ref(null)
const fileName    = ref('')
const fileUpForm  = useForm({ file: null, name: '' })

function onFileChange(e) {
  const f = e.target.files[0]
  if (!f) return
  fileUpForm.file = f
  fileName.value  = f.name
  if (!fileUpForm.name) fileUpForm.name = f.name
}

function uploadFile() {
  fileUpForm.post(
    `/lms/courses/${props.course.id}/sections/${props.section.id}/lessons/${props.lesson.id}/files`,
    { forceFormData: true, onSuccess: () => { fileUpForm.reset(); fileName.value = '' } }
  )
}

function deleteFile(fileId) {
  router.delete(
    `/lms/courses/${props.course.id}/sections/${props.section.id}/lessons/${props.lesson.id}/files/${fileId}`
  )
}

// ── Quiz settings ─────────────────────────────────────────────
const quizForm = useForm({
  title:               props.lesson.quiz?.title ?? '',
  instructions:        props.lesson.quiz?.instructions ?? '',
  pass_mark:           props.lesson.quiz?.pass_mark ?? 70,
  max_attempts:        props.lesson.quiz?.max_attempts ?? 3,
  allow_practice:      props.lesson.quiz?.allow_practice ?? true,
  time_limit_minutes:  props.lesson.quiz?.time_limit_minutes ?? '',
  show_answers_after:  props.lesson.quiz?.show_answers_after ?? true,
  randomise_questions: props.lesson.quiz?.randomise_questions ?? false,
})

function saveQuiz() {
  quizForm.patch(
    `/lms/courses/${props.course.id}/sections/${props.section.id}/lessons/${props.lesson.id}/quiz`
  )
}

// ── Question management ───────────────────────────────────────
const showQuestionModal = ref(false)
const editingQuestion   = ref(null)

const qForm = useForm({
  question:       '',
  type:           'multiple_choice',
  options:        ['', '', '', ''],
  correct_answer: '',
  explanation:    '',
  marks:          1,
})

const tfOptions = ['true', 'false']

function openAddQuestion() {
  editingQuestion.value = null
  qForm.reset()
  qForm.options = ['', '', '', '']
  qForm.type    = 'multiple_choice'
  showQuestionModal.value = true
}

function openEditQuestion(q) {
  editingQuestion.value   = q
  qForm.question          = q.question
  qForm.type              = q.type
  qForm.options           = q.type === 'multiple_choice' ? [...q.options] : ['', '']
  qForm.correct_answer    = q.correct_answer
  qForm.explanation       = q.explanation ?? ''
  qForm.marks             = q.marks
  showQuestionModal.value = true
}

function saveQuestion() {
  const url = editingQuestion.value
    ? `/lms/courses/${props.course.id}/sections/${props.section.id}/lessons/${props.lesson.id}/questions/${editingQuestion.value.id}`
    : `/lms/courses/${props.course.id}/sections/${props.section.id}/lessons/${props.lesson.id}/questions`

  const options = {
    onSuccess: () => {
      showQuestionModal.value = false
      qForm.reset()
    },
  }

  if (editingQuestion.value) {
    qForm.patch(url, options)
  } else {
    qForm.post(url, options)
  }
}

function deleteQuestion(questionId) {
  router.delete(
    `/lms/courses/${props.course.id}/sections/${props.section.id}/lessons/${props.lesson.id}/questions/${questionId}`
  )
}

const isQuiz = computed(() => props.lesson.type === 'quiz')
const isVideo= computed(() => props.lesson.type === 'video')

const tabs = computed(() => {
  const t = [{ key: 'content', label: 'Content' }]
  if (isQuiz.value) {
    t.push({ key: 'quiz', label: 'Quiz Settings' })
    t.push({ key: 'questions', label: `Questions (${props.lesson.quiz?.questions?.length ?? 0})` })
  } else {
    t.push({ key: 'files', label: `Files (${props.lesson.files?.length ?? 0})` })
  }
  return t
})

const activeTab = ref('content')
</script>

<template>
  <div class="max-w-3xl">
    <!-- Header -->
    <div class="mb-6">
      <a :href="`/lms/courses/${course.id}/edit`"
         class="text-sm text-primary hover:underline">← {{ course.title }}</a>
      <p class="text-xs text-app-text/40 mt-0.5">{{ section.title }}</p>
      <div class="flex items-center justify-between mt-1">
        <h1 class="text-2xl font-bold text-app-text">{{ lesson.title }}</h1>
        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-app-text/60 capitalize">
          {{ lesson.type }}
        </span>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 bg-gray-100 dark:bg-gray-800 rounded-xl p-1 w-fit">
      <button v-for="tab in tabs" :key="tab.key"
              @click="activeTab = tab.key"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="activeTab === tab.key
                ? 'bg-surface text-app-text shadow-sm'
                : 'text-app-text/50 hover:text-app-text'">
        {{ tab.label }}
      </button>
    </div>

    <!-- ── CONTENT TAB ──────────────────────────────────────── -->
    <div v-if="activeTab === 'content'">
      <form @submit.prevent="saveLesson" class="space-y-4">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
          <Input v-model="lessonForm.title" label="Title" required :error="lessonForm.errors.title" />

          <div v-if="isVideo" class="flex flex-col gap-1">
            <Input v-model="lessonForm.video_url"
                   label="Video URL"
                   placeholder="https://www.youtube.com/watch?v=..."
                   hint="YouTube or Vimeo URL"
                   :error="lessonForm.errors.video_url" />
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">
              {{ isVideo ? 'Notes / Description' : 'Content' }}
            </label>
            <textarea v-model="lessonForm.content" rows="8"
                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none font-mono" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <Input v-model.number="lessonForm.duration_minutes"
                   label="Duration (minutes)" type="number" min="0" />
            <div class="flex flex-col justify-end pb-1">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="lessonForm.is_free_preview" type="checkbox"
                       class="w-4 h-4 rounded border-gray-300 text-primary" />
                <span class="text-sm font-medium text-app-text">Free preview</span>
              </label>
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <Button type="submit" :loading="lessonForm.processing">Save Changes</Button>
        </div>
      </form>
    </div>

    <!-- ── FILES TAB ───────────────────────────────────────── -->
    <div v-if="activeTab === 'files'" class="space-y-4">
      <!-- Existing files -->
      <div v-if="lesson.files?.length" class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="file in lesson.files" :key="file.id"
               class="flex items-center justify-between px-5 py-3">
            <div>
              <p class="text-sm font-medium text-app-text">{{ file.name }}</p>
              <p class="text-xs text-app-text/40">{{ file.file_name }} · {{ file.file_size }}</p>
            </div>
            <button @click="deleteFile(file.id)"
                    class="text-xs text-red-400 hover:text-red-600 transition-colors px-2 py-1">
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Upload new file -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5 space-y-3">
        <h2 class="text-sm font-semibold text-app-text">Upload File</h2>
        <Input v-model="fileUpForm.name" label="Display Name" />
        <div class="flex items-center gap-3">
          <input ref="fileInput" type="file" class="hidden" @change="onFileChange" />
          <button type="button" @click="fileInput.click()"
                  class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-app-text/60 hover:text-app-text transition-colors">
            Choose File
          </button>
          <span class="text-sm text-app-text/50">{{ fileName || 'No file chosen' }}</span>
        </div>
        <Button size="sm" :loading="fileUpForm.processing"
                :disabled="!fileUpForm.file" @click="uploadFile">
          Upload
        </Button>
      </div>
    </div>

    <!-- ── QUIZ SETTINGS TAB ────────────────────────────────── -->
    <div v-if="activeTab === 'quiz'">
      <form @submit.prevent="saveQuiz" class="space-y-4">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
          <Input v-model="quizForm.title" label="Quiz Title" :error="quizForm.errors.title" />
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Instructions</label>
            <textarea v-model="quizForm.instructions" rows="2"
                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="flex flex-col gap-1">
              <label class="text-sm font-medium text-app-text">Pass Mark (%)</label>
              <input v-model.number="quizForm.pass_mark" type="number" min="1" max="100"
                     class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
            </div>
            <div class="flex flex-col gap-1">
              <label class="text-sm font-medium text-app-text">Max Real Attempts</label>
              <input v-model.number="quizForm.max_attempts" type="number" min="1" max="10"
                     class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
            </div>
            <div class="flex flex-col gap-1">
              <label class="text-sm font-medium text-app-text">Time Limit (minutes)</label>
              <input v-model.number="quizForm.time_limit_minutes" type="number" min="1"
                     placeholder="No limit"
                     class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
            </div>
          </div>
          <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="quizForm.allow_practice" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              <div>
                <span class="text-sm font-medium text-app-text">Allow practice mode</span>
                <p class="text-xs text-app-text/50">Students can attempt unlimited practice runs without affecting their score.</p>
              </div>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="quizForm.show_answers_after" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              <span class="text-sm font-medium text-app-text">Show correct answers after attempt</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="quizForm.randomise_questions" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              <span class="text-sm font-medium text-app-text">Randomise question order</span>
            </label>
          </div>
        </div>
        <div class="flex justify-end">
          <Button type="submit" :loading="quizForm.processing">Save Quiz Settings</Button>
        </div>
      </form>
    </div>

    <!-- ── QUESTIONS TAB ────────────────────────────────────── -->
    <div v-if="activeTab === 'questions'" class="space-y-4">
      <div class="flex items-center justify-between">
        <p class="text-sm text-app-text/60">
          {{ lesson.quiz?.questions?.length ?? 0 }} question(s) ·
          {{ lesson.quiz?.questions?.reduce((s, q) => s + q.marks, 0) ?? 0 }} total marks
        </p>
        <Button size="sm" @click="openAddQuestion">+ Add Question</Button>
      </div>

      <div v-if="!lesson.quiz?.questions?.length"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-6 py-10 text-center text-app-text/40 text-sm">
        No questions yet. Add your first question to build the quiz.
      </div>

      <div v-else class="space-y-3">
        <div v-for="(q, i) in lesson.quiz.questions" :key="q.id"
             class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="text-xs font-bold text-app-text/30">Q{{ i + 1 }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-app-text/50 capitalize">
                  {{ q.type === 'multiple_choice' ? 'MC' : 'T/F' }}
                </span>
                <span class="text-xs text-app-text/40">{{ q.marks }} mark(s)</span>
              </div>
              <p class="text-sm font-medium text-app-text mb-2">{{ q.question }}</p>
              <div class="flex flex-wrap gap-2">
                <span v-for="(opt, oi) in q.options" :key="oi"
                      class="text-xs px-2 py-1 rounded-lg"
                      :class="String(oi) === q.correct_answer || opt === q.correct_answer
                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 font-semibold'
                        : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'">
                  {{ opt }}
                  <span v-if="String(oi) === q.correct_answer || opt === q.correct_answer"> ✓</span>
                </span>
              </div>
              <p v-if="q.explanation" class="text-xs text-app-text/50 mt-1.5 italic">
                💡 {{ q.explanation }}
              </p>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="openEditQuestion(q)"
                      class="text-xs text-app-text/40 hover:text-primary px-2 py-1 transition-colors">
                Edit
              </button>
              <button @click="deleteQuestion(q.id)"
                      class="text-xs text-app-text/40 hover:text-red-500 px-2 py-1 transition-colors">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Question Modal -->
  <Modal :show="showQuestionModal"
         :title="editingQuestion ? 'Edit Question' : 'Add Question'"
         size="lg"
         @close="showQuestionModal = false">
    <div class="space-y-4">

      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Question Type</label>
        <div class="flex gap-3">
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="qForm.type" type="radio" value="multiple_choice"
                   class="w-4 h-4 text-primary" />
            <span class="text-sm text-app-text">Multiple Choice</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="qForm.type" type="radio" value="true_false"
                   class="w-4 h-4 text-primary" />
            <span class="text-sm text-app-text">True / False</span>
          </label>
        </div>
      </div>

      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Question <span class="text-red-500">*</span></label>
        <textarea v-model="qForm.question" rows="2"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        <p v-if="qForm.errors.question" class="text-xs text-red-500">{{ qForm.errors.question }}</p>
      </div>

      <!-- MC options -->
      <div v-if="qForm.type === 'multiple_choice'" class="space-y-2">
        <label class="text-sm font-medium text-app-text">Options <span class="text-app-text/40 text-xs">(mark the correct answer)</span></label>
        <div v-for="(opt, i) in qForm.options" :key="i" class="flex items-center gap-2">
          <label class="flex-shrink-0">
            <input :value="String(i)"
                   :checked="qForm.correct_answer === String(i)"
                   @change="qForm.correct_answer = String(i)"
                   type="radio"
                   class="w-4 h-4 text-primary" />
          </label>
          <input v-model="qForm.options[i]" type="text"
                 :placeholder="`Option ${i + 1}`"
                 class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
          <button v-if="qForm.options.length > 2"
                  type="button"
                  @click="qForm.options.splice(i, 1)"
                  class="text-red-400 hover:text-red-600 text-xs px-1">✕</button>
        </div>
        <button v-if="qForm.options.length < 6"
                type="button"
                @click="qForm.options.push('')"
                class="text-xs text-primary hover:underline">+ Add option</button>
      </div>

      <!-- T/F options -->
      <div v-else class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Correct Answer</label>
        <div class="flex gap-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="qForm.correct_answer" type="radio" value="true"
                   class="w-4 h-4 text-primary" />
            <span class="text-sm text-app-text">True</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input v-model="qForm.correct_answer" type="radio" value="false"
                   class="w-4 h-4 text-primary" />
            <span class="text-sm text-app-text">False</span>
          </label>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Marks</label>
          <input v-model.number="qForm.marks" type="number" min="1"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">
            Explanation <span class="text-app-text/40 font-normal">(optional)</span>
          </label>
          <input v-model="qForm.explanation" type="text"
                 placeholder="Shown after attempt"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
      </div>
    </div>

    <template #footer>
      <button @click="showQuestionModal = false"
              class="px-4 py-2 text-sm text-app-text/60">Cancel</button>
      <Button @click="saveQuestion" :loading="qForm.processing">
        {{ editingQuestion ? 'Save Changes' : 'Add Question' }}
      </Button>
    </template>
  </Modal>
</template>
