<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import StudentLayout from '@shared/layouts/StudentLayout.vue'
defineOptions({ layout: StudentLayout })

const props = defineProps({
  enrollment:  { type: Object, required: true },
  assignment:  { type: Object, required: true },
  submission:  { type: Object, default: null },
})

const form = useForm({
  notes: props.submission?.notes ?? '',
  file:  null,
})

const fileInput = ref(null)

function submit() {
  const fd = new FormData()
  fd.append('notes', form.notes)
  if (fileInput.value?.files[0]) fd.append('file', fileInput.value.files[0])
  router.post(
    `/student/courses/${props.enrollment.id}/assignments/${props.assignment.id}`,
    fd,
    { forceFormData: true }
  )
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-5">
      <a :href="`/student/courses/${enrollment.id}`"
         class="text-sm text-blue-600 hover:underline">← {{ enrollment.course_title }}</a>
      <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2">{{ assignment.title }}</h1>
      <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
        <span v-if="assignment.due_date">Due {{ assignment.due_date }}</span>
        <span>Max {{ assignment.max_marks }} marks</span>
        <span v-if="assignment.is_required" class="text-red-400">Required</span>
      </div>
    </div>

    <!-- Description -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-6 mb-5 prose prose-sm dark:prose-invert max-w-none"
         v-html="assignment.description" />

    <!-- Previous submission result -->
    <div v-if="submission?.graded_at"
         class="bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800 rounded-xl p-5 mb-5">
      <h2 class="text-sm font-semibold text-green-700 dark:text-green-400 mb-2">Grade Received</h2>
      <p class="text-2xl font-bold text-green-700 dark:text-green-400 mb-1">
        {{ submission.grade }} / {{ assignment.max_marks }}
      </p>
      <p v-if="submission.feedback" class="text-sm text-green-700 dark:text-green-400">
        Feedback: {{ submission.feedback }}
      </p>
    </div>

    <!-- Submission status -->
    <div v-if="submission && !submission.graded_at"
         class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-100 dark:border-yellow-800 rounded-xl p-4 mb-5">
      <p class="text-sm text-yellow-700 dark:text-yellow-400">
        ✓ Submitted {{ submission.submitted_at }} — awaiting grading.
      </p>
    </div>

    <!-- Submit / resubmit form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-6">
      <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">
        {{ submission ? 'Update Submission' : 'Submit Assignment' }}
      </h2>
      <div class="space-y-4">
        <div>
          <label class="text-xs font-medium text-gray-500 mb-1 block">Notes (optional)</label>
          <textarea v-model="form.notes" rows="4"
                    placeholder="Any notes or comments for your teacher…"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 resize-none" />
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500 mb-1 block">
            File {{ submission?.file_name ? `(current: ${submission.file_name})` : '' }}
          </label>
          <input ref="fileInput" type="file"
                 class="text-sm text-gray-500" />
        </div>
        <div class="flex justify-end">
          <button @click="submit"
                  :disabled="form.processing"
                  class="px-6 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors">
            {{ form.processing ? 'Submitting…' : (submission ? 'Update Submission' : 'Submit') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
