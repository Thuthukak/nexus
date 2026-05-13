<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  label:       { type: String, default: '' },
  type:        { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  error:       { type: String, default: '' },
  hint:        { type: String, default: '' },
  disabled:    { type: Boolean, default: false },
  required:    { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" class="text-sm font-medium text-app-text">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>
    <input
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      @input="$emit('update:modelValue', $event.target.value)"
      class="w-full px-3 py-2 rounded-lg border text-sm bg-background text-app-text
             focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary
             disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      :class="error
        ? 'border-red-400 focus:ring-red-400/50 focus:border-red-400'
        : 'border-gray-300 dark:border-gray-600'"
    />
    <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
    <p v-else-if="hint" class="text-xs text-app-text/50">{{ hint }}</p>
  </div>
</template>
