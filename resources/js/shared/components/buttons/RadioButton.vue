<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <button
    type="button"
    role="radio"
    :aria-checked="modelValue"
    @click="$emit('update:modelValue', !modelValue)"
    class="w-5 h-5 rounded-full flex items-center justify-center border-2 transition-all flex-shrink-0"
    :style="{
      borderColor: 'var(--color-primary)',
      backgroundColor: 'transparent',
    }"
  >
    <span
      v-if="modelValue"
      class="w-2.5 h-2.5 rounded-full"
      :style="{ backgroundColor: 'var(--color-primary)' }"
    />
  </button>
</template>