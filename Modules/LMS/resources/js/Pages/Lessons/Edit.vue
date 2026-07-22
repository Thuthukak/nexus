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

const lessonForm = useForm({
  title:            props.lesson.title,
  content:          props.lesson.content ?? '',
  video_url:        props.lesson.video_url ?? '',
  duration_minutes: props.lesson.duration_minutes ?? 0,
  is_free_preview:  props.lesson.is_free_preview,
})

function saveLesson() {
  lessonForm.patch(
    `/lms/courses/${props.course.id}/sections/${props.section.id}/lessons/${props.lesson.id}`
  )
}

// ── Quiz settings ─────────────────────────────────────────────
const hasQuiz = computed(() => !!props.lesson.quiz)
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

// ── Questions ─────────────────────────────────────────────────
const showAddQuestion = ref(false)
const editingQuestion  = ref(null)
const questionForm     = useForm({
  question:       '',
  type:           'multiple_choice',
  options:        ['', '', '', ''],
  correct_answer: '',
  explanation:    '',
  marks:          1,
})

function openAddQuestion() {
  questionForm.reset()
  questionForm.options = ['', '', '', '']
  editingQuestion.value = null
  showAddQuestion.value = true
}

function editQuestion(q) {
  questionForm.question       = q.question
  questionForm.type           = q.type
  questionForm.options        = q.type === 'multiple_choice' ? [...q.options] : ['', '']
  questionForm.correct_answer = q.correct_answer
  questionForm.explanation    = q.explanation ?? ''
  questionForm.marks          = q.marks
  editingQuestion.value = q
  showAddQuestion.value = true
}

const baseUrl = computed(() =>
  `/lms/courses/${props.course.id}/sections/${props.section.id}/lessons/${props.lesson.id}`
)

function submitQuestion() {
  if (editingQuestion.value) {
    questionForm.patch(
      `${baseUrl.value}/questions/${editingQuestion.value.id}`,
      { onSuccess: () => { showAddQuestion.value = false; editingQuestion.value = null } }
    )
  } else {
    questionForm.post(`${baseUrl.value}/questions`, {
      onSuccess: () => { showAddQuestion.value = false }
    })
  }
}

function deleteQuestion(qId) {
  if (!confirm('Delete this question?')) return
  router.delete(`${baseUrl.value}/questions/${qId}`)
}

// ── Files ─────────────────────────────────────────────────────
const uploadingFile = ref(false)
const fileInput     = ref(null)
const fileName      = ref('')

async function uploadFile() {
  if (!fileInput.value?.files[0]) return
  uploadingFile.value = true
  const fd = new FormData()
  fd.append('file', fileInput.value.files[0])
  fd.append('name', fileName.value || fileInput.value.files[0].name)
  router.post(`${baseUrl.value}/files`, fd, {
    onFinish: () => { uploadingFile.value = false; fileName.value = '' }
  })
}

function deleteFile(fileId) {
  if (!confirm('Delete this file?')) return
  router.delete(`${baseUrl.value}/files/${fileId}`)
}
</script>

