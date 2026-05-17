<script setup>
import { ref }       from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import Input         from '@shared/components/form/Input.vue'
import Button        from '@shared/components/buttons/Button.vue'
import Badge         from '@shared/components/display/Badge.vue'
import ConfirmDialog from '@shared/components/feedback/ConfirmDialog.vue'

defineOptions({ layout: AppLayout })
defineProps({ taxRates: { type: Array, default: () => [] } })

const form = useForm({
  name:        '',
  rate:        '',
  is_compound: false,
  is_default:  false,
})

function submit() {
  form.post('/financial/tax-rates', {
    onSuccess: () => form.reset(),
  })
}

const confirmDelete = ref(false)
const deletingId    = ref(null)

function promptDelete(id) {
  deletingId.value    = id
  confirmDelete.value = true
}

function handleDelete() {
  router.delete(`/financial/tax-rates/${deletingId.value}`, {
    onFinish: () => {
      confirmDelete.value = false
      deletingId.value    = null
    },
  })
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">Tax Rates</h1>
      <p class="text-sm text-app-text/60 mt-1">Manage tax rates applied to invoices</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Tax rates list -->
      <div class="lg:col-span-2 bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Rate</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Type</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Status</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
            <tr v-if="!taxRates.length">
              <td colspan="5" class="px-4 py-10 text-center text-app-text/40">No tax rates yet.</td>
            </tr>
            <tr v-for="rate in taxRates" :key="rate.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td class="px-4 h-12 font-medium text-app-text">
                {{ rate.name }}
                <Badge v-if="rate.is_default" type="info" class="ml-2">Default</Badge>
              </td>
              <td class="px-4 h-12 text-app-text">{{ rate.rate }}%</td>
              <td class="px-4 h-12 text-app-text/60">{{ rate.is_compound ? 'Compound' : 'Simple' }}</td>
              <td class="px-4 h-12">
                <Badge :type="rate.is_active ? 'success' : 'neutral'" dot>
                  {{ rate.is_active ? 'Active' : 'Inactive' }}
                </Badge>
              </td>
              <td class="px-4 h-12 text-right">
                <button @click="promptDelete(rate.id)"
                        class="text-xs text-app-text/40 hover:text-red-500 transition-colors">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add tax rate -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-sm font-semibold text-app-text mb-4">Add Tax Rate</h2>
        <form @submit.prevent="submit" class="space-y-4">
          <Input v-model="form.name" label="Name" placeholder="e.g. VAT 15%" required :error="form.errors.name" />
          <Input v-model="form.rate" label="Rate (%)" type="number" min="0" max="100" step="0.01" required :error="form.errors.rate" />

          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <input v-model="form.is_default" type="checkbox" id="is_default"
                     class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
              <label for="is_default" class="text-sm text-app-text">Set as default</label>
            </div>
            <div class="flex items-center gap-2">
              <input v-model="form.is_compound" type="checkbox" id="is_compound"
                     class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50" />
              <label for="is_compound" class="text-sm text-app-text">Compound tax</label>
            </div>
          </div>

          <Button type="submit" :loading="form.processing" class="w-full">Add Rate</Button>
        </form>
      </div>
    </div>

    <ConfirmDialog
      :show="confirmDelete"
      title="Delete Tax Rate"
      message="This tax rate will be deleted. Existing invoices will not be affected."
      confirm-label="Delete"
      danger
      @confirm="handleDelete"
      @cancel="confirmDelete = false"
    />
  </div>
</template>
