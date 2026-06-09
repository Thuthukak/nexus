<script setup>
import { router }    from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import Badge         from '@shared/components/display/Badge.vue'
import ConfirmDialog from '@shared/components/feedback/ConfirmDialog.vue'
import { ref }       from 'vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  courses: { type: Array, default: () => [] },
})

const confirmDelete = ref(false)
const deletingId    = ref(null)

function promptDelete(id) {
  deletingId.value    = id
  confirmDelete.value = true
}

function handleDelete() {
  router.delete(`/lms/courses/${deletingId.value}`, {}, {
    onFinish: () => { confirmDelete.value = false; deletingId.value = null },
  })
}

const statusColour = {
  draft:     'neutral',
  published: 'success',
  archived:  'neutral',
}

const difficultyColour = {
  beginner:     'bg-green-100 text-green-700',
  intermediate: 'bg-yellow-100 text-yellow-700',
  advanced:     'bg-red-100 text-red-700',
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Courses</h1>
        <p class="text-sm text-app-text/60 mt-1">{{ courses.length }} course(s)</p>
      </div>
      <a href="/lms/courses/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Course
      </a>
    </div>

    <div v-if="!courses.length"
         class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-6 py-16 text-center text-app-text/40 text-sm">
      No courses yet. Create your first course to get started.
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="course in courses" :key="course.id"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col">
        <!-- Thumbnail -->
        <div class="h-36 bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center flex-shrink-0">
          <svg class="w-12 h-12 text-primary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>

        <div class="p-5 flex flex-col flex-1">
          <div class="flex items-start justify-between gap-2 mb-2">
            <h2 class="font-semibold text-app-text text-base leading-tight">{{ course.title }}</h2>
            <Badge :type="statusColour[course.status]">{{ course.status }}</Badge>
          </div>

          <div class="flex items-center gap-2 mb-3 flex-wrap">
            <span class="text-xs px-2 py-0.5 rounded-full capitalize font-medium"
                  :class="difficultyColour[course.difficulty] ?? 'bg-gray-100 text-gray-600'">
              {{ course.difficulty }}
            </span>
            <span v-if="course.category" class="text-xs text-app-text/40">{{ course.category }}</span>
          </div>

          <div class="flex items-center gap-4 text-xs text-app-text/50 mb-4">
            <span>{{ course.sections_count }} section(s)</span>
            <span>{{ course.cohorts_count }} cohort(s)</span>
            <span v-if="course.estimated_hours">~{{ course.estimated_hours }}h</span>
          </div>

          <div class="flex items-center gap-2 mt-auto pt-3 border-t border-gray-100 dark:border-gray-800">
            <a :href="`/lms/courses/${course.id}/edit`"
               class="flex-1 text-center px-3 py-1.5 text-xs font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors">
              Edit
            </a>
            <a :href="`/lms/courses/${course.id}/cohorts`"
               class="flex-1 text-center px-3 py-1.5 text-xs font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors">
              Cohorts
            </a>
            <a :href="`/lms/courses/${course.id}/report`"
               class="flex-1 text-center px-3 py-1.5 text-xs font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors">
              Report
            </a>
            <button @click="promptDelete(course.id)"
                    class="px-2 py-1.5 text-xs text-red-400 hover:text-red-600 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <ConfirmDialog :show="confirmDelete" title="Delete Course"
      message="This course and all its content will be permanently deleted."
      confirm-label="Delete" danger
      @confirm="handleDelete" @cancel="confirmDelete = false" />
  </div>
</template>
