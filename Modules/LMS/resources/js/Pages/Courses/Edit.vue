<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout   from '@shared/layouts/AppLayout.vue'
import Button      from '@shared/components/buttons/Button.vue'
import Input       from '@shared/components/form/Input.vue'
import Modal       from '@shared/components/feedback/Modal.vue'
import Badge       from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  course: { type: Object, required: true },
})

// ── Course settings form ─────────────────────────────────────
const courseForm = useForm({
  title:               props.course.title,
  description:         props.course.description ?? '',
  category:            props.course.category ?? '',
  status:              props.course.status,
  difficulty:          props.course.difficulty,
  estimated_hours:     props.course.estimated_hours ?? '',
  certificate_enabled: props.course.certificate_enabled,
  require_sequential:  props.course.require_sequential,
})

function saveCourse() {
  courseForm.patch(`/lms/courses/${props.course.id}`)
}

// ── Section modals ────────────────────────────────────────────
const showAddSection = ref(false)
const editingSection = ref(null)
const sectionForm    = useForm({ title: '', description: '' })

function openAddSection() {
  sectionForm.reset()
  showAddSection.value = true
}

function submitSection() {
  if (editingSection.value) {
    sectionForm.patch(
      `/lms/courses/${props.course.id}/sections/${editingSection.value.id}`,
      { onSuccess: () => { showAddSection.value = false; editingSection.value = null } }
    )
  } else {
    sectionForm.post(`/lms/courses/${props.course.id}/sections`, {
      onSuccess: () => { showAddSection.value = false }
    })
  }
}

function editSection(section) {
  sectionForm.title       = section.title
  sectionForm.description = section.description ?? ''
  editingSection.value    = section
  showAddSection.value    = true
}

function deleteSection(sectionId) {
  if (!confirm('Delete this section and all its lessons?')) return
  router.delete(`/lms/courses/${props.course.id}/sections/${sectionId}`)
}

// ── Lesson actions ────────────────────────────────────────────
function deleteLesson(section, lesson) {
  if (!confirm('Delete this lesson?')) return
  router.delete(
    `/lms/courses/${props.course.id}/sections/${section.id}/lessons/${lesson.id}`
  )
}

const lessonTypeIcon = {
  video: '🎬',
  text:  '📄',
  file:  '📎',
  quiz:  '❓',
}

const statusOptions = [
  { value: 'draft',     label: 'Draft' },
  { value: 'published', label: 'Published' },
  { value: 'archived',  label: 'Archived' },
]
const statusColour = { draft: 'neutral', published: 'success', archived: 'neutral' }
</script>

