<script setup>
import { computed } from 'vue'
import StudentLayout from '@shared/layouts/StudentLayout.vue'
defineOptions({ layout: StudentLayout })

const props = defineProps({
  enrollment:        { type: Object, required: true },
  course:            { type: Object, required: true },
  cohort:            { type: Object, required: true },
  sections:          { type: Array, default: () => [] },
  assignments:       { type: Array, default: () => [] },
  completed_lessons: { type: Array, default: () => [] },
  has_certificate:   { type: Boolean, default: false },
})

const lessonTypeIcon = { video: '🎬', text: '📄', file: '📎', quiz: '❓' }

function isLocked(sectionIdx, lessonIdx) {
  if (!props.course.require_sequential) return false
  // First lesson of first section never locked
  if (sectionIdx === 0 && lessonIdx === 0) return false

  // Find previous lesson across sections
  const allLessons = props.sections.flatMap(s => s.lessons)
  const flat = props.sections.flatMap((s, si) =>
    s.lessons.map((l, li) => ({ ...l, si, li }))
  )
  const currentFlat = flat.find(l => l.si === sectionIdx && l.li === lessonIdx)
  const currentIdx  = flat.indexOf(currentFlat)
  if (currentIdx === 0) return false

  const prev = flat[currentIdx - 1]
  return !props.completed_lessons.includes(prev.id)
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <a href="/student/dashboard" class="text-sm text-blue-600 hover:underline">← My Courses</a>
      <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2">{{ course.title }}</h1>
      <p class="text-sm text-gray-500 mt-1">{{ cohort.name }}</p>
    </div>

    <!-- Progress -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 mb-5">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Progress</span>
        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ enrollment.progress }}%</span>
      </div>
      <div class="h-3 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
        <div class="h-full bg-blue-500 rounded-full transition-all"
             :style="{ width: enrollment.progress + '%' }" />
      </div>
      <div v-if="enrollment.status === 'completed'" class="mt-3 flex items-center justify-between">
        <span class="text-sm font-semibold text-green-600">🎉 Course Completed!</span>
        <a v-if="has_certificate"
           :href="`/student/courses/${enrollment.id}/certificate`"
           class="text-xs text-blue-600 hover:underline font-medium">
          Download Certificate →
        </a>
      </div>
    </div>

    <!-- Course content -->
    <div class="space-y-4 mb-6">
      <div v-for="(section, si) in sections" :key="section.id"
           class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
          <h2 class="font-semibold text-sm text-gray-900 dark:text-white">{{ section.title }}</h2>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="(lesson, li) in section.lessons" :key="lesson.id">
            <component :is="isLocked(si, li) ? 'div' : 'a'"
                       :href="isLocked(si, li) ? undefined : `/student/courses/${enrollment.id}/lessons/${lesson.id}`"
                       class="flex items-center gap-3 px-5 py-3 transition-colors"
                       :class="isLocked(si, li)
                         ? 'opacity-40 cursor-not-allowed'
                         : 'hover:bg-gray-50 dark:hover:bg-gray-800/30 cursor-pointer'">
              <span class="text-base flex-shrink-0">{{ lessonTypeIcon[lesson.type] ?? '📄' }}</span>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ lesson.title }}</p>
                <p class="text-xs text-gray-400">
                  {{ lesson.type }}
                  <span v-if="lesson.duration_minutes"> · {{ lesson.duration_minutes }}min</span>
                </p>
              </div>
              <div class="flex-shrink-0">
                <span v-if="completed_lessons.includes(lesson.id)"
                      class="text-green-500 text-sm">✓</span>
                <span v-else-if="isLocked(si, li)"
                      class="text-gray-300 text-sm">🔒</span>
              </div>
            </component>
          </div>
        </div>
      </div>
    </div>

    <!-- Assignments -->
    <div v-if="assignments.length"
         class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Assignments</h2>
      </div>
      <div class="divide-y divide-gray-50 dark:divide-gray-800">
        <a v-for="a in assignments" :key="a.id"
           :href="`/student/courses/${enrollment.id}/assignments/${a.id}`"
           class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ a.title }}</p>
            <p class="text-xs text-gray-400">
              <span v-if="a.due_date">Due {{ a.due_date }} · </span>
              Max {{ a.max_marks }} marks
              <span v-if="a.is_required" class="text-red-400"> · required</span>
            </p>
          </div>
          <span v-if="a.submitted" class="text-xs text-green-600 font-medium">✓ Submitted</span>
          <span v-else class="text-xs text-gray-400">→</span>
        </a>
      </div>
    </div>
  </div>
</template>
