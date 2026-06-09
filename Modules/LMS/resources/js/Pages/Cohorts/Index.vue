<script setup>
import { ref }       from 'vue'
import { useForm }   from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import Badge         from '@shared/components/display/Badge.vue'
import Button        from '@shared/components/buttons/Button.vue'
import Modal         from '@shared/components/feedback/Modal.vue'
import Input         from '@shared/components/form/Input.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  course:  { type: Object, required: true },
  cohorts: { type: Array,  default: () => [] },
})

const showCreate = ref(false)

const form = useForm({
  name:         '',
  description:  '',
  start_date:   '',
  end_date:     '',
  max_students: '',
  teacher_id:   '',
})

function submit() {
  form.post(`/lms/courses/${props.course.id}/cohorts`, {
    onSuccess: () => { showCreate.value = false; form.reset() },
  })
}

const statusColour = {
  upcoming:  'info',
  active:    'success',
  completed: 'neutral',
  cancelled: 'danger',
}
</script>

<template>
  <div class="max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <a :href="`/lms/courses/${course.id}/edit`"
           class="text-sm text-primary hover:underline">← {{ course.title }}</a>
        <h1 class="text-2xl font-bold text-app-text mt-2">Cohorts</h1>
        <p class="text-sm text-app-text/60 mt-1">{{ cohorts.length }} cohort(s)</p>
      </div>
      <Button @click="showCreate = true">+ New Cohort</Button>
    </div>

    <div v-if="!cohorts.length"
         class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-6 py-14 text-center text-app-text/40 text-sm">
      No cohorts yet. Create a cohort then enroll students.
    </div>

    <div v-else class="space-y-3">
      <div v-for="cohort in cohorts" :key="cohort.id"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5 flex items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-3 mb-1">
            <h2 class="font-semibold text-app-text">{{ cohort.name }}</h2>
            <Badge :type="statusColour[cohort.status] ?? 'neutral'">{{ cohort.status }}</Badge>
          </div>
          <div class="flex items-center gap-4 text-xs text-app-text/50">
            <span>{{ cohort.start_date }} → {{ cohort.end_date ?? 'Open-ended' }}</span>
            <span>{{ cohort.enrollments_count }} enrolled</span>
            <span v-if="cohort.max_students">Max {{ cohort.max_students }}</span>
            <span v-if="cohort.teacher">Teacher: {{ cohort.teacher }}</span>
          </div>
        </div>
        <a :href="`/lms/courses/${course.id}/cohorts/${cohort.id}`"
           class="px-4 py-2 text-sm font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-primary hover:border-primary/30 transition-colors flex-shrink-0">
          Manage →
        </a>
      </div>
    </div>

    <!-- Create cohort modal -->
    <Modal :show="showCreate" title="New Cohort" size="md" @close="showCreate = false">
      <form @submit.prevent="submit" class="space-y-4">
        <Input v-model="form.name" label="Cohort Name" required
               placeholder="e.g. January 2026 Intake" :error="form.errors.name" />
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Description</label>
          <textarea v-model="form.description" rows="2"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <Input v-model="form.start_date" label="Start Date" type="date"
                 required :error="form.errors.start_date" />
          <Input v-model="form.end_date" label="End Date" type="date" />
        </div>
        <Input v-model.number="form.max_students"
               label="Max Students" type="number" min="1"
               hint="Leave blank for unlimited" />
      </form>
      <template #footer>
        <button @click="showCreate = false"
                class="px-4 py-2 text-sm text-app-text/60">Cancel</button>
        <Button @click="submit" :loading="form.processing">Create Cohort</Button>
      </template>
    </Modal>
  </div>
</template>
