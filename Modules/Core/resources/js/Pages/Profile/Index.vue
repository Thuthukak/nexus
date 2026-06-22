<script setup>
import { ref, computed } from 'vue'
import { useForm }       from '@inertiajs/vue3'
import AppLayout         from '@shared/layouts/AppLayout.vue'
import Badge             from '@shared/components/display/Badge.vue'
import PasswordInput     from '@shared/components/form/PasswordInput.vue'
import Input             from '@shared/components/form/Input.vue'
import Button            from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  user:           { type: Object,  required: true },
  employee:       { type: Object,  default: null },
  payslips:       { type: Array,   default: () => [] },
  documents:      { type: Object,  default: () => [] },
  leaveHistory:   { type: Array,   default: () => [] },
  enrollments:    { type: Array,   default: () => [] },
  ticketOrders:   { type: Array,   default: () => [] },
  recentActivity: { type: Array,   default: () => [] },
  modules:        { type: Object,  default: () => ({}) },
})

// ── Navigation tabs ───────────────────────────────────────────
const tabs = computed(() => {
  const t = [
    { key: 'overview',  label: 'Overview',  icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { key: 'details',   label: 'Details',   icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { key: 'security',  label: 'Security',  icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
  ]

  if (props.modules.hr && props.employee) {
    t.push({ key: 'payslips',  label: 'Payslips',  icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', badge: props.payslips.length })
    t.push({ key: 'documents', label: 'Documents', icon: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', badge: props.documents.length })
    t.push({ key: 'leave',     label: 'Leave',     icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', badge: props.leaveHistory.length })
  }

  if (props.modules.lms && props.enrollments.length > 0) {
    t.push({ key: 'learning', label: 'Learning', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', badge: props.enrollments.length })
  }

  if (props.modules.events && props.ticketOrders.length > 0) {
    t.push({ key: 'tickets', label: 'My Tickets', icon: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', badge: props.ticketOrders.length })
  }

  return t
})

const activeTab = ref('overview')

// ── Profile form ──────────────────────────────────────────────
const profileForm = useForm({
  name:  props.user.name,
  email: props.user.email,
})

function updateProfile() {
  profileForm.patch('/profile')
}

// ── Password form ─────────────────────────────────────────────
const passwordForm = useForm({
  current_password:      '',
  password:              '',
  password_confirmation: '',
})
const confirmTouched = ref(false)
const confirmError = computed(() => {
  if (! confirmTouched.value || ! passwordForm.password_confirmation) return null
  if (passwordForm.password !== passwordForm.password_confirmation) return 'Passwords do not match.'
  return passwordForm.errors.password_confirmation ?? null
})

function updatePassword() {
  confirmTouched.value = true
  if (passwordForm.password !== passwordForm.password_confirmation) return
  passwordForm.patch('/profile/password', {
    onSuccess: () => { passwordForm.reset(); confirmTouched.value = false },
  })
}

// ── Helpers ───────────────────────────────────────────────────
function currency(val) {
  if (! val) return '—'
  return 'R ' + Number(val).toLocaleString('en-ZA', { minimumFractionDigits: 2 })
}

const leaveStatusColour = {
  approved: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
  pending:  'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
  rejected: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  cancelled:'bg-gray-100 text-gray-500 dark:bg-gray-800',
}

const initials = computed(() =>
  props.user.name
    .split(' ')
    .map(w => w[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
)
</script>

<template>
  <div class="max-w-6xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">My Profile</h1>
      <p class="text-sm text-app-text/60 mt-1">Manage your account details</p>
    </div>
    <div class="flex flex-col lg:flex-row gap-6">

      <!-- ── LEFT SIDEBAR ────────────────────────────────────── -->
      <aside class="lg:w-64 flex-shrink-0">

        <!-- Avatar card -->
        <div class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 p-6 text-center mb-4">
          <!-- Avatar -->
          <div class="w-20 h-20 rounded-2xl bg-primary flex items-center justify-center mx-auto mb-3">
            <span class="text-3xl font-bold text-primary-text">{{ initials }}</span>
          </div>

          <h2 class="font-bold text-app-text text-lg leading-tight">{{ user.name }}</h2>
          <p class="text-sm text-app-text/50 mt-0.5 truncate">{{ user.email }}</p>

          <div class="flex flex-wrap gap-1.5 justify-center mt-3">
            <span v-for="role in user.roles" :key="role"
                  class="text-xs px-2.5 py-1 rounded-full font-semibold
                          bg-primary/10 text-primary">
              {{ role }}
            </span>
          </div>

          <!-- Employee info if applicable -->
          <div v-if="employee"
                class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-left space-y-1.5">
            <div v-if="employee.department" class="flex items-center gap-2 text-xs text-app-text/60">
              <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              {{ employee.department }}
            </div>
            <div v-if="employee.job_title" class="flex items-center gap-2 text-xs text-app-text/60">
              <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              {{ employee.job_title }}
            </div>
            <div v-if="employee.number" class="flex items-center gap-2 text-xs text-app-text/60">
              <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
              </svg>
              {{ employee.number }}
            </div>
          </div>

          <!-- Account meta -->
          <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-left space-y-1">
            <div class="flex items-center gap-2 text-xs text-app-text/40">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Member since {{ user.created_at }}
            </div>
            <div v-if="user.last_login_at" class="flex items-center gap-2 text-xs text-app-text/40">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              Last login {{ user.last_login_at }}
            </div>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 p-2 space-y-0.5">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm
                    font-medium transition-all text-left"
            :class="activeTab === tab.key
              ? 'bg-primary/10 text-primary'
              : 'text-app-text/60 hover:text-app-text hover:bg-gray-50 dark:hover:bg-gray-800/50'"
          >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="tab.icon" />
            </svg>
            <span class="flex-1 truncate">{{ tab.label }}</span>
            <span v-if="tab.badge"
                  class="text-xs font-bold px-1.5 py-0.5 rounded-full"
                  :class="activeTab === tab.key
                    ? 'bg-primary/20 text-primary'
                    : 'bg-gray-100 dark:bg-gray-800 text-app-text/50'">
              {{ tab.badge }}
            </span>
          </button>

          <!-- Notification preferences link -->
          <a href="/profile/notifications"
              class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm
                    font-medium transition-all text-app-text/60
                    hover:text-app-text hover:bg-gray-50 dark:hover:bg-gray-800/50">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            Notifications
          </a>
        </nav>
      </aside>

      <!-- ── RIGHT CONTENT ───────────────────────────────────── -->
      <main class="flex-1 min-w-0 space-y-5">

        <!-- ═══ OVERVIEW ════════════════════════════════════ -->
        <template v-if="activeTab === 'overview'">

          <!-- Stats row -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div v-if="modules.hr && employee"
                  class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 px-4 py-4 text-center">
              <p class="text-2xl font-bold text-app-text">{{ payslips.length }}</p>
              <p class="text-xs text-app-text/50 mt-1">Payslips</p>
            </div>
            <div v-if="modules.hr && employee"
                  class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 px-4 py-4 text-center">
              <p class="text-2xl font-bold text-app-text">{{ documents.length }}</p>
              <p class="text-xs text-app-text/50 mt-1">Documents</p>
            </div>
            <div v-if="modules.lms"
                  class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 px-4 py-4 text-center">
              <p class="text-2xl font-bold text-green-600">
                {{ enrollments.filter(e => e.status === 'completed').length }}
              </p>
              <p class="text-xs text-app-text/50 mt-1">Courses Done</p>
            </div>
            <div v-if="modules.events"
                  class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 px-4 py-4 text-center">
              <p class="text-2xl font-bold text-blue-600">{{ ticketOrders.length }}</p>
              <p class="text-xs text-app-text/50 mt-1">Ticket Orders</p>
            </div>
          </div>

          <!-- Active courses preview -->
          <div v-if="modules.lms && enrollments.length"
                class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
              <h2 class="text-sm font-semibold text-app-text">My Learning</h2>
              <button 
                @click="activeTab = 'learning'"
                class="text-xs text-primary hover:underline">
                View all →
              </button>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-800">
              <div v-for="e in enrollments.slice(0, 3)" :key="e.id"
                    class="flex items-center gap-4 px-5 py-3">
                <div class="w-10 h-10 rounded-xl flex-shrink-0 overflow-hidden
                      bg-gradient-to-br from-primary/20 to-primary/5">
                  <img v-if="e.thumbnail_url" :src="e.thumbnail_url"
                      class="w-full h-full object-cover" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-app-text truncate">{{ e.course_title }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <div class="flex-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                      <div class="h-full bg-primary rounded-full"
                            :style="{ width: e.progress + '%' }" />
                    </div>
                    <span class="text-xs text-app-text/50 flex-shrink-0">{{ e.progress }}%</span>
                  </div>
                </div>
                <a :href="`/student/learn/${e.id}`"
                    class="text-xs text-primary hover:underline flex-shrink-0">
                  {{ e.status === 'completed' ? 'Review' : 'Continue' }}
                </a>
              </div>
            </div>
          </div>

          <!-- Recent activity -->
          <div class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
              <h2 class="text-sm font-semibold text-app-text">Recent Activity</h2>
            </div>
            <div v-if="!recentActivity.length"
                  class="px-5 py-8 text-center text-sm text-app-text/40">
              No activity recorded yet.
            </div>
            <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
              <div v-for="(a, i) in recentActivity" :key="i"
                    class="flex items-start gap-3 px-5 py-3">
                <div class="w-1.5 h-1.5 rounded-full bg-primary/40 flex-shrink-0 mt-1.5" />
                <div class="flex-1 min-w-0">
                  <p class="text-sm text-app-text">{{ a.description }}</p>
                  <p class="text-xs text-app-text/40 mt-0.5">{{ a.created_at }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800
                            text-app-text/50 capitalize flex-shrink-0">
                  {{ a.log_name }}
                </span>
              </div>
            </div>
          </div>
        </template>

        <!-- ═══ PERSONAL DETAILS ════════════════════════════ -->
        <template v-if="activeTab === 'details'">
          <div class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 p-6">
            <h2 class="text-base font-semibold text-app-text mb-5">Personal Details</h2>
            <form @submit.prevent="updateProfile" class="space-y-4 max-w-md">
              <Input 
                v-model="profileForm.name"
                label="Full Name"
                required
                :error="profileForm.errors.name"
              />
              <Input 
                v-model="profileForm.email"
                label="Email Address"
                type="email"
                required
                :error="profileForm.errors.email"
                disabled
              />
              <div class="pt-2">
                <Button 
                  type="submit" 
                  :loading="profileForm.processing">
                  Save Changes
                </Button>
              </div>
            </form>
          </div>
        </template>

        <!-- ═══ SECURITY ════════════════════════════════════ -->
        <template v-if="activeTab === 'security'">
          <div class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 p-6">
            <h2 class="text-base font-semibold text-app-text mb-1">Change Password</h2>
            <p class="text-sm text-app-text/50 mb-5">
              Choose a strong password with at least 8 characters,
              one number and one special character.
            </p>
            <form @submit.prevent="updatePassword" class="space-y-4 max-w-md" novalidate>
              <PasswordInput
                v-model="passwordForm.current_password"
                label="Current Password"
                autocomplete="current-password"
                :error="passwordForm.errors.current_password"
              />
              <PasswordInput
                v-model="passwordForm.password"
                label="New Password"
                autocomplete="new-password"
                :show-strength="true"
                :error="passwordForm.errors.password"
              />
              <PasswordInput
                v-model="passwordForm.password_confirmation"
                label="Confirm New Password"
                autocomplete="new-password"
                :show-strength="false"
                :error="confirmError"
                @blur="confirmTouched = true"
              />
              <div class="pt-2">
                <Button type="submit" :loading="passwordForm.processing">
                  Update Password
                </Button>
              </div>
            </form>
          </div>

          <!-- Account info card -->
          <div class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 p-6">
            <h2 class="text-base font-semibold text-app-text mb-4">Account Info</h2>
            <dl class="space-y-3">
              <div class="flex items-center justify-between">
                <dt class="text-sm text-app-text/60">Email verified</dt>
                <dd class="text-sm">
                  <span v-if="user.email_verified_at"
                        class="text-green-600 font-medium">
                    ✓ Verified {{ user.email_verified_at }}
                  </span>
                  <span v-else class="text-orange-500 font-medium">Not verified</span>
                </dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-sm text-app-text/60">Account created</dt>
                <dd class="text-sm font-medium text-app-text">{{ user.created_at }}</dd>
              </div>
              <div class="flex items-center justify-between">
                <dt class="text-sm text-app-text/60">Last login</dt>
                <dd class="text-sm font-medium text-app-text">
                  {{ user.last_login_at ?? 'Never' }}
                </dd>
              </div>
            </dl>
          </div>
        </template>

        <!-- ═══ PAYSLIPS ════════════════════════════════════ -->
        <template v-if="activeTab === 'payslips'">
          <div class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
              <h2 class="text-base font-semibold text-app-text">My Payslips</h2>
              <p class="text-sm text-app-text/50 mt-0.5">Your payslip history</p>
            </div>
            <div v-if="!payslips.length"
                  class="px-5 py-12 text-center">
              <svg class="w-10 h-10 text-app-text/20 mx-auto mb-3"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p class="text-sm text-app-text/40">No payslips available yet.</p>
            </div>
            <div v-else>
              <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
                  <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Period</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Gross</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Net</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-app-text/50">Date</th>
                    <th class="px-5 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                  <tr v-for="slip in payslips" :key="slip.id"
                      class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                    <td class="px-5 py-3.5 font-semibold text-app-text">{{ slip.period_label }}</td>
                    <td class="px-5 py-3.5 text-right text-app-text/70">{{ currency(slip.gross_amount) }}</td>
                    <td class="px-5 py-3.5 text-right font-medium text-app-text">{{ currency(slip.net_amount) }}</td>
                    <td class="px-5 py-3.5 text-right text-xs text-app-text/40">{{ slip.created_at }}</td>
                    <td class="px-5 py-3.5 text-right">
                      <a :href="`/profile/payslips/${slip.id}/download`"
                          class="text-xs font-medium text-primary hover:underline inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <!-- ═══ DOCUMENTS ═══════════════════════════════════ -->
        <template v-if="activeTab === 'documents'">
          <div class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
              <h2 class="text-base font-semibold text-app-text">My Documents</h2>
              <p class="text-sm text-app-text/50 mt-0.5">Documents on file for you</p>
            </div>
            <div v-if="!documents.length"
                  class="px-5 py-12 text-center">
              <svg class="w-10 h-10 text-app-text/20 mx-auto mb-3"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              <p class="text-sm text-app-text/40">No documents on file.</p>
            </div>
            <div v-else class="divide-y divide-gray-50 dark:divide-gray-800">
              <div v-for="doc in documents" :key="doc.id"
                    class="flex items-center gap-4 px-5 py-4">
                <!-- Doc icon -->
                <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800
                            flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-app-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-medium text-app-text">{{ doc.name }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-app-text/50">
                      {{ doc.type_label }}
                    </span>
                    <span v-if="doc.is_expired"
                          class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                      Expired
                    </span>
                  </div>
                  <div class="flex items-center gap-3 mt-0.5 text-xs text-app-text/40">
                    <span>{{ doc.file_name }}</span>
                    <span>{{ doc.file_size }}</span>
                    <span v-if="doc.expiry_date">
                      {{ doc.is_expired ? 'Expired' : 'Expires' }} {{ doc.expiry_date }}
                    </span>
                  </div>
                </div>
                <a :href="`/profile/documents/${doc.id}/download`"
                    class="text-xs font-medium text-primary hover:underline flex-shrink-0
                          inline-flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Download
                </a>
              </div>
            </div>
          </div>
        </template>

        <!-- ═══ LEAVE ════════════════════════════════════════ -->
        <template v-if="activeTab === 'leave'">
          <div class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
              <h2 class="text-base font-semibold text-app-text">Leave History</h2>
            </div>
            <div v-if="!leaveHistory.length"
                  class="px-5 py-12 text-center text-sm text-app-text/40">
              No leave history.
            </div>
            <div v-else>
              <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
                  <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Type</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">From</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">To</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-app-text/50">Days</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                  <tr v-for="leave in leaveHistory" :key="leave.id"
                      class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                    <td class="px-5 py-3.5 font-medium text-app-text">{{ leave.type }}</td>
                    <td class="px-5 py-3.5 text-app-text/70">{{ leave.start_date }}</td>
                    <td class="px-5 py-3.5 text-app-text/70">{{ leave.end_date }}</td>
                    <td class="px-5 py-3.5 text-center font-medium text-app-text">{{ leave.days }}</td>
                    <td class="px-5 py-3.5">
                      <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize"
                            :class="leaveStatusColour[leave.status] ?? 'bg-gray-100 text-gray-500'">
                        {{ leave.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <!-- ═══ LEARNING ═════════════════════════════════════ -->
        <template v-if="activeTab === 'learning'">
          <div v-if="!enrollments.length"
                class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800
                      px-5 py-12 text-center text-sm text-app-text/40">
            You are not enrolled in any courses.
          </div>
          <div v-else class="space-y-4">
            <div v-for="e in enrollments" :key="e.id"
                  class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
              <div class="flex items-stretch gap-0">
                <!-- Thumbnail -->
                <div class="w-24 flex-shrink-0 bg-gradient-to-br from-primary/30 to-primary/10 relative overflow-hidden">
                  <img v-if="e.thumbnail_url" :src="e.thumbnail_url"
                        class="absolute inset-0 w-full h-full object-cover" />
                </div>
                <div class="flex-1 p-4 min-w-0">
                  <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                      <p class="font-semibold text-app-text text-sm">{{ e.course_title }}</p>
                      <p class="text-xs text-app-text/50 mt-0.5">{{ e.cohort_name }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                      <span v-if="e.has_certificate"
                            class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-medium">
                        🏆 Certified
                      </span>
                      <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                            :class="e.status === 'completed'
                              ? 'bg-green-100 text-green-700'
                              : 'bg-blue-100 text-blue-700'">
                        {{ e.status }}
                      </span>
                    </div>
                  </div>
                  <!-- Progress -->
                  <div class="flex items-center gap-3 mb-2">
                    <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                      <div class="h-full bg-primary rounded-full transition-all"
                            :style="{ width: e.progress + '%' }" />
                    </div>
                    <span class="text-xs font-medium text-app-text/60 flex-shrink-0">
                      {{ e.progress }}%
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-xs text-app-text/40">Enrolled {{ e.enrolled_at }}</span>
                    <div class="flex items-center gap-2">
                      <a v-if="e.has_certificate"
                          :href="`/student/learn/${e.id}/certificate`"
                          class="text-xs text-yellow-600 hover:underline font-medium">
                        Certificate ↓
                      </a>
                      <a :href="`/student/learn/${e.id}`"
                          class="text-xs font-semibold text-white px-3 py-1 rounded-lg"
                          style="background-color: var(--color-primary);">
                        {{ e.status === 'completed' ? 'Review' : 'Continue' }} →
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- ═══ TICKETS ══════════════════════════════════════ -->
        <template v-if="activeTab === 'tickets'">
          <div v-if="!ticketOrders.length"
                class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800
                      px-5 py-12 text-center text-sm text-app-text/40">
            No ticket orders yet.
          </div>
          <div v-else class="space-y-4">
            <div v-for="order in ticketOrders" :key="order.id"
                  class="bg-surface rounded-2xl border border-gray-100 dark:border-gray-800 p-5">
              <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                  <p class="font-semibold text-app-text">{{ order.event_title }}</p>
                  <div class="flex items-center gap-3 mt-1 text-xs text-app-text/50">
                    <span>📅 {{ order.event_date }}</span>
                    <span v-if="order.event_venue">📍 {{ order.event_venue }}</span>
                  </div>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="font-bold text-app-text">R {{ Number(order.total).toLocaleString('en-ZA', {minimumFractionDigits: 2}) }}</p>
                  <p class="text-xs text-app-text/40 mt-0.5">{{ order.tickets_count }} ticket(s)</p>
                </div>
              </div>
              <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-800">
                <span class="text-xs font-mono text-app-text/50">{{ order.reference }}</span>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-app-text/40">Paid {{ order.paid_at }}</span>
                  <a :href="`/events-admin/events/${order.event_id}/orders/${order.id}/download`"
                      class="text-xs font-medium text-primary hover:underline
                            inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Tickets
                  </a>
                </div>
              </div>
            </div>
          </div>
        </template>
      </main>
    </div>
  </div>
</template>
