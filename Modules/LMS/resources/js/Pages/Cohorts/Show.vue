<script setup>
import { ref }         from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout       from '@shared/layouts/AppLayout.vue'
import Badge           from '@shared/components/display/Badge.vue'
import Button          from '@shared/components/buttons/Button.vue'
import Modal           from '@shared/components/feedback/Modal.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  course:    { type: Object, required: true },
  cohort:    { type: Object, required: true },
  students:  { type: Array,  default: () => [] },
  available: { type: Array,  default: () => [] },
})

const showEnroll = ref(false)
const selected   = ref([])
const enrollForm = useForm({ student_ids: [] })

function toggleStudent(id) {
  const idx = selected.value.indexOf(id)
  if (idx === -1) selected.value.push(id)
  else selected.value.splice(idx, 1)
}

function enroll() {
  enrollForm.student_ids = selected.value
  enrollForm.post(
    `/lms/courses/${props.course.id}/cohorts/${props.cohort.id}/enroll`,
    {
      onSuccess: () => {
        showEnroll.value = false
        selected.value   = []
      },
    }
  )
}

function withdraw(enrollmentId) {
  router.delete(
    `/lms/courses/${props.course.id}/cohorts/${props.cohort.id}/enrollments/${enrollmentId}`
  )
}

const statusColour = {
  active:    'success',
  completed: 'info',
  withdrawn: 'neutral',
  suspended: 'danger',
}
</script>

<template>
  <div class="max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <a :href="`/lms/courses/${course.id}/cohorts`"
           class="text-sm text-primary hover:underline">← Cohorts</a>
        <h1 class="text-2xl font-bold text-app-text mt-2">{{ cohort.name }}</h1>
        <p class="text-sm text-app-text/60 mt-1">
          {{ course.title }} ·
          {{ cohort.start_date }}
          <span v-if="cohort.end_date"> – {{ cohort.end_date }}</span>
        </p>
      </div>
      <div class="flex gap-2">
        <Button size="sm" @click="showEnroll = true">Enroll Students</Button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Enrolled</p>
        <p class="text-2xl font-bold text-app-text">{{ students.length }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Completed</p>
        <p class="text-2xl font-bold text-green-600">
          {{ students.filter(s => s.status === 'completed').length }}
        </p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Avg Progress</p>
        <p class="text-2xl font-bold text-app-text">
          {{ students.length
            ? Math.round(students.reduce((s, st) => s + st.progress, 0) / students.length)
            : 0 }}%
        </p>
      </div>
    </div>

    <!-- Student list -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-sm font-semibold text-app-text">Students</h2>
      </div>
      <div v-if="!students.length"
           class="px-5 py-10 text-center text-sm text-app-text/40">
        No students enrolled yet.
      </div>
      <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
        <div v-for="s in students" :key="s.enrollment_id"
             class="flex items-center justify-between px-5 py-3">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
              <span class="text-white text-xs font-semibold">
                {{ s.student_name?.charAt(0)?.toUpperCase() }}
              </span>
            </div>
            <div class="min-w-0">
              <p class="text-sm font-medium text-app-text truncate">{{ s.student_name }}</p>
              <p class="text-xs text-app-text/40 truncate">{{ s.student_email }}</p>
            </div>
          </div>

          <div class="flex items-center gap-4 flex-shrink-0">
            <!-- Progress bar -->
            <div class="hidden sm:block w-28">
              <div class="flex items-center justify-between text-xs text-app-text/50 mb-1">
                <span>Progress</span>
                <span>{{ s.progress }}%</span>
              </div>
              <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full transition-all"
                     :style="{ width: s.progress + '%' }" />
              </div>
            </div>

            <Badge :type="statusColour[s.status] ?? 'neutral'">{{ s.status }}</Badge>

            <div class="flex items-center gap-1">
              <a :href="`/lms/courses/${course.id}/report/student/${s.enrollment_id}`"
                 class="text-xs text-app-text/40 hover:text-primary transition-colors px-2 py-1">
                Report
              </a>
              <button v-if="s.status === 'active'"
                      @click="withdraw(s.enrollment_id)"
                      class="text-xs text-app-text/40 hover:text-red-500 transition-colors px-2 py-1">
                Withdraw
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Enroll modal -->
    <Modal :show="showEnroll" title="Enroll Students" size="lg" @close="showEnroll = false">
      <div class="space-y-3">
        <p class="text-sm text-app-text/60">
          Select students to enroll in <strong>{{ cohort.name }}</strong>.
        </p>
        <div v-if="!available.length" class="text-sm text-app-text/40 py-4 text-center">
          All available students are already enrolled.
        </div>
        <div v-else class="max-h-80 overflow-y-auto space-y-1">
          <label v-for="student in available" :key="student.id"
                 class="flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer transition-colors"
                 :class="selected.includes(student.id)
                   ? 'bg-primary/10 border border-primary/20'
                   : 'hover:bg-gray-50 dark:hover:bg-gray-800 border border-transparent'">
            <input :value="student.id"
                   :checked="selected.includes(student.id)"
                   @change="toggleStudent(student.id)"
                   type="checkbox"
                   class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
            <div>
              <p class="text-sm font-medium text-app-text">{{ student.name }}</p>
              <p class="text-xs text-app-text/50">{{ student.email }}</p>
            </div>
          </label>
        </div>
        <p v-if="selected.length" class="text-xs text-primary font-medium">
          {{ selected.length }} student(s) selected
        </p>
      </div>
      <template #footer>
        <button @click="showEnroll = false; selected = []"
                class="px-4 py-2 text-sm text-app-text/60">Cancel</button>
        <Button @click="enroll"
                :disabled="!selected.length"
                :loading="enrollForm.processing">
          Enroll Selected
        </Button>
      </template>
    </Modal>
  </div>
</template>
