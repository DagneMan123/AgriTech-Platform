<template>
  <DashboardLayout>
    <template #title>Cooperative Members</template>
    <template #subtitle>Manage cooperative members and their activities</template>

    <!-- Filters & Search -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <FormField
          v-model="searchQuery"
          type="text"
          placeholder="Search members..."
          label="Search"
        />
        <FormSelect
          v-model="filterStatus"
          :options="statusOptions"
          label="Status"
          placeholder="All statuses"
        />
        <div class="flex items-end">
          <AppButton class="w-full">
            <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Member
          </AppButton>
        </div>
      </div>
    </div>

    <!-- Members Table -->
    <AppCard title="Members List">
      <AppTable :columns="tableColumns" :data="members" />
    </AppCard>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AppCard from '@/components/common/AppCard.vue'
import AppButton from '@/components/common/AppButton.vue'
import AppTable from '@/components/common/AppTable.vue'
import FormField from '@/components/forms/FormField.vue'
import FormSelect from '@/components/forms/FormSelect.vue'

const searchQuery = ref('')
const filterStatus = ref('')

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'pending', label: 'Pending' }
]

const members = ref([
  { id: 1, name: 'John Farmer', email: 'john@example.com', role: 'Farmer', joinDate: '2026-01-15', status: 'Active' },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'Farmer', joinDate: '2026-02-20', status: 'Active' },
  { id: 3, name: 'Bob Johnson', email: 'bob@example.com', role: 'Farmer', joinDate: '2026-03-10', status: 'Inactive' }
])

const tableColumns = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role' },
  { key: 'joinDate', label: 'Join Date' },
  { key: 'status', label: 'Status' }
]

const filteredMembers = computed(() => {
  return members.value.filter(member => {
    const matchesSearch = member.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                          member.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesStatus = !filterStatus.value || member.status.toLowerCase() === filterStatus.value
    return matchesSearch && matchesStatus
  })
})
</script>
