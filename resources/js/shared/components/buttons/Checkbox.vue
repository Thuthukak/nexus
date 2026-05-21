<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <button
    type="button"
    role="checkbox"
    :aria-checked="modelValue"
    @click="$emit('update:modelValue', !modelValue)"
    class="w-5 h-5 rounded flex items-center justify-center border-2 transition-all flex-shrink-0"
    :style="{
      borderColor: 'var(--color-primary)',
      backgroundColor: modelValue ? 'var(--color-primary)' : 'transparent',
    }"
  >
    <svg v-if="modelValue" class="w-3 h-3" viewBox="0 0 12 12" fill="none">
      <path d="M2 6l3 3 5-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </button>
</template>