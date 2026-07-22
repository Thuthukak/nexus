<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout  from '@shared/layouts/AppLayout.vue'
import Input      from '@shared/components/form/Input.vue'
import Button     from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  course:  { type: Object, required: true },
  section: { type: Object, required: true },
})

const form = useForm({
  title:            '',
  type:             'text',
  content:          '',
  video_url:        '',
  video_type:       'embed',
  duration_minutes: '',
  is_free_preview:  false,
})

const isVideo = computed(() => form.type === 'video')
const isText  = computed(() => form.type === 'text')

function submit() {
  form.post(`/lms/courses/${props.course.id}/sections/${props.section.id}/lessons`)
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <a :href="`/lms/courses/${course.id}/edit`"
         class="text-sm text-primary hover:underline">← {{ course.title }}</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">New Lesson</h1>
      <p class="text-sm text-app-text/60 mt-1">Section: {{ section.title }}</p>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <Input v-model="form.title" label="Lesson Title" required :error="form.errors.title" />

        <!-- Type selector -->
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Lesson Type</label>
          <div class="grid grid-cols-4 gap-2">
            <button v-for="t in [
              { value: 'text',  label: '📄 Text' },
              { value: 'video', label: '🎬 Video' },
              { value: 'file',  label: '📎 File' },
              { value: 'quiz',  label: '❓ Quiz' },
            ]" :key="t.value" type="button"
                    @click="form.type = t.value"
                    class="px-3 py-2.5 rounded-lg border-2 text-sm font-medium transition-all text-center"
                    :class="form.type === t.value
                      ? 'border-primary bg-primary/5 text-primary'
                      : 'border-gray-200 dark:border-gray-700 text-app-text/60 hover:border-gray-300'">
              {{ t.label }}
            </button>
          </div>
        </div>

        <!-- Video options -->
        <template v-if="isVideo">
          <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
              <input v-model="form.video_type" type="radio" value="embed"
                     class="text-primary focus:ring-primary/50" />
              Embed (YouTube / Vimeo)
            </label>
            <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
              <input v-model="form.video_type" type="radio" value="upload"
                     class="text-primary focus:ring-primary/50" />
              Upload video file
            </label>
          </div>
          <Input v-if="form.video_type === 'embed'"
                 v-model="form.video_url" label="Video URL"
                 placeholder="https://youtube.com/watch?v=... or https://vimeo.com/..."
                 :error="form.errors.video_url" />
          <div v-else class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Video File</label>
            <input type="file" accept="video/mp4,video/mov,video/avi,video/webm"
                   @change="e => form.video_upload = e.target.files[0]"
                   class="text-sm text-app-text/60" />
            <p class="text-xs text-app-text/40">Max 500MB. mp4, mov, avi, webm.</p>
          </div>
        </template>

        <!-- Text content -->
        <div v-if="isText" class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Content</label>
          <textarea v-model="form.content" rows="8" placeholder="Lesson content (HTML or plain text)..."
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary/50 resize-y" />
        </div>

        <div v-if="form.type === 'quiz'"
             class="text-sm text-blue-600 bg-blue-50 dark:bg-blue-900/20 px-4 py-3 rounded-lg">
          A quiz will be automatically created. You can add questions after saving.
        </div>

        <div class="grid grid-cols-2 gap-4">
          <Input v-model.number="form.duration_minutes" label="Duration (minutes)"
                 type="number" min="0" :error="form.errors.duration_minutes" />
          <div class="flex flex-col justify-end">
            <label class="flex items-center gap-2 cursor-pointer text-sm text-app-text">
              <input v-model="form.is_free_preview" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
              Free preview (visible without enrollment)
            </label>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <a :href="`/lms/courses/${course.id}/edit`"
           class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Create Lesson</Button>
      </div>
    </form>
  </div>
</template>
