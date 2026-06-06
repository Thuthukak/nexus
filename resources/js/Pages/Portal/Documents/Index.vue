<script setup>
import PortalLayout from '@shared/layouts/PortalLayout.vue'

defineOptions({ layout: PortalLayout })

defineProps({
  documents: { type: Array, default: () => [] },
})

const typeColour = {
  contract:    'bg-blue-100 text-blue-700',
  nda:         'bg-purple-100 text-purple-700',
  sla:         'bg-green-100 text-green-700',
  certificate: 'bg-yellow-100 text-yellow-700',
  other:       'bg-gray-100 text-gray-600',
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Documents</h1>
      <p class="text-sm text-gray-500 mt-1">Documents shared with you</p>
    </div>

    <div v-if="!documents.length"
         class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 px-6 py-16 text-center">
      <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
      </svg>
      <p class="text-sm text-gray-400">No documents available yet.</p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="doc in documents" :key="doc.id"
           class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5"
           :class="doc.is_expired ? 'border-red-200 dark:border-red-900/40' : ''">
        <div class="flex items-start justify-between gap-4">
          <div class="flex items-start gap-3 flex-1 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap mb-1">
                <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ doc.name }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                      :class="typeColour[doc.type] ?? 'bg-gray-100 text-gray-600'">
                  {{ doc.type_label }}
                </span>
                <span v-if="doc.is_expired"
                      class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">
                  Expired
                </span>
              </div>
              <div class="flex items-center gap-3 text-xs text-gray-400 flex-wrap">
                <span>{{ doc.file_name }}</span>
                <span>{{ doc.file_size }}</span>
                <span v-if="doc.expiry_date">
                  {{ doc.is_expired ? 'Expired' : 'Expires' }} {{ doc.expiry_date }}
                </span>
                <span>Added {{ doc.created_at }}</span>
              </div>
              <p v-if="doc.notes" class="text-xs text-gray-500 mt-1">{{ doc.notes }}</p>
            </div>
          </div>
          <a :href="`/portal/documents/${doc.id}/download`"
             class="flex-shrink-0 px-3 py-1.5 text-xs font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-gray-600 dark:text-gray-400 hover:text-primary hover:border-primary/30 transition-colors">
            Download
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
