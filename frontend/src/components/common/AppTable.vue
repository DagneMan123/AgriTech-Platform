<template>
  <div class="overflow-x-auto bg-white rounded-lg shadow-md">
    <table class="w-full">
      <thead class="bg-gray-50 border-b">
        <tr>
          <th v-for="column in columns" :key="column.key" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
            {{ column.label }}
          </th>
          <th v-if="$slots.actions" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <tr v-for="row in rows" :key="row.id" class="hover:bg-gray-50">
          <td v-for="column in columns" :key="column.key" class="px-6 py-4 text-sm text-gray-700">
            <slot :name="`cell-${column.key}`" :row="row">
              {{ row[column.key] }}
            </slot>
          </td>
          <td v-if="$slots.actions" class="px-6 py-4 text-sm">
            <slot name="actions" :row="row" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
interface Column {
  key: string
  label: string
}

interface Props {
  columns: Column[]
  rows: any[]
}

defineProps<Props>()
</script>
