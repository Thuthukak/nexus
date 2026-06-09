<script setup>
import { ref, computed, watch } from 'vue'
import { useForm }              from '@inertiajs/vue3'
import AppLayout                from '@shared/layouts/AppLayout.vue'
import Input                    from '@shared/components/form/Input.vue'
import Button                   from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  course:  { type: Object, required: true },
  section: { type: Object, required: true },
})

const videoFileInput  = ref(null)
const videoFileName   = ref('')
const lessonFileInput = ref(null)
const lessonFileName  = ref('')

const form = useForm({
  title:             '',
  type:              'text',
  content:           '',
  video_url:         '',
  video_type:        'embed',
  video_upload:      null,
  duration_minutes:  '',
  is_free_preview:   false,
})

watch(() => form.type, (newType) => {
  if (newType === 'video') form.video_type = 'embed'
})

function onVideoFileChange(e) {
  const file = e.target.files[0]
  if (!file) return
  form.video_upload = file
  videoFileName.value = file.name
  form.video_type  = 'upload'
}

function submit() {
  form.post(
    `/lms/courses/${props.course.id}/sections/${props.section.id}/lessons`,
    { forceFormData: true }
  )
}

const lessonTypes = [
  { value: 'text',  label: '📄 Text / Rich Content' },
  { value: 'video', label: '🎬 Video' },
  { value: 'file',  label: '📎 File Download' },
  { value: 'quiz',  label: '📝 Quiz' },
]
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <a :href="`/lms/courses/${course.id}/edit`"
         class="text-sm text-primary hover:underline">← {{ course.title }}</a>
      <p class="text-xs text-app-text/40 mt-0.5">{{ section.title }}</p>
      <h1 class="text-2xl font-bold text-app-text mt-1">Add Lesson</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">

        <Input v-model="form.title" label="Lesson Title" required :error="form.errors.title" />

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Lesson Type</label>
          <div class="grid grid-cols-2 gap-2">
            <label v-for="t in lessonTypes" :key="t.value"
                   class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg border-2 cursor-pointer transition-all"
                   :class="form.type === t.value
                     ? 'border-primary bg-primary/5'
                     : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
              <input v-model="form.type" type="radio" :value="t.value" class="hidden" />
              <span class="text-sm font-medium text-app-text">{{ t.label }}</span>
            </label>
          </div>
        </div>

        <!-- Text content -->
        <div v-if="form.type === 'text' || form.type === 'file'" class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Content / Instructions</label>
          <textarea v-model="form.content" rows="5"
                    :placeholder="form.type === 'file' ? 'Instructions for this download…' : 'Lesson content…'"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>

        <!-- Video options -->
        <div v-if="form.type === 'video'" class="space-y-3">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Video Source</label>
            <div class="flex gap-3">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.video_type" type="radio" value="embed"
                       class="w-4 h-4 text-primary" />
                <span class="text-sm text-app-text">Embed URL (YouTube/Vimeo)</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.video_type" type="radio" value="upload"
                       class="w-4 h-4 text-primary" />
                <span class="text-sm text-app-text">Upload Video File</span>
              </label>
            </div>
          </div>

          <div v-if="form.video_type === 'embed'" class="flex flex-col gap-1">
            <Input v-model="form.video_url"
                   label="Video URL"
                   placeholder="https://www.youtube.com/watch?v=..."
                   :error="form.errors.video_url" />
            <p class="text-xs text-app-text/40">
              Supports YouTube and Vimeo URLs. Recommended for low-bandwidth access.
            </p>
          </div>

          <div v-else class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Video File</label>
            <div class="flex items-center gap-3">
              <input ref="videoFileInput" type="file"
                     accept="video/mp4,video/mov,video/avi,video/webm"
                     class="hidden" @change="onVideoFileChange" />
              <button type="button" @click="videoFileInput.click()"
                      class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-app-text/60 hover:text-app-text transition-colors">
                Choose Video
              </button>
              <span class="text-sm text-app-text/50">{{ videoFileName || 'No file chosen' }}</span>
            </div>
            <p class="text-xs text-app-text/40">MP4, MOV, AVI, WebM — max 500MB</p>
          </div>

          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Notes / Description</label>
            <textarea v-model="form.content" rows="3"
                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
          </div>
        </div>

        <!-- Quiz info -->
        <div v-if="form.type === 'quiz'"
             class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl px-4 py-3">
          <p class="text-sm text-blue-700 dark:text-blue-400 font-medium">Quiz Lesson</p>
          <p class="text-xs text-blue-600/80 dark:text-blue-500 mt-1">
            A blank quiz will be created automatically. You can add questions and configure settings
            after creating the lesson.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <Input v-model.number="form.duration_minutes"
                 label="Duration (minutes)"
                 type="number" min="0"
                 hint="Approximate time to complete" />
          <div class="flex flex-col justify-end pb-0.5">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="form.is_free_preview" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 text-primary" />
              <span class="text-sm font-medium text-app-text">Free preview</span>
            </label>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <a :href="`/lms/courses/${course.id}/edit`"
           class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Add Lesson</Button>
      </div>
    </form>
  </div>
</template>
