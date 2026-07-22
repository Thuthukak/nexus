<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import StudentLayout from '@shared/layouts/StudentLayout.vue'
defineOptions({ layout: StudentLayout })

const props = defineProps({
  enrollment: { type: Object, required: true },
  lesson:     { type: Object, required: true },
  quiz:       { type: Object, required: true },
})

const answers    = reactive({})
const submitting = ref(false)

const allAnswered = computed(() =>
  props.quiz.questions.every(q => answers[q.id] !== undefined)
)

function submit() {
  if (!allAnswered.value) return
  submitting.value = true
  router.post(
    `/student/courses/${props.enrollment.id}/lessons/${props.lesson.id}/quiz`,
    { answers, is_practice: props.quiz.is_practice },
    { onFinish: () => { submitting.value = false } }
  )
}
</script>

<template>
  <div>
    <div class="mb-5">
      <a :href="`/student/courses/${enrollment.id}/lessons/${lesson.id}`"
         class="text-sm text-blue-600 hover:underline">← {{ lesson.title }}</a>
      <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2">{{ quiz.title }}</h1>
      <p v-if="quiz.is_practice"
         class="text-xs text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20 px-3 py-1.5 rounded-lg inline-block mt-2">
        Practice Mode — results won't count
      </p>
    </div>

    <p v-if="quiz.instructions"
       class="text-sm text-gray-600 dark:text-gray-400 mb-5">{{ quiz.instructions }}</p>

    <div class="space-y-5">
      <div v-for="(q, idx) in quiz.questions" :key="q.id"
           class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
          {{ idx + 1 }}. {{ q.question }}
          <span class="text-xs font-normal text-gray-400 ml-1">({{ q.marks }} mark{{ q.marks > 1 ? 's' : '' }})</span>
        </p>

        <!-- Multiple choice -->
        <div v-if="q.type === 'multiple_choice'" class="space-y-2">
          <label v-for="opt in q.options" :key="opt.key ?? opt"
                 class="flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all"
                 :class="answers[q.id] === (opt.label ?? opt)
                   ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                   : 'border-gray-100 dark:border-gray-800 hover:border-gray-200'">
            <input type="radio" :name="q.id" :value="opt.label ?? opt"
                   v-model="answers[q.id]"
                   class="text-blue-600 focus:ring-blue-500" />
            <span class="text-sm text-gray-800 dark:text-gray-200">{{ opt.label ?? opt }}</span>
          </label>
        </div>

        <!-- True/False -->
        <div v-else class="flex gap-4">
          <label v-for="opt in [{ k: 'true', l: 'True' }, { k: 'false', l: 'False' }]" :key="opt.k"
                 class="flex-1 flex items-center justify-center gap-2 p-3 rounded-lg border-2 cursor-pointer transition-all"
                 :class="answers[q.id] === opt.k
                   ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                   : 'border-gray-100 dark:border-gray-800 hover:border-gray-200'">
            <input type="radio" :name="q.id" :value="opt.k"
                   v-model="answers[q.id]"
                   class="text-blue-600 focus:ring-blue-500" />
            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ opt.l }}</span>
          </label>
        </div>
      </div>
    </div>

    <div class="mt-6 flex items-center justify-between">
      <p class="text-xs text-gray-400">
        {{ Object.keys(answers).length }} / {{ quiz.questions.length }} answered
      </p>
      <button @click="submit"
              :disabled="!allAnswered || submitting"
              class="px-6 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
        {{ submitting ? 'Submitting…' : 'Submit Quiz' }}
      </button>
    </div>
  </div>
</template>
