<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Expert Portal</h2>
      <p>Agricultural Consultation</p>
    </div>

    <nav class="sidebar-menu">
      <router-link to="/expert/dashboard" class="menu-item" active-class="active">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
      </router-link>
      <router-link to="/expert/consultations" class="menu-item" active-class="active">
        <i class="fas fa-comments"></i>
        <span>Consultations</span>
      </router-link>
      <router-link to="/expert/training" class="menu-item" active-class="active">
        <i class="fas fa-graduation-cap"></i>
        <span>Training Materials</span>
      </router-link>
      <router-link to="/expert/articles" class="menu-item" active-class="active">
        <i class="fas fa-file-alt"></i>
        <span>Articles</span>
      </router-link>
      <router-link to="/expert/disease-reports" class="menu-item" active-class="active">
        <i class="fas fa-virus"></i>
        <span>Disease Reports</span>
      </router-link>
      <router-link to="/expert/farm-visits" class="menu-item" active-class="active">
        <i class="fas fa-map-marker-alt"></i>
        <span>Farm Visits</span>
      </router-link>
    </nav>

    <div class="sidebar-bottom">
      <router-link to="/expert/profile" class="menu-item" active-class="active">
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
  color: #06b6d4;
  background: #ecfdf5;
  border-left: 3px solid #06b6d4;
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
