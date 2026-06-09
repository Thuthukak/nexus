<script setup>
import { computed } from 'vue'
import StudentLayout from '@shared/layouts/StudentLayout.vue'

defineOptions({ layout: StudentLayout })

const props = defineProps({
  enrollment: { type: Object, required: true },
  course:     { type: Object, required: true },
  cohort:     { type: Object, required: true },
  sections:   { type: Array,  default: () => [] },
  has_certificate: { type: Boolean, default: false },
})

const totalLessons = computed(() =>
  props.sections.reduce((s, sec) => s + sec.lessons.length, 0)
)

const completedLessons = computed(() =>
  props.sections.reduce((s, sec) =>
    s + sec.lessons.filter(l => l.is_completed).length, 0
  )
)

const lessonIcon = {
  video: '🎬',
  text:  '📄',
  file:  '📎',
  quiz:  '📝',
}

function isLocked(sectionIdx, lessonIdx) {
  if (! props.course.require_sequential) return false
  if (sectionIdx === 0 && lessonIdx === 0) return false

  // Check if previous lesson is complete
  let prev = null
  for (let si = 0; si <= sectionIdx; si++) {
    const section = props.sections[si]
    const maxLi   = si === sectionIdx ? lessonIdx - 1 : section.lessons.length - 1
    for (let li = 0; li <= maxLi; li++) {
      prev = section.lessons[li]
    }
  }
  return prev ? !prev.is_completed : false
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <a href="/student/dashboard" class="text-sm text-primary hover:underline">← My Courses</a>
      <h1 class="text-xl font-bold text-gray-900 dark:text-white mt-2">{{ course.title }}</h1>
      <p class="text-sm text-gray-500 mt-0.5">{{ cohort.name }}</p>
    </div>

    <!-- Progress -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 mb-6">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Your Progress</span>
        <span class="text-sm font-bold" style="color: var(--color-primary);">
          {{ completedLessons }}/{{ totalLessons }} lessons
        </span>
      </div>
      <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
        <div class="h-full rounded-full transition-all duration-500"
             style="background-color: var(--color-primary);"
             :style="{ width: enrollment.progress + '%' }" />
      </div>
      <div class="flex items-center justify-between mt-2">
        <span class="text-xs text-gray-400">{{ enrollment.progress }}% complete</span>
        <a v-if="has_certificate"
           :href="`/student/learn/${enrollment.id}/certificate`"
           class="text-xs text-yellow-600 font-semibold hover:underline">
          🏆 Download Certificate
        </a>
      </div>
    </div>

    <!-- Sections and lessons -->
    <div class="space-y-4">
      <div v-for="(section, si) in sections" :key="section.id"
           class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <!-- Section header -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
          <h2 class="font-semibold text-gray-900 dark:text-white text-sm">{{ section.title }}</h2>
          <p class="text-xs text-gray-400 mt-0.5">
            {{ section.lessons.filter(l => l.is_completed).length }}/{{ section.lessons.length }} complete
          </p>
        </div>

        <!-- Lessons -->
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
          <a v-for="(lesson, li) in section.lessons"
             :key="lesson.id"
             :href="isLocked(si, li) ? '#' : `/student/learn/${enrollment.id}/lesson/${lesson.id}`"
             class="flex items-center gap-3 px-4 py-3 transition-colors"
             :class="isLocked(si, li)
               ? 'opacity-50 cursor-not-allowed'
               : lesson.is_completed
                 ? 'hover:bg-green-50 dark:hover:bg-green-900/10'
                 : 'hover:bg-gray-50 dark:hover:bg-gray-800/40'">

            <!-- Status icon -->
            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                 :class="lesson.is_completed
                   ? 'bg-green-100 dark:bg-green-900/30'
                   : isLocked(si, li)
                     ? 'bg-gray-100 dark:bg-gray-800'
                     : 'bg-gray-100 dark:bg-gray-800'">
              <span v-if="lesson.is_completed" class="text-green-600 text-xs font-bold">✓</span>
              <span v-else-if="isLocked(si, li)" class="text-gray-400 text-xs">🔒</span>
              <span v-else class="text-sm">{{ lessonIcon[lesson.type] }}</span>
            </div>

            <!-- Lesson info -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium truncate"
                 :class="lesson.is_completed
                   ? 'text-green-700 dark:text-green-400'
                   : 'text-gray-900 dark:text-white'">
                {{ lesson.title }}
              </p>
              <div class="flex items-center gap-2 mt-0.5">
                <span class="text-xs text-gray-400 capitalize">{{ lesson.type }}</span>
                <span v-if="lesson.duration_minutes"
                      class="text-xs text-gray-400">{{ lesson.duration_minutes }}min</span>
                <span v-if="lesson.has_quiz && lesson.quiz_passed === true"
                      class="text-xs text-green-600 font-medium">Quiz ✓</span>
                <span v-else-if="lesson.has_quiz && lesson.quiz_passed === false"
                      class="text-xs text-red-500 font-medium">Quiz ✗</span>
                <span v-else-if="lesson.has_quiz"
                      class="text-xs text-blue-600">Quiz</span>
              </div>
            </div>

            <svg v-if="!isLocked(si, li)"
                 class="w-4 h-4 text-gray-300 flex-shrink-0"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
