<script setup>
import { useForm }  from '@inertiajs/vue3'
import AppLayout    from '@shared/layouts/AppLayout.vue'
import Input        from '@shared/components/form/Input.vue'
import Button       from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const form = useForm({
  title:               '',
  description:         '',
  category:            '',
  difficulty:          'beginner',
  estimated_hours:     '',
  certificate_enabled: true,
  require_sequential:  true,
})

function submit() {
  form.post('/lms/courses')
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <a href="/lms/courses" class="text-sm text-primary hover:underline">← Courses</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">New Course</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Course Details</h2>

        <Input v-model="form.title" label="Course Title" required :error="form.errors.title" />

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Description</label>
          <textarea v-model="form.description" rows="3"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Difficulty</label>
            <select v-model="form.difficulty"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
              <option value="beginner">Beginner</option>
              <option value="intermediate">Intermediate</option>
              <option value="advanced">Advanced</option>
            </select>
          </div>
          <Input v-model.number="form.estimated_hours"
                 label="Est. Hours" type="number" min="0" />
          <Input v-model="form.category" label="Category"
                 hint="e.g. Compliance, Safety" />
        </div>

        <div class="space-y-2 pt-2">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input v-model="form.certificate_enabled" type="checkbox"
                   class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
            <div>
              <span class="text-sm font-medium text-app-text">Issue certificate on completion</span>
              <p class="text-xs text-app-text/50">Auto-generate a PDF certificate when the student completes all lessons and passes all quizzes.</p>
            </div>
          </label>
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input v-model="form.require_sequential" type="checkbox"
                   class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
            <div>
              <span class="text-sm font-medium text-app-text">Require sequential completion</span>
              <p class="text-xs text-app-text/50">Students must complete each lesson in order before unlocking the next.</p>
            </div>
          </label>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <a href="/lms/courses"
           class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">
          Create Course
        </Button>
      </div>
    </form>
  </div>
</template>
