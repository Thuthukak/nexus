<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout   from '@shared/layouts/AppLayout.vue'
import Input       from '@shared/components/form/Input.vue'
import Button      from '@shared/components/buttons/Button.vue'

defineOptions({ layout: AppLayout })

const props = defineProps({ settings: { type: Object, required: true } })

const form = useForm({ ...props.settings })

const isPayfast  = computed(() => form.gateway === 'payfast')
const isPaystack = computed(() => form.gateway === 'paystack')
const isNone     = computed(() => form.gateway === 'none')

function submit() {
  form.patch('/financial/settings/payments')
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-app-text">Payment Settings</h1>
      <p class="text-sm text-app-text/60 mt-1">
        Configure online payment gateway and banking details
      </p>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- Gateway selection -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-4">Payment Gateway</h2>

        <div class="grid grid-cols-3 gap-3 mb-4">
          <button v-for="gw in [
            { value: 'none',     label: 'Manual Only',  desc: 'EFT / cash only' },
            { value: 'payfast',  label: 'PayFast',      desc: 'ZA preferred' },
            { value: 'paystack', label: 'Paystack',     desc: 'Multi-currency' },
          ]" :key="gw.value" type="button"
                  @click="form.gateway = gw.value"
                  class="flex flex-col items-center gap-1 p-4 rounded-xl border-2 transition-all text-center"
                  :class="form.gateway === gw.value
                    ? 'border-primary bg-primary/5'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
            <span class="text-sm font-semibold text-app-text">{{ gw.label }}</span>
            <span class="text-xs text-app-text/50">{{ gw.desc }}</span>
          </button>
        </div>

        <!-- Test mode toggle -->
        <div v-if="!isNone" class="flex items-center justify-between p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
          <div>
            <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-400">Test Mode</p>
            <p class="text-xs text-yellow-700 dark:text-yellow-500 mt-0.5">
              No real payments processed. Use sandbox credentials.
            </p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input v-model="form.test_mode" type="checkbox" class="sr-only peer" />
            <div class="w-10 h-6 bg-gray-300 peer-focus:ring-2 peer-focus:ring-yellow-400 rounded-full peer peer-checked:bg-yellow-500 transition-colors"></div>
            <span class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-4"></span>
          </label>
        </div>
      </div>

      <!-- PayFast credentials -->
      <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
        <div v-if="isPayfast" class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
          <div class="flex items-center gap-3 mb-2">
            <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">PayFast Credentials</h2>
            <a href="https://www.payfast.co.za/registration" target="_blank"
               class="text-xs text-primary hover:underline">Get credentials →</a>
          </div>
          <p v-if="form.test_mode" class="text-xs text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20 px-3 py-2 rounded-lg">
            Test mode: use PayFast sandbox credentials from
            <a href="https://sandbox.payfast.co.za" target="_blank" class="underline">sandbox.payfast.co.za</a>
          </p>
          <div class="grid grid-cols-2 gap-4">
            <Input v-model="form.payfast_merchant_id"  label="Merchant ID"  :error="form.errors.payfast_merchant_id" />
            <Input v-model="form.payfast_merchant_key" label="Merchant Key" :error="form.errors.payfast_merchant_key" />
          </div>
          <Input v-model="form.payfast_passphrase" label="Passphrase (optional but recommended)"
                 hint="Set in your PayFast account under Settings" :error="form.errors.payfast_passphrase" />
        </div>
      </Transition>

      <!-- Paystack credentials -->
      <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
        <div v-if="isPaystack" class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
          <div class="flex items-center gap-3 mb-2">
            <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider">Paystack Credentials</h2>
            <a href="https://dashboard.paystack.com" target="_blank"
               class="text-xs text-primary hover:underline">Paystack dashboard →</a>
          </div>
          <p v-if="form.test_mode" class="text-xs text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20 px-3 py-2 rounded-lg">
            Test mode: use your Paystack test keys (prefix: pk_test_ / sk_test_)
          </p>
          <div class="grid grid-cols-1 gap-4">
            <Input v-model="form.paystack_public_key" label="Public Key"  hint="Starts with pk_test_ or pk_live_" :error="form.errors.paystack_public_key" />
            <Input v-model="form.paystack_secret_key" label="Secret Key"  hint="Starts with sk_test_ or sk_live_ — keep this private" :error="form.errors.paystack_secret_key" />
          </div>
        </div>
      </Transition>

      <!-- Bank / EFT details -->
      <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 p-6 space-y-4">
        <h2 class="text-xs font-semibold text-app-text/50 uppercase tracking-wider mb-2">
          EFT / Bank Transfer Details
        </h2>
        <p class="text-xs text-app-text/40">
          Displayed on invoice emails alongside the online payment option.
        </p>
        <div class="grid grid-cols-2 gap-4">
          <Input v-model="form.bank_account_name"   label="Account Name" :error="form.errors.bank_account_name" />
          <Input v-model="form.bank_name"           label="Bank Name"    :error="form.errors.bank_name" />
          <Input v-model="form.bank_account_number" label="Account Number" :error="form.errors.bank_account_number" />
          <Input v-model="form.bank_branch_code"    label="Branch Code"  :error="form.errors.bank_branch_code" />
          <Input v-model="form.bank_reference_prefix" label="Payment Reference Prefix"
                 hint="e.g. INV- → customer uses INV-0042 as reference" :error="form.errors.bank_reference_prefix" />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-app-text">Additional Payment Instructions</label>
          <textarea v-model="form.payment_instructions" rows="3"
                    placeholder="e.g. Please email proof of payment to accounts@yourcompany.co.za"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-background text-app-text text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none" />
        </div>
      </div>

      <div class="flex justify-end">
        <Button type="submit" :loading="form.processing">Save Payment Settings</Button>
      </div>
    </form>
  </div>
</template>
