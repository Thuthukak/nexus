<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: { type: String,  default: '' },
  label:      { type: String,  default: '' },
  placeholder:{ type: String,  default: '' },
  error:      { type: String,  default: '' },
  hint:       { type: String,  default: '' },
})

const emit    = defineEmits(['update:modelValue'])
const visible = ref(false)
const focused = ref(false)

// When blurred without changes, show masked placeholder
const displayValue = computed(() =>
  props.modelValue ? props.modelValue : ''
)

function onInput(e) {
  emit('update:modelValue', e.target.value)
}

function onFocus() {
  focused.value = true
  visible.value = true
}

function onBlur() {
  focused.value = false
  if (props.modelValue) visible.value = false
}
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" class="text-sm font-medium text-app-text">{{ label }}</label>
    <div class="relative">
      <input
        :type="visible ? 'text' : 'password'"
        :value="modelValue"
        :placeholder="placeholder || (visible ? '' : '••••••••••••••••')"
        @input="onInput"
        @focus="onFocus"
        @blur="onBlur"
        class="w-full px-3 py-2 pr-10 rounded-lg border bg-background text-app-text text-sm
               focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary
               transition-colors font-mono"
        :class="error
          ? 'border-red-400 focus:ring-red-400/50'
          : 'border-gray-300 dark:border-gray-600'"
      />
      <button
        type="button"
        @click="visible = !visible"
        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-app-text/30 hover:text-app-text/60 transition-colors"
        :title="visible ? 'Hide' : 'Show'"
      >
        <!-- Eye open -->
        <svg v-if="!visible" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <!-- Eye closed -->
        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
        </svg>
      </button>
    </div>
    <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
    <p v-else-if="hint" class="text-xs text-app-text/50">{{ hint }}</p>
  </div>
</template>
