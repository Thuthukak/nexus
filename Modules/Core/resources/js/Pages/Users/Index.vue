<script setup>
import { ref }           from 'vue'
import { router }        from '@inertiajs/vue3'
import AppLayout         from '@shared/layouts/AppLayout.vue'
import DataTable         from '@shared/components/data/DataTable.vue'
import Badge             from '@shared/components/display/Badge.vue'
import ConfirmDialog     from '@shared/components/feedback/ConfirmDialog.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  users:   { type: Array,  default: () => [] },
  roles:   { type: Array,  default: () => [] },
  filters: { type: Object, default: () => ({}) },
  stats:   { type: Object, default: () => ({}) },
})

const columns = [
  { key: 'name',          label: 'Name',       sortable: true },
  { key: 'email',         label: 'Email',      sortable: true },
  { key: 'roles',         label: 'Role',       sortable: false },
  { key: 'last_login_at', label: 'Last Login', sortable: false },
  { key: 'is_active',     label: 'Status',     sortable: true },
  { key: 'actions',       label: '',           sortable: false },
]

const search        = ref(props.filters.search ?? '')
const filterRole    = ref(props.filters.role   ?? '')
const filterStatus  = ref(props.filters.status ?? '')

function applyFilters() {
  router.get('/users', {
    search: search.value       || undefined,
    role:   filterRole.value   || undefined,
    status: filterStatus.value || undefined,
  }, { preserveState: true, replace: true })
}

function clearFilters() {
  search.value       = ''
  filterRole.value   = ''
  filterStatus.value = ''
  router.get('/users', {}, { preserveState: true, replace: true })
}

// Actions
const confirmDeactivate = ref(false)
const confirmDelete     = ref(false)
const targetUser        = ref(null)
const actionLoading     = ref(false)

function promptDeactivate(user) {
  targetUser.value        = user
  confirmDeactivate.value = true
}

function promptDelete(user) {
  targetUser.value    = user
  confirmDelete.value = true
}

function handleDeactivate() {
  actionLoading.value = true
  router.patch(`/users/${targetUser.value.id}/deactivate`, {}, {
    onFinish: () => {
      actionLoading.value     = false
      confirmDeactivate.value = false
    },
  })
}

function handleDelete() {
  actionLoading.value = true
  router.delete(`/users/${targetUser.value.id}`, {}, {
    onFinish: () => {
      actionLoading.value = false
      confirmDelete.value = false
    },
  })
}

function activate(user) {
  router.patch(`/users/${user.id}/activate`)
}

function resetPassword(user) {
  router.post(`/users/${user.id}/reset-password`)
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Users</h1>
        <p class="text-sm text-app-text/60 mt-1">Manage team members and their access</p>
      </div>
      <a href="/users/create"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add User
      </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Total Users</p>
        <p class="text-2xl font-bold text-app-text">{{ stats.total }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Active</p>
        <p class="text-2xl font-bold text-green-600">{{ stats.active }}</p>
      </div>
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 px-4 py-3">
        <p class="text-xs text-app-text/50 mb-1">Inactive</p>
        <p class="text-2xl font-bold text-app-text/40">{{ stats.inactive }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-4 mb-4 flex flex-wrap items-end gap-3">
      <div class="flex-1 min-w-44">
        <label class="text-xs font-medium text-app-text/50 mb-1 block">Search</label>
        <input v-model="search" @keyup.enter="applyFilters"
               placeholder="Name or email…"
               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
      </div>
      <div>
        <label class="text-xs font-medium text-app-text/50 mb-1 block">Role</label>
        <select v-model="filterRole"
                class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
          <option value="">All roles</option>
          <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
        </select>
      </div>
      <div>
        <label class="text-xs font-medium text-app-text/50 mb-1 block">Status</label>
        <select v-model="filterStatus"
                class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50">
          <option value="">All</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <button @click="applyFilters"
              class="px-4 py-2 rounded-lg bg-primary text-primary-text text-sm font-medium hover:opacity-90">
        Filter
      </button>
      <button v-if="filters.search || filters.role || filters.status"
              @click="clearFilters"
              class="px-4 py-2 rounded-lg text-sm text-app-text/60 hover:text-app-text border border-gray-200 dark:border-gray-700">
        Clear
      </button>
    </div>

    <!-- Table -->
    <DataTable :columns="columns" :rows="users" empty-message="No users found.">
      <template #cell-name="{ row, value }">
          <div class="flex items-center gap-2">
            <!-- <span>{{ value }}</span> -->
            <span v-if="row.invite_pending"
                  class="text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30
                         dark:text-yellow-400 px-1.5 py-0.5 rounded-full font-medium">
              Invite pending
            </span>
          </div>
        <a :href="`/users/${row.id}`" class="font-medium text-primary hover:underline">
          {{ value }}
        </a>
      </template>

      <template #cell-roles="{ value }">
        <div class="flex flex-wrap gap-1">
          <Badge v-for="role in value" :key="role" type="info">{{ role }}</Badge>
        </div>
      </template>

      <template #cell-is_active="{ value }">
        <Badge :type="value ? 'success' : 'neutral'" dot>
          {{ value ? 'Active' : 'Inactive' }}
        </Badge>
      </template>

      <template #cell-last_login_at="{ value }">
        <span class="text-app-text/50 text-xs">{{ value ?? 'Never' }}</span>
      </template>

      <template #cell-actions="{ row }">
        <div class="flex items-center justify-end gap-1">
          <a :href="`/users/${row.id}/edit`"
             class="px-2 py-1 text-xs text-app-text/50 hover:text-primary rounded transition-colors">
            Edit
          </a>
          <button v-if="row.is_active"
                  @click="promptDeactivate(row)"
                  class="px-2 py-1 text-xs text-app-text/50 hover:text-yellow-600 rounded transition-colors">
            Deactivate
          </button>
          <button v-else
                  @click="activate(row)"
                  class="px-2 py-1 text-xs text-app-text/50 hover:text-green-600 rounded transition-colors">
            Activate
          </button>
          <button @click="promptDelete(row)"
                  class="px-2 py-1 text-xs text-app-text/50 hover:text-red-500 rounded transition-colors">
            Delete
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Confirm deactivate -->
    <ConfirmDialog
      :show="confirmDeactivate"
      title="Deactivate User"
      :message="`${targetUser?.name} will no longer be able to log in. You can reactivate them at any time.`"
      confirm-label="Deactivate"
      @confirm="handleDeactivate"
      @cancel="confirmDeactivate = false"
    />

    <!-- Confirm delete -->
    <ConfirmDialog
      :show="confirmDelete"
      title="Delete User"
      :message="`${targetUser?.name} will be permanently deleted. This cannot be undone.`"
      confirm-label="Delete"
      danger
      @confirm="handleDelete"
      @cancel="confirmDelete = false"
    />
  </div>
</template>