<template>
  <div class="max-w-3xl space-y-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <a :href="`/lms/courses/${course.id}/edit`"
           class="text-sm text-primary hover:underline">← {{ course.title }}</a>
        <h1 class="text-2xl font-bold text-app-text mt-2">{{ lesson.title }}</h1>
        <p class="text-sm text-app-text/60 mt-1">{{ section.title }} · {{ lesson.type }}</p>
      </div>
    </div>

    <!-- Lesson settings -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
      <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Lesson Settings</h2>
      <form @submit.prevent="saveLesson" class="space-y-4">
        <Input v-model="lessonForm.title" label="Title" required :error="lessonForm.errors.title" />

        <div v-if="lesson.type === 'video'" class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Video URL</label>
          <input v-model="lessonForm.video_url" type="url"
                 placeholder="https://youtube.com/watch?v=..."
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>

        <div v-if="['text','file'].includes(lesson.type)" class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Content</label>
          <textarea v-model="lessonForm.content" rows="8"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary/50 resize-y" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <Input v-model.number="lessonForm.duration_minutes" label="Duration (min)"
                 type="number" min="0" />
          <div class="flex items-end pb-1">
            <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
              <input v-model="lessonForm.is_free_preview" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              Free preview
            </label>
          </div>
        </div>
        <div class="flex justify-end">
          <Button type="submit" :loading="lessonForm.processing" size="sm">Save</Button>
        </div>
      </form>
    </div>

    <!-- Files (for file/text lessons) -->
    <div v-if="['text','file'].includes(lesson.type)"
         class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
      <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Downloadable Files</h2>

      <div v-if="lesson.files?.length" class="divide-y divide-gray-50 dark:divide-gray-800 mb-4">
        <div v-for="f in lesson.files" :key="f.id"
             class="flex items-center justify-between py-2">
          <div>
            <p class="text-sm font-medium text-app-text">{{ f.name }}</p>
            <p class="text-xs text-app-text/40">{{ f.file_name }} · {{ f.file_size }}</p>
          </div>
          <button @click="deleteFile(f.id)"
                  class="text-xs text-red-400 hover:text-red-600 px-2 py-1">Delete</button>
        </div>
      </div>

      <div class="flex items-end gap-3">
        <div class="flex-1">
          <label class="text-xs text-app-text/50 mb-1 block">Display name (optional)</label>
          <input v-model="fileName" type="text" placeholder="e.g. Course Notes PDF"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-sm text-app-text focus:outline-none focus:ring-2 focus:ring-primary/50" />
        </div>
        <div>
          <label class="text-xs text-app-text/50 mb-1 block">File</label>
          <input ref="fileInput" type="file"
                 class="text-sm text-app-text/60" />
        </div>
        <Button @click="uploadFile" :loading="uploadingFile" size="sm">Upload</Button>
      </div>
    </div>

    <!-- Quiz settings (quiz type lessons only) -->
    <template v-if="lesson.type === 'quiz' && hasQuiz">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Quiz Settings</h2>
        <form @submit.prevent="saveQuiz" class="space-y-4">
          <Input v-model="quizForm.title" label="Quiz Title" required />
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Instructions (optional)</label>
            <textarea v-model="quizForm.instructions" rows="2"
                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-sm text-app-text focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <Input v-model.number="quizForm.pass_mark"          label="Pass Mark (%)" type="number" min="1" max="100" />
            <Input v-model.number="quizForm.max_attempts"       label="Max Attempts"  type="number" min="1" max="10" />
            <Input v-model.number="quizForm.time_limit_minutes" label="Time Limit (min, blank=unlimited)" type="number" min="1" />
          </div>
          <div class="flex items-center gap-6 flex-wrap">
            <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
              <input v-model="quizForm.allow_practice" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              Allow practice mode
            </label>
            <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
              <input v-model="quizForm.show_answers_after" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              Show answers after attempt
            </label>
            <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
              <input v-model="quizForm.randomise_questions" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              Randomise question order
            </label>
          </div>
          <div class="flex justify-end">
            <Button type="submit" :loading="quizForm.processing" size="sm">Save Quiz Settings</Button>
          </div>
        </form>
      </div>

      <!-- Questions -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Questions</h2>
          <Button size="sm" @click="openAddQuestion">+ Add Question</Button>
        </div>

        <div v-if="!lesson.quiz?.questions?.length"
             class="py-8 text-center text-sm text-app-text/40">
          No questions yet. Add questions to complete the quiz.
        </div>

        <div v-else class="space-y-3">
          <div v-for="(q, idx) in lesson.quiz.questions" :key="q.id"
               class="border border-gray-100 dark:border-gray-800 rounded-lg p-4">
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1">
                <p class="text-sm font-medium text-app-text">{{ idx + 1 }}. {{ q.question }}</p>
                <p class="text-xs text-app-text/40 mt-1">
                  {{ q.type === 'true_false' ? 'True/False' : 'Multiple choice' }}
                  · {{ q.marks }} mark(s)
                  · Answer: <span class="text-green-600 font-medium">{{ q.correct_answer }}</span>
                </p>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <button @click="editQuestion(q)"
                        class="text-xs text-app-text/40 hover:text-primary px-2 py-1 transition-colors">Edit</button>
                <button @click="deleteQuestion(q.id)"
                        class="text-xs text-app-text/40 hover:text-red-500 px-2 py-1 transition-colors">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Question modal -->
    <Modal :show="showAddQuestion"
           :title="editingQuestion ? 'Edit Question' : 'Add Question'"
           size="lg"
           @close="showAddQuestion = false">
      <form @submit.prevent="submitQuestion" class="space-y-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Question</label>
          <textarea v-model="questionForm.question" rows="2" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-sm text-app-text focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>

        <div class="flex gap-4">
          <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
            <input v-model="questionForm.type" type="radio" value="multiple_choice"
                   class="text-primary" /> Multiple Choice
          </label>
          <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
            <input v-model="questionForm.type" type="radio" value="true_false"
                   class="text-primary" /> True / False
          </label>
        </div>

        <div v-if="questionForm.type === 'multiple_choice'" class="space-y-2">
          <label class="text-sm font-medium text-app-text">Options</label>
          <div v-for="(_, i) in questionForm.options" :key="i" class="flex items-center gap-2">
            <input v-model="questionForm.options[i]" type="text"
                   :placeholder="`Option ${i + 1}`"
                   class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-sm text-app-text focus:outline-none focus:ring-2 focus:ring-primary/50" />
          </div>
          <div class="flex flex-col gap-1 mt-2">
            <label class="text-sm font-medium text-app-text">Correct Answer</label>
            <select v-model="questionForm.correct_answer"
                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-sm text-app-text focus:outline-none focus:ring-2 focus:ring-primary/50">
              <option value="">Select correct answer…</option>
              <option v-for="(opt, i) in questionForm.options.filter(o => o)" :key="i" :value="opt">
                {{ opt }}
              </option>
            </select>
          </div>
        </div>

        <div v-else class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Correct Answer</label>
          <select v-model="questionForm.correct_answer"
                  class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-sm text-app-text focus:outline-none focus:ring-2 focus:ring-primary/50">
            <option value="true">True</option>
            <option value="false">False</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <Input v-model.number="questionForm.marks" label="Marks" type="number" min="1" />
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Explanation (optional)</label>
            <input v-model="questionForm.explanation" type="text"
                   placeholder="Shown after attempt if enabled"
                   class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-sm text-app-text focus:outline-none focus:ring-2 focus:ring-primary/50" />
          </div>
        </div>
      </form>
      <template #footer>
        <button @click="showAddQuestion = false"
                class="px-4 py-2 text-sm text-app-text/60">Cancel</button>
        <Button @click="submitQuestion" :loading="questionForm.processing">
          {{ editingQuestion ? 'Save' : 'Add Question' }}
        </Button>
      </template>
    </Modal>
  </div>
</template>
