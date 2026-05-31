<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout         from '@shared/layouts/AppLayout.vue'
import Badge             from '@shared/components/display/Badge.vue'
import Button            from '@shared/components/buttons/Button.vue'
import Modal             from '@shared/components/feedback/Modal.vue'
import ConfirmDialog     from '@shared/components/feedback/ConfirmDialog.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({
  roles:       { type: Array,  default: () => [] },
  permissions: { type: Object, default: () => ({}) },
})

// Create role
const showCreate = ref(false)
const createForm = useForm({ name: '', permissions: [] })

function createRole() {
  createForm.post('/roles', {
    onSuccess: () => {
      showCreate.value = false
      createForm.reset()
    },
  })
}

// Edit permissions
const editingRole    = ref(null)
const editPermissions= ref([])

function openEdit(role) {
  editingRole.value     = role
  editPermissions.value = [...role.permissions]
}

function togglePermission(perm) {
  const idx = editPermissions.value.indexOf(perm)
  if (idx === -1) editPermissions.value.push(perm)
  else editPermissions.value.splice(idx, 1)
}

function savePermissions() {
  router.patch(`/roles/${editingRole.value.id}`, {
    permissions: editPermissions.value,
  }, {
    onSuccess: () => editingRole.value = null,
  })
}

// Delete
const confirmDelete = ref(false)
const deletingRole  = ref(null)

function promptDelete(role) {
  deletingRole.value  = role
  confirmDelete.value = true
}

function handleDelete() {
  router.delete(`/roles/${deletingRole.value.id}`, {}, {
    onFinish: () => {
      confirmDelete.value = false
      deletingRole.value  = null
    },
  })
}

const moduleNames = computed(() => Object.keys(props.permissions))
</script>

<template>
  <div class="max-w-5xl">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-app-text">Roles & Permissions</h1>
        <p class="text-sm text-app-text/60 mt-1">Manage access control for your team</p>
      </div>
      <Button @click="showCreate = true">New Role</Button>
    </div>

    <!-- Roles list -->
    <div class="space-y-3">
      <div v-for="role in roles" :key="role.id"
           class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-5">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
              <h2 class="font-semibold text-app-text">{{ role.name }}</h2>
              <Badge v-if="role.is_system" type="neutral">System</Badge>
              <span class="text-xs text-app-text/40">
                {{ role.users_count }} user{{ role.users_count !== 1 ? 's' : '' }}
              </span>
            </div>
            <div v-if="role.permissions.length" class="flex flex-wrap gap-1">
              <span v-for="perm in role.permissions.slice(0, 6)" :key="perm"
                    class="text-xs bg-gray-100 dark:bg-gray-800 text-app-text/60 px-2 py-0.5 rounded font-mono">
                {{ perm }}
              </span>
              <span v-if="role.permissions.length > 6"
                    class="text-xs text-app-text/40">
                +{{ role.permissions.length - 6 }} more
              </span>
              <span v-if="!role.permissions.length && role.name !== 'Super Admin'"
                    class="text-xs text-app-text/30">No permissions assigned</span>
              <span v-if="role.name === 'Super Admin'"
                    class="text-xs text-app-text/40 italic">All permissions (wildcard)</span>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-shrink-0">
            <button v-if="role.name !== 'Super Admin'"
                    @click="openEdit(role)"
                    class="px-3 py-1.5 text-xs font-medium border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/60 hover:text-app-text transition-colors">
              Edit Permissions
            </button>
            <button v-if="!role.is_system"
                    @click="promptDelete(role)"
                    class="px-3 py-1.5 text-xs font-medium border border-red-200 text-red-500 rounded-lg hover:bg-red-50 transition-colors">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create role modal -->
    <Modal :show="showCreate" title="New Role" size="md" @close="showCreate = false">
      <div class="space-y-4">
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Role Name</label>
          <input v-model="createForm.name" type="text" placeholder="e.g. Accounts Clerk"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50" />
          <p v-if="createForm.errors.name" class="text-xs text-red-500">{{ createForm.errors.name }}</p>
        </div>
      </div>
      <template #footer>
        <button @click="showCreate = false"
                class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</button>
        <Button @click="createRole" :loading="createForm.processing">Create Role</Button>
      </template>
    </Modal>

    <!-- Edit permissions modal -->
    <Modal :show="!!editingRole" :title="`Permissions — ${editingRole?.name}`"
           size="xl" @close="editingRole = null">
      <div v-if="editingRole" class="space-y-6">
        <div v-for="module in moduleNames" :key="module">
          <h3 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-2 capitalize">
            {{ module }}
          </h3>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <label v-for="perm in permissions[module]" :key="perm"
                   class="flex items-center gap-2 p-2 rounded-lg border border-gray-100 dark:border-gray-800 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
              <input :checked="editPermissions.includes(perm)"
                     @change="togglePermission(perm)"
                     type="checkbox"
                     class="w-3.5 h-3.5 rounded border-gray-300 text-primary focus:ring-primary/50" />
              <span class="text-xs font-mono text-app-text/70 truncate">
                {{ perm.split('.').slice(1).join('.') }}
              </span>
            </label>
          </div>
        </div>
      </div>
      <template #footer>
        <button @click="editingRole = null"
                class="px-4 py-2 text-sm text-app-text/60 hover:text-app-text">Cancel</button>
        <Button @click="savePermissions">Save Permissions</Button>
      </template>
    </Modal>

    <!-- Confirm delete -->
    <ConfirmDialog
      :show="confirmDelete"
      title="Delete Role"
      :message="`Role '${deletingRole?.name}' will be permanently deleted.`"
      confirm-label="Delete"
      danger
      @confirm="handleDelete"
      @cancel="confirmDelete = false"
    />
  </div>
</template>
