<script setup>
import { computed }    from 'vue'
import { usePage }     from '@inertiajs/vue3'
import AppLayout       from '@shared/layouts/AppLayout.vue'
import Badge           from '@shared/components/display/Badge.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  modules: { type: Array, required: true }, // full registry: name, alias, description, is_enabled, is_licensed, is_core, order...
  appName: { type: String, required: true },
})

const page = usePage()
const user = computed(() => page.props.auth?.user)

// Only modules that are actually usable right now — licensed AND enabled.
// Core is always included since it's implicitly on.
const activeModules = computed(() =>
  [...props.modules]
    .filter(m => m.is_core || (m.is_licensed && m.is_enabled))
    .sort((a, b) => a.order - b.order)
)

const hasOptionalModules = computed(() => activeModules.value.some(m => !m.is_core))

// One quiet visual identity per module, keyed by alias — falls back to a
// generic mark for anything added later that isn't in this list yet.
const MODULE_ICONS = {
  core: 'M12 3l7 3v5c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6l7-3z',
  financial: 'M12 2v20M17 6.5c0-1.9-2.2-3-5-3s-5 1.5-5 3.5 2.2 3 5 3 5 1.1 5 3.5-2.2 3.5-5 3.5-5-1.1-5-3',
  hr: 'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM3 20c0-3.3 2.7-6 6-6s6 2.7 6 6M17 9a2.5 2.5 0 1 0 0-5M20.5 20c0-2.6-1.9-4.7-4.5-5.4',
  bookings: 'M4 9h16M7 3v3M17 3v3M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zM9 14l2 2 4-4',
  events: 'M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V7zM9 5v14',
}
const DEFAULT_ICON = 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z'

// Where "Open" should take you. Core has no landing page of its own —
// it lives inside Settings.
const MODULE_ROUTES = {
  core: '/dashboard',
  financial: '/financial',
  hr: '/hr',
  bookings: '/bookings',
  events: '/events',
}

function iconPath(mod)  { return MODULE_ICONS[mod.alias] ?? DEFAULT_ICON }
function moduleHref(mod) { return MODULE_ROUTES[mod.alias] ?? '#' }

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Good morning'
  if (h < 18) return 'Good afternoon'
  return 'Good evening'
})
</script>

<template>
  <div class="max-w-5xl">
    <!-- Header -->
    <div class="mb-8 flex items-start justify-between flex-wrap gap-3">
      <div>
        <h1 class="text-2xl font-bold text-app-text">
          {{ greeting }}, {{ user?.name ?? 'there' }}
        </h1>
        <p class="text-sm text-app-text/60 mt-1">
          Here's what's live on your {{ props.appName }} workspace today.
        </p>
      </div>
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400">
        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
        {{ activeModules.length }} module{{ activeModules.length === 1 ? '' : 's' }} active
      </span>
    </div>

    <!-- Module grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <a
        v-for="mod in activeModules"
        :key="mod.name"
        :href="moduleHref(mod)"
        class="group bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-5 flex flex-col gap-4 transition-colors hover:border-primary/40"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
               style="background-color: color-mix(in srgb, var(--color-primary) 12%, transparent);">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)"
                 stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
                 class="w-5 h-5">
              <path :d="iconPath(mod)" />
            </svg>
          </div>
          <Badge v-if="mod.is_core" type="info">Core</Badge>
        </div>

        <div class="flex-1">
          <h2 class="font-semibold text-app-text mb-1">{{ mod.name }}</h2>
          <p class="text-sm text-app-text/60 leading-relaxed">{{ mod.description }}</p>
        </div>

        <span class="text-xs font-medium text-primary inline-flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
          Open
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
               stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
            <path d="M5 12h14M13 6l6 6-6 6" />
          </svg>
        </span>
      </a>
    </div>

    <!-- Invitation to activate more, only shown when nothing beyond Core is on -->
    <div v-if="!hasOptionalModules"
         class="mt-4 bg-surface rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-6 text-center">
      <p class="text-sm text-app-text/60">
        Only Core is active right now. Head to Module Manager to turn on
        Financial, HR, Bookings, or Events for your licence.
      </p>
      <a href="/admin/modules"
         class="inline-block mt-3 text-sm font-medium text-primary hover:underline">
        Go to Module Manager
      </a>
    </div>
  </div>
</template>