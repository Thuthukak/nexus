<script setup>
import { ref }     from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout   from '@shared/layouts/AppLayout.vue'
import Badge       from '@shared/components/display/Badge.vue'
import Button      from '@shared/components/buttons/Button.vue'
import Modal       from '@shared/components/feedback/Modal.vue'
import Input       from '@shared/components/form/Input.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({ submissions: { type: Array, default: () => [] } })

const showGrade   = ref(false)
const grading     = ref(null)
const gradeForm   = useForm({ grade: '', feedback: '' })

function openGrade(sub) {
  grading.value        = sub
  gradeForm.grade      = sub.grade ?? ''
  gradeForm.feedback   = sub.feedback ?? ''
  showGrade.value      = true
}

function submitGrade() {
  gradeForm.patch(`/lms/assignments/submissions/${grading.value.id}/grade`, {
    onSuccess: () => { showGrade.value = false; grading.value = null }
  })
}

const pending = props.submissions.filter(s => !s.graded_at)
const graded  = props.submissions.filter(s =>  s.graded_at)
</script>

<template>
  <div class="max-w-5xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">Assignment Submissions</h1>
      <p class="text-sm text-app-text/60 mt-1">
        {{ pending.length }} pending · {{ graded.length }} graded
      </p>
    </div>

    <div v-if="!submissions.length"
         class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-6 py-14 text-center text-sm text-app-text/40">
      No submissions yet.
    </div>

    <div v-else class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
      <div class="divide-y divide-gray-50 dark:divide-gray-800">
        <div v-for="sub in submissions" :key="sub.id"
             class="flex items-center justify-between px-5 py-4 gap-4">
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-app-text">{{ sub.student_name }}</p>
            <p class="text-xs text-app-text/50 truncate">
              {{ sub.course_title }} · {{ sub.cohort_name }} · {{ sub.assignment }}
            </p>
            <p class="text-xs text-app-text/40 mt-0.5">Submitted {{ sub.submitted_at }}</p>
            <p v-if="sub.notes" class="text-xs text-app-text/60 mt-1 italic">{{ sub.notes }}</p>
          </div>

          <div class="flex items-center gap-3 flex-shrink-0">
            <a v-if="sub.file_name"
               :href="`/lms/assignments/submissions/${sub.id}/download`"
               class="text-xs text-primary hover:underline">
              ↓ {{ sub.file_name }}
            </a>

            <div v-if="sub.graded_at" class="text-right">
              <p class="text-sm font-semibold text-app-text">{{ sub.grade }} / {{ sub.max_marks }}</p>
              <p class="text-xs text-app-text/40">Graded {{ sub.graded_at }}</p>
            </div>
            <Badge v-else type="warning">Pending</Badge>

            <Button size="sm" @click="openGrade(sub)">
              {{ sub.graded_at ? 'Re-grade' : 'Grade' }}
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Grade modal -->
    <Modal :show="showGrade" title="Grade Submission" @close="showGrade = false">
      <div v-if="grading" class="space-y-4">
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg px-4 py-3 text-sm">
          <p class="font-medium text-app-text">{{ grading.student_name }}</p>
          <p class="text-app-text/60">{{ grading.assignment }}</p>
          <p v-if="grading.notes" class="text-app-text/60 mt-1 italic text-xs">{{ grading.notes }}</p>
        </div>
        <Input v-model.number="gradeForm.grade"
               :label="`Grade (out of ${grading.max_marks})`"
               type="number" min="0" :max="grading.max_marks"
               required :error="gradeForm.errors.grade" />
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Feedback (optional)</label>
          <textarea v-model="gradeForm.feedback" rows="3"
                    placeholder="Feedback visible to the student…"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-sm text-app-text focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>
      </div>
      <template #footer>
        <button @click="showGrade = false" class="px-4 py-2 text-sm text-app-text/60">Cancel</button>
        <Button @click="submitGrade" :loading="gradeForm.processing">Save Grade</Button>
      </template>
    </Modal>
  </div>
</template>
