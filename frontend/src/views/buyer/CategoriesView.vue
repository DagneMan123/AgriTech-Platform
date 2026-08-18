<template>
  <div class="buyer-layout">
    <BuyerSidebar @logout="handleLogout" />
    <div class="buyer-page">
      <div class="page-header">
        <h1>Categories</h1>
        <p>Browse products by category</p>
      </div>

      <div class="content-section">
        <div v-if="loading" class="loading">
          <i class="fas fa-spinner fa-spin"></i>
          Loading categories...
        </div>

        <div v-if="categories.length === 0 && !loading" class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>No categories available</p>
        </div>

        <div v-if="categories.length > 0" class="categories-grid">
          <div v-for="category in categories" :key="category.id" class="category-card" @click="selectCategory(category)">
            <div class="category-icon">
              <i class="fas fa-tag"></i>
            </div>
            <h3>{{ category.name }}</h3>
            <p class="product-count">{{ category.product_count || 0 }} products</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()

const categories = ref([])
const loading = ref(true)

const loadCategories = async () => {
  loading.value = true
  try {
    const response = await fetch('http://localhost:8000/api/marketplace/categories', {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      categories.value = data.data || []
    }
  } catch (err) {
    console.error('Error loading categories:', err)
  } finally {
    loading.value = false
  }
}

const selectCategory = (category) => {
  router.push({
    name: 'buyer-marketplace',
    query: { category: category.id }
  })
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  loadCategories()
})
</script>

<style scoped>
.buyer-layout {
  display: flex;
  height: 100vh;
}

.buyer-page {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f9fafb;
  padding: 30px;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 5px;
}

.page-header p {
  color: #6b7280;
  font-size: 14px;
}

.content-section {
  background: white;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.loading {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #9ca3af;
}

.empty-state i {
  font-size: 48px;
  color: #d1d5db;
  margin-bottom: 15px;
  display: block;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 20px;
}

.category-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  background: #f9fafb;
}

.category-card:hover {
  border-color: #3b82f6;
  background: #eff6ff;
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.category-icon {
  font-size: 32px;
  color: #3b82f6;
  margin-bottom: 10px;
}

.category-card h3 {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.product-count {
  font-size: 12px;
  color: #9ca3af;
}

@media (max-width: 768px) {
  .buyer-page {
    margin-left: 0;
    padding: 20px;
  }

  .categories-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }
}
</style>
