<template>
  <DashboardLayout>
    <template #title>Cooperative Reports</template>
    <template #subtitle>Comprehensive analysis and reports for cooperative performance</template>

    <!-- Report Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <FormSelect
          v-model="reportType"
          :options="reportTypeOptions"
          label="Report Type"
        />
        <FormSelect
          v-model="period"
          :options="periodOptions"
          label="Period"
        />
        <FormField
          v-model="startDate"
          type="date"
          label="Start Date"
        />
        <FormField
          v-model="endDate"
          type="date"
          label="End Date"
        />
      </div>
      <div class="mt-4 flex gap-2">
        <AppButton variant="primary">Generate Report</AppButton>
        <AppButton variant="secondary">Export PDF</AppButton>
      </div>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Member Statistics Report -->
      <AppCard title="Member Statistics">
        <div class="space-y-4">
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Total Members</span>
            <span class="text-2xl font-bold text-green-600">145</span>
          </div>
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Active Members</span>
            <span class="text-2xl font-bold text-blue-600">120</span>
          </div>
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">New Members</span>
            <span class="text-2xl font-bold text-yellow-600">12</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Growth Rate</span>
            <span class="text-2xl font-bold text-purple-600">+8.3%</span>
          </div>
        </div>
      </AppCard>

      <!-- Sales Performance Report -->
      <AppCard title="Sales Performance">
        <div class="space-y-4">
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Total Sales</span>
            <span class="text-2xl font-bold text-green-600">$125,000</span>
          </div>
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Average Sale</span>
            <span class="text-2xl font-bold text-blue-600">$288</span>
          </div>
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Total Orders</span>
            <span class="text-2xl font-bold text-yellow-600">434</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Growth Rate</span>
            <span class="text-2xl font-bold text-purple-600">+12.5%</span>
          </div>
        </div>
      </AppCard>

      <!-- Financial Summary -->
      <AppCard title="Financial Summary">
        <div class="space-y-4">
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Revenue</span>
            <span class="text-2xl font-bold text-green-600">$125,000</span>
          </div>
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Operating Costs</span>
            <span class="text-2xl font-bold text-red-600">$45,000</span>
          </div>
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Net Profit</span>
            <span class="text-2xl font-bold text-green-600">$80,000</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Profit Margin</span>
            <span class="text-2xl font-bold text-purple-600">64%</span>
          </div>
        </div>
      </AppCard>

      <!-- Distribution Channels -->
      <AppCard title="Distribution Analysis">
        <div class="space-y-4">
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Direct Sales</span>
            <span class="text-2xl font-bold">45%</span>
          </div>
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Wholesale</span>
            <span class="text-2xl font-bold">30%</span>
          </div>
          <div class="flex justify-between items-center pb-4 border-b">
            <span class="text-gray-600">Export</span>
            <span class="text-2xl font-bold">15%</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Online</span>
            <span class="text-2xl font-bold">10%</span>
          </div>
        </div>
      </AppCard>
    </div>

    <!-- Detailed Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
      <AppCard title="Sales Trend">
        <LineChart />
      </AppCard>

      <AppCard title="Member Distribution">
        <BarChart />
      </AppCard>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AppCard from '@/components/common/AppCard.vue'
import AppButton from '@/components/common/AppButton.vue'
import FormField from '@/components/forms/FormField.vue'
import FormSelect from '@/components/forms/FormSelect.vue'
import LineChart from '@/components/charts/LineChart.vue'
import BarChart from '@/components/charts/BarChart.vue'

const reportType = ref('summary')
const period = ref('month')
const startDate = ref('2026-08-01')
const endDate = ref('2026-08-31')

const reportTypeOptions = [
  { value: 'summary', label: 'Summary Report' },
  { value: 'detailed', label: 'Detailed Report' },
  { value: 'financial', label: 'Financial Report' },
  { value: 'member', label: 'Member Report' }
]

const periodOptions = [
  { value: 'week', label: 'Weekly' },
  { value: 'month', label: 'Monthly' },
  { value: 'quarter', label: 'Quarterly' },
  { value: 'year', label: 'Yearly' }
]
</script>
