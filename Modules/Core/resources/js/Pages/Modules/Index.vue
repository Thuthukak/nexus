<script setup>
import { ref }         from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout       from '@shared/layouts/AppLayout.vue'
import Badge           from '@shared/components/display/Badge.vue'
import Modal           from '@shared/components/feedback/Modal.vue'
import Button          from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  modules: { type: Array,  required: true },
  licence: { type: Object, required: true },
})

function enable(name)  { router.patch(`/admin/modules/${name}/enable`) }
function disable(name) { router.patch(`/admin/modules/${name}/disable`) }

const showLicenceModal = ref(false)
const licenceForm      = useForm({ licence_key: '' })

function updateLicence() {
  licenceForm.post('/admin/modules/licence', {
    onSuccess: () => {
      showLicenceModal.value = false
      licenceForm.reset()
    },
  })
}

const statusBadge = (mod) => {
  if (mod.is_core)     return { type: 'info',    label: 'Core' }
  if (mod.is_enabled)  return { type: 'success', label: 'Active' }
  if (mod.is_licensed) return { type: 'neutral', label: 'Inactive' }
  return { type: 'danger', label: 'Not Licensed' }
}
</script>

<template>
  <div class="max-w-4xl">
    <div class="mb-6 flex items-start justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Module Manager</h1>
        <p class="text-sm text-app-text/60 mt-1">Enable or disable platform modules</p>
      </div>
      <button @click="showLicenceModal = true"
              class="text-sm text-primary hover:underline">
        Update Licence Key
      </button>
    </div>

    <!-- Licence info -->
    <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-5 mb-6">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div>
          <p class="text-xs text-app-text/50 mb-0.5">Licensed To</p>
          <p class="font-semibold text-app-text">
            {{ licence.is_dev ? 'Development' : licence.licensee }}
          </p>
        </div>
        <div v-if="!licence.is_dev">
          <p class="text-xs text-app-text/50 mb-0.5">Expires</p>
          <p class="font-semibold text-app-text">{{ licence.expires_at }}</p>
        </div>
        <div>
          <p class="text-xs text-app-text/50 mb-0.5">Tier</p>
          <p class="font-semibold text-app-text capitalize">{{ licence.tier }}</p>
        </div>
        <div>
          <p class="text-xs text-app-text/50 mb-0.5">Max Users</p>
          <p class="font-semibold text-app-text">{{ licence.max_users }}</p>
        </div>
      </div>
    </div>

    <!-- Module cards -->
    <div class="space-y-3">
      <div v-for="mod in modules" :key="mod.name"
           class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1 flex-wrap">
              <h2 class="font-semibold text-app-text">{{ mod.name }}</h2>
              <Badge :type="statusBadge(mod).type">{{ statusBadge(mod).label }}</Badge>
              <span class="text-xs text-app-text/30 font-mono">v{{ mod.version }}</span>
            </div>
            <p class="text-sm text-app-text/60">{{ mod.description }}</p>
            <p v-if="mod.requires?.length" class="text-xs text-app-text/40 mt-1">
              Requires: {{ mod.requires.join(', ') }}
            </p>
          </div>

          <div class="flex-shrink-0">
            <!-- Core — cannot toggle -->
            <span v-if="mod.is_core"
                  class="text-xs text-app-text/30 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700">
              Always On
            </span>

            <!-- Licensed + enabled — can disable -->
            <button v-else-if="mod.is_enabled"
                    @click="disable(mod.name)"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-app-text/60 hover:text-red-500 hover:border-red-200 transition-colors">
              Disable
            </button>

            <!-- Licensed + disabled — can enable -->
            <button v-else-if="mod.is_licensed"
                    @click="enable(mod.name)"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg text-white transition-opacity hover:opacity-90"
                    style="background-color: var(--color-primary);">
              Enable
            </button>

            <!-- Not licensed -->
            <span v-else
                  class="text-xs text-app-text/30 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700">
              Not Licensed
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Licence update modal -->
    <Modal :show="showLicenceModal" title="Update Licence Key" size="md"
           @close="showLicenceModal = false">
      <div class="space-y-4">
        <p class="text-sm text-app-text/60">
          Paste your new licence key below. This will update your licensed modules
          and refresh all module activation states.
        </p>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Licence Key</label>
          <textarea v-model="licenceForm.licence_key" rows="5"
                    placeholder="Paste licence key…"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
          <p v-if="licenceForm.errors.licence_key" class="text-xs text-red-500">
            {{ licenceForm.errors.licence_key }}
          </p>
        </div>
      </div>
      <template #footer>
        <button @click="showLicenceModal = false"
                class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">
          Cancel
        </button>
        <Button @click="updateLicence" :loading="licenceForm.processing">
          Update Licence
        </Button>
      </template>
    </Modal>
  </div>
</template>
