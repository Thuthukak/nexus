<script setup>
import { TransitionGroup } from 'vue'
import Toast from './Toast.vue'
import { useToastStore } from '@shared/stores/useToastStore.js'

const toast = useToastStore()
</script>

<template>
  <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 pointer-events-none">
    <TransitionGroup
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-4 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-2 scale-95"
    >
      <Toast
        v-for="t in toast.toasts"
        :key="t.id"
        :id="t.id"
        :type="t.type"
        :title="t.title"
        :message="t.message"
        @dismiss="toast.dismiss"
      />
    </TransitionGroup>
  </div>
</template>
