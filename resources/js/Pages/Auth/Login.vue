<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthLayout from '@shared/layouts/AuthLayout.vue'

defineOptions({ layout: AuthLayout })

const form = useForm({
  email:    '',
  password: '',
  remember: false,
})

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <div>
    <h2 class="text-xl font-semibold text-app-text mb-6">Sign in to your account</h2>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-app-text mb-1">
          Email address
        </label>
        <input
          v-model="form.email"
          type="email"
          autocomplete="email"
          required
          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600
                 bg-background text-app-text text-sm
                 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary
                 transition-colors"
          placeholder="you@example.com"
        />
        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">
          {{ form.errors.email }}
        </p>
      </div>

      <!-- Password -->
      <div>
        <label class="block text-sm font-medium text-app-text mb-1">
          Password
        </label>
        <input
          v-model="form.password"
          type="password"
          autocomplete="current-password"
          required
          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600
                 bg-background text-app-text text-sm
                 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary
                 transition-colors"
          placeholder="••••••••"
        />
        <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">
          {{ form.errors.password }}
        </p>
      </div>

      <!-- Remember me -->
      <div class="flex items-center gap-2">
        <input
          v-model="form.remember"
          id="remember"
          type="checkbox"
          class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/50"
        />
        <label for="remember" class="text-sm text-app-text/70">Remember me</label>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        :disabled="form.processing"
        class="w-full py-2.5 px-4 rounded-lg text-sm font-semibold
               bg-primary text-primary-text
               hover:opacity-90 disabled:opacity-50
               transition-opacity focus:outline-none focus:ring-2 focus:ring-primary/50"
      >
        <span v-if="form.processing">Signing in…</span>
        <span v-else>Sign in</span>
      </button>
    </form>
  </div>
</template>
