<script setup>
import { router, usePage }    from '@inertiajs/vue3'
import AppLayout     from '@shared/layouts/AppLayout.vue'
import Badge         from '@shared/components/display/Badge.vue'
import Button        from '@shared/components/buttons/Button.vue'
import ConfirmDialog from '@shared/components/feedback/ConfirmDialog.vue'
import { ref, computed } from 'vue'

defineOptions({ layout: AppLayout })
const props = defineProps({ user: { type: Object, required: true } })

const page        = usePage()
const currentUser = page.props.auth?.user

const confirmDeactivate = ref(false)
const confirmDelete     = ref(false)

const isSelf = computed(() => currentUser?.id === props.user.id)

function deactivate() {
  router.patch(`/users/${props.user.id}/deactivate`, {}, {
    onFinish: () => confirmDeactivate.value = false,
  })
}

function activate() {
  router.patch(`/users/${props.user.id}/activate`)
}

function resetPassword() {
  router.post(`/users/${props.user.id}/reset-password`)
}

function handleDelete() {
  router.delete(`/users/${props.user.id}`, {}, {
    onFinish: () => confirmDelete.value = false,
  })
}
</script>

<template>
  <div class="max-w-3xl">
    <div class="mb-6">
      <a href="/users" class="text-sm text-primary hover:underline">← Users</a>
      <div class="flex items-start justify-between mt-3 gap-4 flex-wrap">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
            <span class="text-white text-xl font-bold">
              {{ user.name?.charAt(0)?.toUpperCase() }}
            </span>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-app-text">{{ user.name }}</h1>
            <div class="flex items-center gap-3 mt-1 flex-wrap">
              <Badge :type="user.is_active ? 'success' : 'neutral'" dot>
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </Badge>
              <Badge v-for="role in user.roles" :key="role" type="info">{{ role }}</Badge>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2 flex-wrap" v-if="!isSelf">
          <a :href="`/users/${user.id}/edit`"
             class="px-3 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/70 hover:text-app-text transition-colors">
            Edit
          </a>
          <button @click="resetPassword"
                  class="px-3 py-1.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg text-app-text/70 hover:text-app-text transition-colors">
            Reset Password
          </button>
          <Button v-if="user.is_active"  variant="secondary" size="sm" @click="confirmDeactivate = true">
            Deactivate
          </Button>
          <Button v-else variant="secondary" size="sm" @click="activate">
            Reactivate
          </Button>
          <Button variant="danger" size="sm" @click="confirmDelete = true">
            Delete
          </Button>
        </div>
      </div>
    </div>

    <!-- Details -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div class="bg-surface rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Account</h2>
        <dl class="space-y-3">
          <div>
            <dt class="text-xs text-app-text/50">Email</dt>
            <dd class="text-sm font-medium text-app-text mt-0.5">{{ user.email }}</dd>
          </div>
          <div>
            <dt class="text-xs text-app-text/50">Guard</dt>
            <dd class="text-sm font-medium text-app-text mt-0.5 capitalize">{{ user.guard }}</dd>
          </div>
          <div>
            <dt class="text-xs text-app-text/50">Portal Access</dt>
            <dd class="mt-0.5">
              <Badge :type="user.portal_access ? 'success' : 'neutral'">
                {{ user.portal_access ? 'Enabled' : 'Disabled' }}
              </Badge>
            </dd>
          </div>
          <div>
            <dt class="text-xs text-app-text/50">Last Login</dt>
            <dd class="text-sm font-medium text-app-text mt-0.5">
              {{ user.last_login_at ?? 'Never' }}
            </dd>
          </div>
          <div>
            <dt class="text-xs text-app-text/50">Member Since</dt>
            <dd class="text-sm font-medium text-app-text mt-0.5">{{ user.created_at }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <ConfirmDialog
      :show="confirmDeactivate"
      title="Deactivate User"
      :message="`${user.name} will no longer be able to log in.`"
      confirm-label="Deactivate"
      @confirm="deactivate"
      @cancel="confirmDeactivate = false"
    />

    <ConfirmDialog
      :show="confirmDelete"
      title="Delete User"
      :message="`${user.name} will be permanently deleted. This cannot be undone.`"
      confirm-label="Delete"
      danger
      @confirm="handleDelete"
      @cancel="confirmDelete = false"
    />
  </div>
</template>
