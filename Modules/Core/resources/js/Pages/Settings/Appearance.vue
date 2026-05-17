<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useForm }   from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import Button        from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  theme: { type: Object, required: true },
})

const form = useForm({ ...props.theme })

// Live preview state — separate from the form
const preview = reactive({ ...props.theme })

// Keep preview in sync with form changes
watch(form, (val) => {
  Object.assign(preview, {
    primary:      val.primary,
    primary_text: val.primary_text,
    secondary:    val.secondary,
    accent:       val.accent,
    sidebar_bg:   val.sidebar_bg,
    sidebar_text: val.sidebar_text,
    surface:      val.surface,
    background:   val.background,
    text:         val.text,
  })
}, { deep: true })

function submit() {
  form.patch('/settings/appearance')
}

// WCAG contrast ratio utility
function getLuminance(hex) {
  const rgb = parseInt(hex.replace('#', ''), 16)
  const r   = ((rgb >> 16) & 255) / 255
  const g   = ((rgb >>  8) & 255) / 255
  const b   = ((rgb >>  0) & 255) / 255
  const toLinear = c => c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4)
  return 0.2126 * toLinear(r) + 0.7152 * toLinear(g) + 0.0722 * toLinear(b)
}

function contrastRatio(hex1, hex2) {
  const l1 = getLuminance(hex1)
  const l2 = getLuminance(hex2)
  return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05)
}

const contrastWarnings = computed(() => {
  const warnings = []
  const pairs = [
    { label: 'Primary text on primary bg', fg: form.primary_text, bg: form.primary },
    { label: 'Body text on background',    fg: form.text,         bg: form.background },
    { label: 'Sidebar text on sidebar bg', fg: form.sidebar_text, bg: form.sidebar_bg },
  ]
  pairs.forEach(({ label, fg, bg }) => {
    try {
      const ratio = contrastRatio(fg, bg)
      if (ratio < 4.5) warnings.push({ label, ratio: ratio.toFixed(1) })
    } catch {}
  })
  return warnings
})

const tokens = [
  { key: 'primary',      label: 'Primary',           hint: 'Main brand colour — buttons, links, active nav' },
  { key: 'primary_text', label: 'Primary Text',       hint: 'Text colour on primary backgrounds' },
  { key: 'secondary',    label: 'Secondary',          hint: 'Supporting accent — badges, secondary buttons' },
  { key: 'accent',       label: 'Accent',             hint: 'Call-to-action highlights and featured elements' },
  { key: 'sidebar_bg',   label: 'Sidebar Background', hint: 'Sidebar panel background colour' },
  { key: 'sidebar_text', label: 'Sidebar Text',       hint: 'Navigation item text in the sidebar' },
  { key: 'surface',      label: 'Surface',            hint: 'Card and panel backgrounds' },
  { key: 'background',   label: 'Page Background',    hint: 'Main page background colour' },
  { key: 'text',         label: 'Body Text',          hint: 'Default text colour throughout the app' },
]

const presets = [
  {
    label: 'Ocean Blue',
    values: { primary: '#1E3A5F', primary_text: '#FFFFFF', secondary: '#2E86AB', accent: '#F39C12', sidebar_bg: '#1E3A5F', sidebar_text: '#CBD5E1', surface: '#FFFFFF', background: '#F8F9FA', text: '#2C3E50' },
  },
  {
    label: 'Forest Green',
    values: { primary: '#1A4731', primary_text: '#FFFFFF', secondary: '#2D6A4F', accent: '#F4A261', sidebar_bg: '#1A4731', sidebar_text: '#A8D5B5', surface: '#FFFFFF', background: '#F4F7F4', text: '#1B2D22' },
  },
  {
    label: 'Corporate Grey',
    values: { primary: '#374151', primary_text: '#FFFFFF', secondary: '#6B7280', accent: '#3B82F6', sidebar_bg: '#1F2937', sidebar_text: '#9CA3AF', surface: '#FFFFFF', background: '#F9FAFB', text: '#111827' },
  },
  {
    label: 'Warm Terracotta',
    values: { primary: '#9B2335', primary_text: '#FFFFFF', secondary: '#C0392B', accent: '#E67E22', sidebar_bg: '#7B1D1D', sidebar_text: '#FECACA', surface: '#FFFFFF', background: '#FEF9F5', text: '#3D1515' },
  },
]

function applyPreset(preset) {
  Object.assign(form, preset.values)
}
</script>

