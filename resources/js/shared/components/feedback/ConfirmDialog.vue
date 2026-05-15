<script setup>
import Modal from './Modal.vue'
import Button from '@shared/components/buttons/Button.vue'

defineProps({
  show:          { type: Boolean, required: true },
  title:         { type: String,  default: 'Are you sure?' },
  message:       { type: String,  default: 'This action cannot be undone.' },
  confirmLabel:  { type: String,  default: 'Confirm' },
  cancelLabel:   { type: String,  default: 'Cancel' },
  danger:        { type: Boolean, default: false },
  loading:       { type: Boolean, default: false },
})

defineEmits(['confirm', 'cancel'])
</script>

<template>
  <Modal :show="show" :title="title" :danger="danger" size="sm" @close="$emit('cancel')">
    <p class="text-sm text-app-text/70 leading-relaxed">{{ message }}</p>

    <template #footer>
      <button
        @click="$emit('cancel')"
        class="px-4 py-2 text-sm font-medium text-app-text/60 hover:text-app-text transition-colors"
      >
        {{ cancelLabel }}
      </button>
      <Button
        :variant="danger ? 'danger' : 'primary'"
        :loading="loading"
        @click="$emit('confirm')"
      >
        {{ confirmLabel }}
      </Button>
    </template>
  </Modal>
</template>
