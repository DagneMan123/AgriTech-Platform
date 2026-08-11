<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <h1>Market Prices</h1>
        <p>Check current market prices for agricultural products</p>
      </div>

      <div class="content-section">
        <div class="section-header">
          <h2>Product Prices</h2>
          <select class="filter-select">
            <option>All Markets</option>
            <option>Local Market</option>
            <option>Central Market</option>
          </select>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Market</th>
              <th>Current Price</th>
              <th>Previous Price</th>
              <th>Change</th>
              <th>Unit</th>
              <th>Last Updated</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(product, i) in ['Maize', 'Wheat', 'Tomatoes', 'Beans', 'Carrots', 'Potatoes']" :key="i">
              <td>{{ product }}</td>
              <td>Central Market</td>
              <td>${{ 25 + i * 5 }}</td>
              <td>${{ 24 + i * 5 }}</td>
              <td><span class="change-badge" :class="i % 2 === 0 ? 'up' : 'down'">{{ i % 2 === 0 ? '↑' : '↓' }} {{ (i % 2 === 0 ? 3 : 2) }}%</span></td>
              <td>{{ ['bag', 'bag', 'crate', 'kg', 'kg', 'kg'][i] }}</td>
              <td>Today</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="content-section" style="margin-top: 20px;">
        <h2>Price Trends</h2>
        <p style="color: #666; margin: 0;">Price history chart would be displayed here</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout { display: flex; height: 100vh; }
.farmer-page { margin-left: 260px; flex: 1; overflow-y: auto; background-color: #f5f5f5; padding: 20px; }
.page-header { margin-bottom: 30px; }
.page-header h1 { font-size: 28px; font-weight: bold; color: #333; margin-bottom: 5px; }
.page-header p { color: #666; }
.content-section { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
.section-header h2 { font-size: 20px; font-weight: bold; color: #333; }
.filter-select { padding: 10px 15px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 14px; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background-color: #f9fafb; padding: 12px; text-align: left; font-weight: 600; color: #374151; border-bottom: 2px solid #e5e7eb; font-size: 13px; }
.data-table td { padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
.data-table tbody tr:hover { background-color: #f9fafb; }
.change-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
.change-badge.up { background-color: #d1fae5; color: #065f46; }
.change-badge.down { background-color: #fee2e2; color: #991b1b; }
@media (max-width: 768px) { .farmer-page { margin-left: 0; } .data-table { font-size: 12px; } }
</style>
