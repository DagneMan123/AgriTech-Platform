<template>
  <div class="space-y-2">
    <!-- Admin Menu -->
    <template v-if="role === 'admin'">
      <nav-item href="/app/admin/dashboard" icon="📊" label="Dashboard" />
      <nav-item href="/app/admin/users" icon="👥" label="Users" />
      <nav-item href="/app/admin/roles" icon="🔑" label="Roles" />
      <nav-item href="/app/admin/activity-logs" icon="📝" label="Activity Logs" />
      <nav-item href="/app/admin/settings" icon="⚙️" label="Settings" />
      <nav-item href="/app/admin/reports" icon="📈" label="Reports" />
    </template>

    <!-- Farmer Menu -->
    <template v-else-if="role === 'farmer'">
      <nav-item href="/app/farmer/dashboard" icon="🏠" label="Dashboard" />
      <nav-item href="/app/farmer/farms" icon="🌾" label="Farms" />
      <nav-item href="/app/farmer/crops" icon="🌱" label="Crops" />
      <nav-item href="/app/farmer/products" icon="📦" label="Products" />
      <nav-item href="/app/farmer/orders" icon="📋" label="Orders" />
      <nav-item href="/app/farmer/consultations" icon="💬" label="Consultations" />
      <nav-item href="/app/farmer/loans" icon="💰" label="Loans" />
    </template>

    <!-- Buyer Menu -->
    <template v-else-if="role === 'buyer'">
      <nav-item href="/app/buyer/dashboard" icon="🏠" label="Dashboard" />
      <nav-item href="/app/buyer/marketplace" icon="🛒" label="Marketplace" />
      <nav-item href="/app/buyer/cart" icon="🛍️" label="Cart" />
      <nav-item href="/app/buyer/orders" icon="📋" label="Orders" />
      <nav-item href="/app/buyer/wishlist" icon="❤️" label="Wishlist" />
    </template>

    <!-- Supplier Menu -->
    <template v-else-if="role === 'supplier'">
      <nav-item href="/app/supplier/dashboard" icon="🏠" label="Dashboard" />
      <nav-item href="/app/supplier/products" icon="📦" label="Products" />
      <nav-item href="/app/supplier/inventory" icon="📊" label="Inventory" />
      <nav-item href="/app/supplier/orders" icon="📋" label="Orders" />
      <nav-item href="/app/supplier/license" icon="📄" label="License" />
    </template>

    <!-- Transport Menu -->
    <template v-else-if="role === 'transport'">
      <nav-item href="/app/transport/dashboard" icon="🏠" label="Dashboard" />
      <nav-item href="/app/transport/deliveries" icon="🚚" label="Deliveries" />
      <nav-item href="/app/transport/vehicles" icon="🚗" label="Vehicles" />
    </template>

    <!-- Expert Menu -->
    <template v-else-if="role === 'expert'">
      <nav-item href="/app/expert/dashboard" icon="🏠" label="Dashboard" />
      <nav-item href="/app/expert/consultations" icon="💬" label="Consultations" />
      <nav-item href="/app/expert/articles" icon="📰" label="Articles" />
      <nav-item href="/app/expert/training" icon="📚" label="Training" />
    </template>

    <!-- Financial Menu -->
    <template v-else-if="role === 'financial'">
      <nav-item href="/app/financial/dashboard" icon="🏠" label="Dashboard" />
      <nav-item href="/app/financial/loans" icon="💳" label="Loans" />
      <nav-item href="/app/financial/insurance" icon="🛡️" label="Insurance" />
    </template>

    <!-- Cooperative Menu -->
    <template v-else-if="role === 'cooperative'">
      <nav-item href="/app/cooperative/dashboard" icon="🏠" label="Dashboard" />
      <nav-item href="/app/cooperative/members" icon="👥" label="Members" />
      <nav-item href="/app/cooperative/sales" icon="📊" label="Sales" />
    </template>
  </div>
</template>

<script setup lang="ts">
import NavItem from './NavItem.vue'

defineProps<{
  role: string | null
}>()
</script>
