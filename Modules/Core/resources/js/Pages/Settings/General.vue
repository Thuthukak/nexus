<script setup>
import { ref }      from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import AppLayout    from '@shared/layouts/AppLayout.vue'
import Input        from '@shared/components/form/Input.vue'
import Button       from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({ settings: { type: Object, required: true } })
const page  = usePage()

const form = useForm({
  app_name: props.settings.app_name,
  timezone: props.settings.timezone,
})

function submit() {
  form.patch('/settings/general')
}

const logoForm    = useForm({ logo: null })
const logoPreview = ref(page.props.app?.logo_url ?? null)
const logoInput   = ref(null)

function onLogoChange(e) {
  const file = e.target.files[0]
  if (!file) return
  logoPreview.value = URL.createObjectURL(file)
  logoForm.logo = file
  logoForm.post('/settings/logo', {
    forceFormData: true,
    onSuccess: () => {
      logoPreview.value = page.props.app?.logo_url ?? null
    },
  })
}

function removeLogo() {
  router.delete('/settings/logo', {
    onSuccess: () => { logoPreview.value = null },
  })
}

const timezones = [
  'Africa/Johannesburg', 'Africa/Cairo', 'Africa/Lagos',
  'Europe/London', 'Europe/Paris',
  'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles',
  'Asia/Dubai', 'Asia/Singapore', 'Asia/Tokyo',
  'Australia/Sydney', 'UTC',
]
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">General Settings</h1>
      <p class="text-sm text-app-text/60 mt-1">Platform-wide configuration</p>
    </div>

    <div class="space-y-6">
      <!-- Logo -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">
          Application Logo
        </h2>
        <div class="flex items-center gap-6">
          <div class="w-24 h-16 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-gray-900/50 flex-shrink-0">
            <img v-if="logoPreview" :src="logoPreview" class="h-full w-full object-contain p-1" />
            <svg v-else class="w-8 h-8 text-app-text/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <div class="space-y-2">
            <input ref="logoInput" type="file" accept="image/*"
                   class="hidden" @change="onLogoChange" />
            <button @click="logoInput.click()"
                    class="px-4 py-2 text-sm font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/70 hover:text-app-text hover:border-gray-300 transition-colors">
              {{ logoPreview ? 'Change Logo' : 'Upload Logo' }}
            </button>
            <button v-if="logoPreview" @click="removeLogo"
                    class="block text-xs text-red-400 hover:text-red-600 transition-colors">
              Remove logo
            </button>
            <p class="text-xs text-app-text/40">PNG, JPG or SVG. Max 2MB. Recommended: 200×60px</p>
          </div>
        </div>
      </div>

      <!-- App settings -->
      <form @submit.prevent="submit">
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Application</h2>
          <Input v-model="form.app_name" label="Application Name"
                 hint="Displayed in the browser tab and sidebar header"
                 :error="form.errors.app_name" />
          <div class="flex flex-col gap-1">
            <label class="text-sm font-medium text-app-text">Timezone</label>
            <select v-model="form.timezone"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
              <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end mt-4">
          <Button type="submit" :loading="form.processing">Save Settings</Button>
        </div>
      </form>
    </div>
  </div>
</template>