<template>
  <div class="max-w-5xl space-y-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <a href="/lms/courses" class="text-sm text-primary hover:underline">← Courses</a>
        <h1 class="text-2xl font-bold text-app-text mt-2">{{ course.title }}</h1>
      </div>
      <div class="flex items-center gap-2">
        <a :href="`/lms/courses/${course.id}/cohorts`"
           class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors">
          Cohorts
        </a>
        <a :href="`/lms/courses/${course.id}/report`"
           class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors">
          Report
        </a>
      </div>
    </div>

    <!-- Course settings card -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Course Settings</h2>
        <Badge :type="statusColour[course.status]">{{ course.status }}</Badge>
      </div>
      <form @submit.prevent="saveCourse" class="space-y-4">
        <Input v-model="courseForm.title" label="Title" required :error="courseForm.errors.title" />
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Description</label>
          <textarea v-model="courseForm.description" rows="2"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Status</label>
            <select v-model="courseForm.status"
                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
              <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Difficulty</label>
            <select v-model="courseForm.difficulty"
                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="advanced">Advanced</option>
            </select>
          </div>
          <Input v-model.number="courseForm.estimated_hours" label="Est. Hours" type="number" min="0" />
          <Input v-model="courseForm.category" label="Category" />
        </div>
        <div class="flex items-center gap-6">
          <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
            <input v-model="courseForm.certificate_enabled" type="checkbox"
                   class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
            Issue certificate
          </label>
          <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
            <input v-model="courseForm.require_sequential" type="checkbox"
                   class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
            Sequential lessons
          </label>
        </div>
        <div class="flex justify-end">
          <Button type="submit" :loading="courseForm.processing" size="sm">Save Settings</Button>
        </div>
      </form>
    </div>

    <!-- Sections + Lessons builder -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-app-text">Course Content</h2>
        <Button size="sm" @click="openAddSection">+ Add Section</Button>
      </div>

      <div v-if="!course.sections?.length"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-6 py-12 text-center text-app-text/40 text-sm">
        No sections yet. Add a section to start building the course.
      </div>

      <div v-else class="space-y-4">
        <div v-for="section in course.sections" :key="section.id"
             class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
          <!-- Section header -->
          <div class="flex items-center justify-between px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
            <div>
              <span class="font-semibold text-sm text-app-text">{{ section.title }}</span>
              <span class="ml-2 text-xs text-app-text/40">{{ section.lessons?.length ?? 0 }} lesson(s)</span>
            </div>
            <div class="flex items-center gap-2">
              <button @click="editSection(section)"
                      class="text-xs text-app-text/40 hover:text-primary transition-colors px-2 py-1">
                Edit
              </button>
              <button @click="deleteSection(section.id)"
                      class="text-xs text-app-text/40 hover:text-red-500 transition-colors px-2 py-1">
                Delete
              </button>
            </div>
          </div>

          <!-- Lessons list -->
          <div class="divide-y divide-gray-50 dark:divide-gray-800/50">
            <div v-for="lesson in section.lessons" :key="lesson.id"
                 class="flex items-center justify-between px-5 py-3">
              <div class="flex items-center gap-3 min-w-0">
                <span class="text-base">{{ lessonTypeIcon[lesson.type] ?? '📄' }}</span>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-app-text truncate">{{ lesson.title }}</p>
                  <p class="text-xs text-app-text/40">
                    {{ lesson.type }}
                    <span v-if="lesson.duration_minutes"> · {{ lesson.duration_minutes }}min</span>
                    <span v-if="lesson.has_quiz"> · has quiz</span>
                    <span v-if="lesson.is_free_preview"> · preview</span>
                  </p>
                </div>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <a :href="`/lms/courses/${course.id}/sections/${section.id}/lessons/${lesson.id}/edit`"
                   class="text-xs text-app-text/40 hover:text-primary transition-colors px-2 py-1">
                  Edit
                </a>
                <button @click="deleteLesson(section, lesson)"
                        class="text-xs text-app-text/40 hover:text-red-500 transition-colors px-2 py-1">
                  Delete
                </button>
              </div>
            </div>
          </div>

          <!-- Add lesson button -->
          <div class="px-5 py-3 border-t border-gray-50 dark:border-gray-800/50">
            <a :href="`/lms/courses/${course.id}/sections/${section.id}/lessons/create`"
               class="text-xs text-primary hover:underline">
              + Add Lesson
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Section modal -->
    <Modal :show="showAddSection"
           :title="editingSection ? 'Edit Section' : 'Add Section'"
           @close="showAddSection = false; editingSection = null">
      <form @submit.prevent="submitSection" class="space-y-4">
        <Input v-model="sectionForm.title" label="Section Title" required
               :error="sectionForm.errors.title" />
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Description (optional)</label>
          <textarea v-model="sectionForm.description" rows="2"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>
      </form>
      <template #footer>
        <button @click="showAddSection = false; editingSection = null"
                class="px-4 py-2 text-sm text-app-text/60">Cancel</button>
        <Button @click="submitSection" :loading="sectionForm.processing">
          {{ editingSection ? 'Save' : 'Add Section' }}
        </Button>
      </template>
    </Modal>
  </div>
</template>
