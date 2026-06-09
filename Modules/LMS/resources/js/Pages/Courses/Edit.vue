<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout  from '@shared/layouts/AppLayout.vue'
import Input      from '@shared/components/form/Input.vue'
import Button     from '@shared/components/buttons/Button.vue'
import Modal      from '@shared/components/feedback/Modal.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  course: { type: Object, required: true },
})

const activeTab = ref('content')

// ── Course settings form ──────────────────────────────────────
const settingsForm = useForm({
  title:               props.course.title,
  description:         props.course.description ?? '',
  category:            props.course.category ?? '',
  status:              props.course.status,
  difficulty:          props.course.difficulty,
  estimated_hours:     props.course.estimated_hours ?? 0,
  certificate_enabled: props.course.certificate_enabled,
  require_sequential:  props.course.require_sequential,
})

function saveSettings() {
  settingsForm.patch(`/lms/courses/${props.course.id}`)
}

// ── Add section ───────────────────────────────────────────────
const showAddSection = ref(false)
const sectionForm    = useForm({ title: '', description: '' })

function addSection() {
  sectionForm.post(`/lms/courses/${props.course.id}/sections`, {
    onSuccess: () => {
      showAddSection.value = false
      sectionForm.reset()
    },
  })
}

// ── Edit section ──────────────────────────────────────────────
const editingSection = ref(null)
const editSectionForm= useForm({ title: '', description: '' })

function openEditSection(section) {
  editingSection.value    = section
  editSectionForm.title   = section.title
  editSectionForm.description = section.description ?? ''
}

function saveSection() {
  editSectionForm.patch(
    `/lms/courses/${props.course.id}/sections/${editingSection.value.id}`,
    { onSuccess: () => editingSection.value = null }
  )
}

function deleteSection(sectionId) {
  router.delete(`/lms/courses/${props.course.id}/sections/${sectionId}`)
}

// ── Lesson type icons ─────────────────────────────────────────
const lessonIcon = {
  video: 'M15 10l4.553-2.069A1 1 0 0121 8.82v6.361a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
  text:  'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  file:  'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
  quiz:  'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
}

const totalLessons = computed(() =>
  props.course.sections?.reduce((s, sec) => s + (sec.lessons?.length ?? 0), 0) ?? 0
)
</script>

