<script setup>
import { ref }     from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout   from '@shared/layouts/AppLayout.vue'
import Input       from '@shared/components/form/Input.vue'
import Button      from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const bannerInput   = ref(null)
const bannerPreview = ref(null)

const form = useForm({
  title:           '',
  description:     '',
  venue:           '',
  venue_address:   '',
  starts_at:       '',
  ends_at:         '',
  status:          'draft',
  is_featured:     false,
  organiser_name:  '',
  organiser_email: '',
  banner:          null,
})

function onBannerChange(e) {
  const file = e.target.files[0]
  if (!file) return
  form.banner   = file
  bannerPreview.value = URL.createObjectURL(file)
}

function submit() {
  form.post('/events-admin/events', { forceFormData: true })
}
</script>

<template>
  <div class="max-w-3xl">
    <div class="mb-6">
      <a href="/events-admin/events" class="text-sm text-primary hover:underline">← Events</a>
      <h1 class="text-2xl font-bold text-app-text mt-2">New Event</h1>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- Banner upload -->
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="relative h-48 bg-gradient-to-br from-primary/20 to-primary/5 cursor-pointer group"
             @click="bannerInput.click()">
          <img v-if="bannerPreview" :src="bannerPreview"
               class="absolute inset-0 w-full h-full object-cover" />
          <div class="absolute inset-0 flex flex-col items-center justify-center gap-2"
               :class="bannerPreview ? 'bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity' : ''">
            <svg class="w-8 h-8" :class="bannerPreview ? 'text-white' : 'text-primary/40'"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-sm font-medium" :class="bannerPreview ? 'text-white' : 'text-primary/50'">
              {{ bannerPreview ? 'Change banner image' : 'Click to upload banner image' }}
            </span>
            <span class="text-xs" :class="bannerPreview ? 'text-white/70' : 'text-primary/30'">
              Recommended: 1200×400px
            </span>
          </div>
        </div>
        <input ref="bannerInput" type="file" accept="image/*" class="hidden"
               @change="onBannerChange" />
      </div>

      <!-- Event details -->
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Event Details</h2>

        <Input v-model="form.title" label="Event Title" required :error="form.errors.title" />

        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Description</label>
          <textarea v-model="form.description" rows="4"
                    placeholder="Tell people what this event is about…"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Input v-model="form.starts_at" label="Start Date & Time"
                 type="datetime-local" required :error="form.errors.starts_at" />
          <Input v-model="form.ends_at" label="End Date & Time"
                 type="datetime-local" />
        </div>

        <Input v-model="form.venue" label="Venue Name"
               placeholder="e.g. Sandton Convention Centre" />
        <Input v-model="form.venue_address" label="Venue Address"
               placeholder="e.g. 161 Maude St, Sandton" />
      </div>

      <!-- Organiser -->
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Organiser</h2>
        <div class="grid grid-cols-2 gap-4">
          <Input v-model="form.organiser_name"  label="Organiser Name" />
          <Input v-model="form.organiser_email" label="Organiser Email" type="email" />
        </div>
      </div>

      <!-- Settings -->
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Settings</h2>
        <div class="flex items-center gap-6">
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Status</label>
            <select v-model="form.status"
                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
              <option value="draft">Draft (not visible)</option>
              <option value="published">Published (public)</option>
            </select>
          </div>
          <label class="flex items-center gap-2 cursor-pointer mt-4">
            <input v-model="form.is_featured" type="checkbox"
                   class="w-4 h-4 rounded border-gray-300 text-primary" />
            <div>
              <span class="text-sm font-medium text-app-text">Featured event</span>
              <p class="text-xs text-app-text/50">Show prominently on the events page</p>
            </div>
          </label>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <a href="/events-admin/events"
           class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</a>
        <Button type="submit" :loading="form.processing">Create Event</Button>
      </div>
    </form>
  </div>
</template>
