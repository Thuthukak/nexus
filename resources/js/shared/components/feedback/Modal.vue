<script setup>
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
  show:     { type: Boolean, required: true },
  title:    { type: String,  default: '' },
  size:     { type: String,  default: 'md',
    validator: v => ['sm', 'md', 'lg', 'xl'].includes(v) },
  danger:   { type: Boolean, default: false },
  closeable:{ type: Boolean, default: true },
})

const emit = defineEmits(['close'])

function close() {
  if (props.closeable) emit('close')
}

function onKeydown(e) {
  if (e.key === 'Escape' && props.show) close()
}

onMounted(()  => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/50 backdrop-blur-sm"
          @click="close"
        />

        <!-- Panel -->
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition-all duration-150 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="show"
            class="relative bg-surface rounded-2xl shadow-xl w-full z-10"
            :class="{
              'max-w-sm':  size === 'sm',
              'max-w-md':  size === 'md',
              'max-w-lg':  size === 'lg',
              'max-w-2xl': size === 'xl',
            }"
          >
            <!-- Header -->
            <div
              v-if="title || $slots.header"
              class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800"
            >
              <slot name="header">
                <h2 class="text-base font-semibold" :class="danger ? 'text-red-600' : 'text-app-text'">
                  {{ title }}
                </h2>
              </slot>
              <button
                v-if="closeable"
                @click="close"
                class="p-1.5 rounded-lg text-app-text/30 hover:text-app-text/60 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-4">
              <slot />
            </div>

            <!-- Footer -->
            <div
              v-if="$slots.footer"
              class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-800"
            >
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
