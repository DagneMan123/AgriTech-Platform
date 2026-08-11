<template>
  <nav class="mb-6" aria-label="Breadcrumb">
    <ol class="flex flex-wrap gap-2 text-sm">
      <li v-for="(item, index) in items" :key="index">
        <router-link v-if="item.to" :to="item.to" class="text-green-600 hover:text-green-700">
          {{ item.label }}
        </router-link>
        <span v-else class="text-gray-600">{{ item.label }}</span>
        <span v-if="index < items.length - 1" class="mx-2 text-gray-400">/</span>
      </li>
    </ol>
  </nav>
</template>

<script setup lang="ts">
interface BreadcrumbItem {
  label: string
  to?: string
}

interface Props {
  items: BreadcrumbItem[]
}

defineProps<Props>()
</script>
