<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue:  { type: String, default: '' },
  label:       { type: String, default: 'Password' },
  placeholder: { type: String, default: '' },
  error:       { type: String, default: null },
  showStrength:{ type: Boolean, default: false },
  autocomplete:{ type: String, default: 'current-password' },
})

const emit = defineEmits(['update:modelValue'])

const show = ref(false)

const rules = computed(() => [
  {
    id:   'length',
    text: 'At least 8 characters',
    met:  props.modelValue.length >= 8,
  },
  {
    id:   'letter',
    text: 'At least one letter (A–Z)',
    met:  /[A-Za-z]/.test(props.modelValue),
  },
  {
    id:   'number',
    text: 'At least one number (0–9)',
    met:  /[0-9]/.test(props.modelValue),
  },
  {
    id:   'special',
    text: 'At least one special character (@, #, !, %…)',
    met:  /[\W_]/.test(props.modelValue),
  },
])

const metCount = computed(() => rules.value.filter(r => r.met).length)

const strength = computed(() => {
  if (! props.modelValue) return null
  if (metCount.value <= 1) return { label: 'Weak',   colour: 'bg-red-500',    width: 'w-1/4' }
  if (metCount.value === 2) return { label: 'Fair',   colour: 'bg-orange-400', width: 'w-2/4' }
  if (metCount.value === 3) return { label: 'Good',   colour: 'bg-yellow-400', width: 'w-3/4' }
  return                           { label: 'Strong', colour: 'bg-green-500',  width: 'w-full' }
})

const allMet    = computed(() => metCount.value === 4)
const hasInput  = computed(() => props.modelValue.length > 0)
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" class="text-sm font-medium text-app-text">{{ label }}</label>

    <!-- Input with toggle -->
    <div class="relative">
      <input
        :value="modelValue"
        :type="show ? 'text' : 'password'"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
        @input="emit('update:modelValue', $event.target.value)"
        class="w-full px-3 py-2.5 pr-10 rounded-lg border text-sm
               focus:outline-none focus:ring-2 focus:ring-primary/50
               transition-colors"
        :class="error
          ? 'border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600'
          : allMet && hasInput
            ? 'border-green-400 dark:border-green-600 bg-background'
            : 'border-gray-300 dark:border-gray-600 bg-background'"
      />

      <!-- Show/hide toggle -->
      <button type="button"
              @click="show = !show"
              class="absolute right-3 top-1/2 -translate-y-1/2
                     text-app-text/40 hover:text-app-text/70 transition-colors">
        <!-- Eye open -->
        <svg v-if="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
               -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <!-- Eye closed -->
        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
               a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
               M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29
               m7.532 7.532l3.29 3.29M3 3l3.59 3.59
               m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7
               a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
        </svg>
      </button>
    </div>

    <!-- Backend error -->
    <p v-if="error" class="text-xs text-red-500 flex items-center gap-1">
      <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd"
          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
          clip-rule="evenodd" />
      </svg>
      {{ error }}
    </p>

    <!-- Strength meter + rules — only when showStrength is true -->
    <template v-if="showStrength && hasInput">
      <!-- Strength bar -->
      <div class="mt-1">
        <div class="flex items-center justify-between mb-1">
          <span class="text-xs text-app-text/50">Password strength</span>
          <span class="text-xs font-semibold"
                :class="{
                  'text-red-500':    strength?.label === 'Weak',
                  'text-orange-500': strength?.label === 'Fair',
                  'text-yellow-500': strength?.label === 'Good',
                  'text-green-600':  strength?.label === 'Strong',
                }">
            {{ strength?.label }}
          </span>
        </div>
        <div class="h-1.5 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all duration-300"
               :class="[strength?.colour, strength?.width]" />
        </div>
      </div>

      <!-- Rule checklist -->
      <ul class="mt-2 space-y-1">
        <li v-for="rule in rules" :key="rule.id"
            class="flex items-center gap-2 text-xs transition-colors"
            :class="rule.met ? 'text-green-600' : 'text-app-text/50'">
          <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="rule.met"
                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                  d="M5 13l4 4L19 7" />
            <path v-else
                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ rule.text }}
        </li>
      </ul>
    </template>
  </div>
</template>