<template>
  <div class="max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <a href="/lms/courses" class="text-sm text-primary hover:underline">← Courses</a>
        <h1 class="text-2xl font-bold text-app-text mt-2">{{ course.title }}</h1>
        <p class="text-sm text-app-text/60 mt-1">
          {{ course.sections?.length ?? 0 }} sections · {{ totalLessons }} lessons
        </p>
      </div>
      <div class="flex items-center gap-2">
        <a :href="`/lms/courses/${course.id}/cohorts`"
           class="px-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/70 hover:text-app-text transition-colors">
          Manage Cohorts
        </a>
        <a :href="`/lms/courses/${course.id}/report`"
           class="px-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/70 hover:text-app-text transition-colors">
          View Report
        </a>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 bg-gray-100 dark:bg-gray-800 rounded-xl p-1 w-fit">
      <button v-for="tab in [
        { key: 'content', label: 'Content' },
        { key: 'settings', label: 'Settings' },
      ]" :key="tab.key"
              @click="activeTab = tab.key"
              class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
              :class="activeTab === tab.key
                ? 'bg-surface text-app-text shadow-sm'
                : 'text-app-text/50 hover:text-app-text'">
        {{ tab.label }}
      </button>
    </div>

    <!-- ── CONTENT TAB ──────────────────────────────────────── -->
    <div v-if="activeTab === 'content'" class="space-y-4">

      <!-- Sections -->
      <div v-for="section in course.sections" :key="section.id"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <!-- Section header -->
        <div class="flex items-center justify-between px-5 py-3 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
          <h2 class="font-semibold text-app-text text-sm">{{ section.title }}</h2>
          <div class="flex items-center gap-2">
            <a :href="`/lms/courses/${course.id}/sections/${section.id}/lessons/create`"
               class="text-xs text-primary hover:underline">
              + Add Lesson
            </a>
            <button @click="openEditSection(section)"
                    class="text-xs text-app-text/40 hover:text-app-text transition-colors px-2 py-1">
              Edit
            </button>
            <button @click="deleteSection(section.id)"
                    class="text-xs text-red-400 hover:text-red-600 transition-colors px-2 py-1">
              Delete
            </button>
          </div>
        </div>

        <!-- Lessons list -->
        <div v-if="!section.lessons?.length"
             class="px-5 py-6 text-sm text-app-text/40 text-center">
          No lessons yet.
          <a :href="`/lms/courses/${course.id}/sections/${section.id}/lessons/create`"
             class="text-primary hover:underline ml-1">Add the first lesson →</a>
        </div>

        <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
          <div v-for="lesson in section.lessons" :key="lesson.id"
               class="flex items-center justify-between px-5 py-3">
            <div class="flex items-center gap-3">
              <svg class="w-4 h-4 text-app-text/30 flex-shrink-0"
                   fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  :d="lessonIcon[lesson.type]" />
              </svg>
              <div>
                <span class="text-sm font-medium text-app-text">{{ lesson.title }}</span>
                <span class="ml-2 text-xs text-app-text/40 capitalize">{{ lesson.type }}</span>
                <span v-if="lesson.duration_minutes" class="ml-2 text-xs text-app-text/30">
                  {{ lesson.duration_minutes }}min
                </span>
                <span v-if="lesson.has_quiz"
                      class="ml-2 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 px-1.5 rounded">
                  Quiz
                </span>
                <span v-if="lesson.files_count"
                      class="ml-2 text-xs bg-gray-100 text-gray-500 px-1.5 rounded">
                  {{ lesson.files_count }} file(s)
                </span>
              </div>
            </div>
            <a :href="`/lms/courses/${course.id}/sections/${section.id}/lessons/${lesson.id}/edit`"
               class="text-xs text-app-text/40 hover:text-primary transition-colors px-2 py-1">
              Edit
            </a>
          </div>
        </div>
      </div>

      <!-- Add section button -->
      <button @click="showAddSection = true"
              class="w-full flex items-center justify-center gap-2 py-4 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 text-app-text/40 hover:text-app-text hover:border-gray-300 transition-colors text-sm font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Section
      </button>
    </div>

    <!-- ── SETTINGS TAB ─────────────────────────────────────── -->
    <div v-if="activeTab === 'settings'">
      <form @submit.prevent="saveSettings" class="space-y-6">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Course Details</h2>
          <Input v-model="settingsForm.title" label="Title" required :error="settingsForm.errors.title" />
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Description</label>
            <textarea v-model="settingsForm.description" rows="3"
                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="flex flex-col gap-1">
              <label class="text-sm font-medium text-app-text">Status</label>
              <select v-model="settingsForm.status"
                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
              </select>
            </div>
            <div class="flex flex-col gap-1">
              <label class="text-sm font-medium text-app-text">Difficulty</label>
              <select v-model="settingsForm.difficulty"
                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
              </select>
            </div>
            <Input v-model.number="settingsForm.estimated_hours" label="Est. Hours" type="number" min="0" />
            <Input v-model="settingsForm.category" label="Category" />
          </div>
          <div class="space-y-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="settingsForm.certificate_enabled" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              <span class="text-sm font-medium text-app-text">Issue certificate on completion</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="settingsForm.require_sequential" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              <span class="text-sm font-medium text-app-text">Require sequential lesson completion</span>
            </label>
          </div>
        </div>
        <div class="flex justify-end">
          <Button type="submit" :loading="settingsForm.processing">Save Settings</Button>
        </div>
      </form>
    </div>

  </div>

  <!-- Add Section Modal -->
  <Modal :show="showAddSection" title="Add Section" size="md" @close="showAddSection = false">
    <div class="space-y-4">
      <Input v-model="sectionForm.title" label="Section Title" required :error="sectionForm.errors.title" />
      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Description</label>
        <textarea v-model="sectionForm.description" rows="2"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
      </div>
    </div>
    <template #footer>
      <button @click="showAddSection = false"
              class="px-4 py-2 text-sm text-app-text/60">Cancel</button>
      <Button @click="addSection" :loading="sectionForm.processing">Add Section</Button>
    </template>
  </Modal>

  <!-- Edit Section Modal -->
  <Modal :show="!!editingSection" title="Edit Section" size="md" @close="editingSection = null">
    <div class="space-y-4">
      <Input v-model="editSectionForm.title" label="Title" required />
      <div class="flex flex-col gap-1">
        <label class="text-sm font-medium text-app-text">Description</label>
        <textarea v-model="editSectionForm.description" rows="2"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
      </div>
    </div>
    <template #footer>
      <button @click="editingSection = null" class="px-4 py-2 text-sm text-app-text/60">Cancel</button>
      <Button @click="saveSection" :loading="editSectionForm.processing">Save</Button>
    </template>
  </Modal>
</template>