<template>
  <div class="max-w-4xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">Appearance</h1>
      <p class="text-sm text-app-text/60 mt-1">
        Customise the colour palette for your Nexus platform
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Controls -->
      <div class="lg:col-span-2 space-y-6">

        <!-- Presets -->
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">
            Preset Palettes
          </h2>
          <div class="grid grid-cols-2 gap-3">
            <button
              v-for="preset in presets"
              :key="preset.label"
              @click="applyPreset(preset)"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary/40 hover:bg-primary/5 transition-colors text-left"
            >
              <div class="flex gap-1">
                <span class="w-4 h-4 rounded-full border border-white/20 shadow-sm"
                      :style="{ backgroundColor: preset.values.primary }" />
                <span class="w-4 h-4 rounded-full border border-white/20 shadow-sm"
                      :style="{ backgroundColor: preset.values.accent }" />
                <span class="w-4 h-4 rounded-full border border-white/20 shadow-sm"
                      :style="{ backgroundColor: preset.values.secondary }" />
              </div>
              <span class="text-sm font-medium text-app-text">{{ preset.label }}</span>
            </button>
          </div>
        </div>

        <!-- Colour tokens -->
        <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
          <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">
            Colour Tokens
          </h2>
          <div class="space-y-4">
            <div v-for="token in tokens" :key="token.key"
                 class="flex items-center gap-4">
              <div class="relative flex-shrink-0">
                <input
                  v-model="form[token.key]"
                  type="color"
                  class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5"
                />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-app-text">{{ token.label }}</p>
                <p class="text-xs text-app-text/50">{{ token.hint }}</p>
              </div>
              <code class="text-xs text-app-text/40 font-mono">{{ form[token.key] }}</code>
            </div>
          </div>
        </div>

        <!-- Contrast warnings -->
        <div
          v-if="contrastWarnings.length"
          class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4"
        >
          <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-400 mb-2">
            Accessibility Warnings
          </h3>
          <ul class="space-y-1">
            <li
              v-for="w in contrastWarnings"
              :key="w.label"
              class="text-xs text-yellow-700 dark:text-yellow-500"
            >
              {{ w.label }} — contrast ratio {{ w.ratio }}:1
              (minimum 4.5:1 for WCAG AA)
            </li>
          </ul>
        </div>

        <!-- Save -->
        <div class="flex justify-end">
          <Button @click="submit" :loading="form.processing">
            Save Palette
          </Button>
        </div>
      </div>

      <!-- Live preview -->
      <div class="lg:col-span-1">
        <div class="sticky top-6">
          <p class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-3">
            Live Preview
          </p>

          <!-- Mini shell preview -->
          <div
            class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm"
            style="height: 420px; display: flex;"
          >
            <!-- Mini sidebar -->
            <div
              class="w-16 flex flex-col py-3 gap-2 px-2"
              :style="{ backgroundColor: preview.sidebar_bg }"
            >
              <div class="h-2 w-8 rounded mx-auto mb-2 opacity-60"
                   :style="{ backgroundColor: preview.sidebar_text }" />
              <div v-for="i in 4" :key="i"
                   class="h-6 w-6 rounded mx-auto opacity-40"
                   :style="{ backgroundColor: i === 1 ? preview.primary_text : preview.sidebar_text }" />
            </div>

            <!-- Mini content -->
            <div class="flex-1 flex flex-col" :style="{ backgroundColor: preview.background }">
              <!-- Mini topbar -->
              <div class="h-8 border-b flex items-center px-3 gap-2"
                   :style="{ backgroundColor: preview.surface, borderColor: '#e5e7eb' }">
                <div class="w-12 h-2 rounded" :style="{ backgroundColor: preview.text + '30' }" />
                <div class="ml-auto w-5 h-5 rounded-full"
                     :style="{ backgroundColor: preview.primary }" />
              </div>

              <!-- Mini content area -->
              <div class="flex-1 p-3 space-y-2">
                <!-- Stat cards -->
                <div class="grid grid-cols-2 gap-1.5">
                  <div v-for="i in 4" :key="i"
                       class="rounded-lg p-2"
                       :style="{ backgroundColor: preview.surface }">
                    <div class="h-1.5 w-8 rounded mb-1.5"
                         :style="{ backgroundColor: preview.text + '30' }" />
                    <div class="h-2.5 w-6 rounded"
                         :style="{ backgroundColor: preview.text + '60' }" />
                  </div>
                </div>

                <!-- Primary button preview -->
                <div class="rounded-lg p-2 flex items-center gap-2"
                     :style="{ backgroundColor: preview.surface }">
                  <div class="h-5 px-3 rounded flex items-center"
                       :style="{ backgroundColor: preview.primary }">
                    <span class="text-xs font-medium"
                          :style="{ color: preview.primary_text, fontSize: '8px' }">
                      Button
                    </span>
                  </div>
                  <div class="h-4 w-12 rounded"
                       :style="{ backgroundColor: preview.secondary + '30' }" />
                  <div class="h-3 w-8 rounded ml-auto"
                       :style="{ backgroundColor: preview.accent + '60' }" />
                </div>

                <!-- Mini table -->
                <div class="rounded-lg overflow-hidden"
                     :style="{ backgroundColor: preview.surface }">
                  <div v-for="i in 3" :key="i"
                       class="flex items-center gap-2 px-2 py-1.5 border-b"
                       :style="{ borderColor: preview.text + '10' }">
                    <div class="h-1.5 rounded flex-1"
                         :style="{ backgroundColor: preview.text + '20' }" />
                    <div class="h-3 w-8 rounded"
                         :style="{ backgroundColor: i === 1 ? '#10B981' + '30' : preview.accent + '30' }" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
