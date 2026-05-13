<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  columns: { type: Array, required: true },
  rows:    { type: Array, required: true },
  loading: { type: Boolean, default: false },
  emptyMessage: { type: String, default: 'No records found.' },
})

const emit = defineEmits(['row-click'])

const sortKey   = ref('')
const sortOrder = ref('asc')

function setSort(key) {
  if (!key) return
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value   = key
    sortOrder.value = 'asc'
  }
}

const sortedRows = computed(() => {
  if (!sortKey.value) return props.rows
  return [...props.rows].sort((a, b) => {
    const av = a[sortKey.value] ?? ''
    const bv = b[sortKey.value] ?? ''
    const cmp = String(av).localeCompare(String(bv), undefined, { numeric: true })
    return sortOrder.value === 'asc' ? cmp : -cmp
  })
})
</script>

<template>
  <div class="bg-surface rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <!-- Head -->
        <thead class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-app-text/50 select-none"
              :class="col.sortable ? 'cursor-pointer hover:text-app-text' : ''"
              @click="col.sortable ? setSort(col.key) : null"
            >
              <span class="flex items-center gap-1">
                {{ col.label }}
                <template v-if="col.sortable">
                  <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      :d="sortKey === col.key && sortOrder === 'asc'
                        ? 'M5 15l7-7 7 7'
                        : sortKey === col.key && sortOrder === 'desc'
                        ? 'M19 9l-7 7-7-7'
                        : 'M8 9l4-4 4 4m0 6l-4 4-4-4'" />
                  </svg>
                </template>
              </span>
            </th>
          </tr>
        </thead>

        <!-- Body -->
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
          <!-- Loading -->
          <template v-if="loading">
            <tr v-for="n in 5" :key="n">
              <td v-for="col in columns" :key="col.key" class="px-4 py-3">
                <div class="h-4 bg-gray-100 dark:bg-gray-800 rounded animate-pulse" />
              </td>
            </tr>
          </template>

          <!-- Empty -->
          <template v-else-if="!sortedRows.length">
            <tr>
              <td :colspan="columns.length" class="px-4 py-12 text-center text-app-text/40 text-sm">
                {{ emptyMessage }}
              </td>
            </tr>
          </template>

          <!-- Rows -->
          <template v-else>
            <tr
              v-for="(row, i) in sortedRows"
              :key="i"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              :class="$attrs.onRowClick ? 'cursor-pointer' : ''"
              @click="$emit('row-click', row)"
            >
              <td
                v-for="col in columns"
                :key="col.key"
                class="px-4 h-12 text-app-text"
              >
                <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                  {{ row[col.key] ?? '—' }}
                </slot>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>
