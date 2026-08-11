<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Financial Portal</h2>
      <p>Loans & Insurance</p>
    </div>

    <nav class="sidebar-menu">
      <router-link to="/financial/dashboard" class="menu-item" active-class="active">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </router-link>
      <router-link to="/financial/loan-applications" class="menu-item" active-class="active">
        <i class="fas fa-file-contract"></i>
        <span>Loan Applications</span>
      </router-link>
      <router-link to="/financial/loans" class="menu-item" active-class="active">
        <i class="fas fa-dollar-sign"></i>
        <span>Loans</span>
      </router-link>
      <router-link to="/financial/insurance" class="menu-item" active-class="active">
        <i class="fas fa-shield-alt"></i>
        <span>Insurance</span>
      </router-link>
      <router-link to="/financial/transactions" class="menu-item" active-class="active">
        <i class="fas fa-exchange-alt"></i>
        <span>Transactions</span>
      </router-link>
      <router-link to="/financial/repayments" class="menu-item" active-class="active">
        <i class="fas fa-calendar-check"></i>
        <span>Repayments</span>
      </router-link>
      <router-link to="/financial/reports" class="menu-item" active-class="active">
        <i class="fas fa-file-chart-line"></i>
        <span>Reports</span>
      </router-link>
    </nav>

    <div class="sidebar-bottom">
      <router-link to="/financial/profile" class="menu-item" active-class="active">
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
  color: #6366f1;
  background: #eef2ff;
  border-left: 3px solid #6366f1;
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
