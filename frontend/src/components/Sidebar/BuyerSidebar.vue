<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Buyer Portal</h2>
      <p>Shopping</p>
    </div>

    <nav class="sidebar-menu">
      <router-link to="/buyer/dashboard" class="menu-item" active-class="active">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </router-link>
      <router-link to="/buyer/marketplace" class="menu-item" active-class="active">
        <i class="fas fa-shopping-bag"></i>
        <span>Marketplace</span>
      </router-link>
      <router-link to="/buyer/categories" class="menu-item" active-class="active">
        <i class="fas fa-list"></i>
        <span>Categories</span>
      </router-link>
      <router-link to="/buyer/cart" class="menu-item" active-class="active">
        <i class="fas fa-shopping-cart"></i>
        <span>Shopping Cart</span>
      </router-link>
      <router-link to="/buyer/orders" class="menu-item" active-class="active">
        <i class="fas fa-box"></i>
        <span>Orders</span>
      </router-link>
      <router-link to="/buyer/payments" class="menu-item" active-class="active">
        <i class="fas fa-credit-card"></i>
        <span>Payments</span>
      </router-link>
      <router-link to="/buyer/wishlist" class="menu-item" active-class="active">
        <i class="fas fa-bookmark"></i>
        <span>Wishlist</span>
      </router-link>
      <router-link to="/buyer/reviews" class="menu-item" active-class="active">
        <i class="fas fa-star"></i>
        <span>Reviews</span>
      </router-link>
    </nav>

    <div class="sidebar-bottom">
      <router-link to="/buyer/profile" class="menu-item" active-class="active">
        <i class="fas fa-user-circle"></i>
        <span>Profile</span>
      </router-link>
      <button class="menu-item logout-btn" @click="emit('logout')">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
const emit = defineEmits(['logout'])
</script>

<style scoped>
.sidebar {
  width: 260px;
  background: white;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  overflow-y: auto;
  z-index: 1000;
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.sidebar-header h2 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.sidebar-header p {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
}

.sidebar-menu {
  flex: 1;
  padding: 10px 0;
  overflow-y: auto;
}

.menu-item {
  padding: 12px 20px;
  color: #6b7280;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  transition: all 0.3s;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.menu-item:hover {
  color: #1f2937;
  background: #f3f4f6;
}

.menu-item.active {
  color: #3b82f6;
  background: #eff6ff;
  border-left: 3px solid #3b82f6;
  padding-left: 17px;
}

.menu-item i {
  font-size: 16px;
  min-width: 20px;
}

.sidebar-bottom {
  padding: 10px 0;
  border-top: 1px solid #e5e7eb;
}

.logout-btn {
  color: #ef4444;
}

.logout-btn:hover {
  color: #dc2626;
  background: #fee2e2;
}
</style>
