<template>
  <div class="buyer-layout">
    <BuyerSidebar @logout="handleLogout" />
    <div class="buyer-page">
      <div class="page-header">
        <h1>Reviews</h1>
        <p>Your product reviews and ratings</p>
      </div>

      <div class="content-section">
        <div v-if="loading" class="loading">
          <i class="fas fa-spinner fa-spin"></i>
          Loading reviews...
        </div>

        <div v-if="reviews.length === 0 && !loading" class="empty-state">
          <i class="fas fa-star"></i>
          <p>No reviews yet</p>
          <p class="subtitle">Start reviewing products you've purchased</p>
        </div>

        <div v-if="reviews.length > 0" class="reviews-list">
          <div v-for="review in reviews" :key="review.id" class="review-item">
            <div class="review-header">
              <h4>{{ review.product_name }}</h4>
              <div class="rating">
                <i v-for="n in 5" :key="n" :class="['fas fa-star', n <= review.rating ? 'filled' : '']"></i>
              </div>
            </div>
            <p class="review-comment">{{ review.comment }}</p>
            <p class="review-date">{{ formatDate(review.created_at) }}</p>
          </div>
        </div>
      </div>

      <div class="content-section">
        <h2>Add Review</h2>
        <form @submit.prevent="submitReview" class="form">
          <div class="form-group">
            <label>Product</label>
            <select v-model="newReview.product_id" required>
              <option value="">Select a product</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Rating</label>
            <div class="rating-input">
              <i v-for="n in 5" :key="n" class="fas fa-star" :class="n <= newReview.rating ? 'filled' : ''" @click="newReview.rating = n"></i>
            </div>
          </div>

          <div class="form-group">
            <label>Comment</label>
            <textarea v-model="newReview.comment" placeholder="Share your thoughts..." rows="4"></textarea>
          </div>

          <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
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

const reviews = ref([])
const products = ref([])
const loading = ref(true)
const newReview = ref({
  product_id: '',
  rating: 0,
  comment: ''
})

const loadReviews = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/buyer/reviews', {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      reviews.value = data.data || []
    }
  } catch (err) {
    console.error('Error loading reviews:', err)
  } finally {
    loading.value = false
  }
}

const loadProducts = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/marketplace/products', {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      products.value = data.data || []
    }
  } catch (err) {
    console.error('Error loading products:', err)
  }
}

const submitReview = async () => {
  if (!newReview.value.product_id || !newReview.value.rating) {
    alert('Please select a product and rating')
    return
  }

  try {
    const response = await fetch('http://localhost:8000/api/buyer/reviews', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newReview.value)
    })

    if (response.ok) {
      alert('Review submitted successfully')
      newReview.value = { product_id: '', rating: 0, comment: '' }
      loadReviews()
    }
  } catch (err) {
    console.error('Error submitting review:', err)
    alert('Failed to submit review')
  }
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-US')
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  loadReviews()
  loadProducts()
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
  margin-bottom: 20px;
}

.content-section h2 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 20px;
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

.subtitle {
  font-size: 13px;
  color: #9ca3af;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.review-item {
  padding: 15px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.review-header h4 {
  margin: 0;
  color: #1f2937;
  font-size: 14px;
  font-weight: 600;
}

.rating {
  display: flex;
  gap: 4px;
}

.rating i {
  color: #d1d5db;
  font-size: 14px;
}

.rating i.filled {
  color: #fbbf24;
}

.review-comment {
  margin: 10px 0;
  color: #4b5563;
  font-size: 14px;
}

.review-date {
  margin: 0;
  color: #9ca3af;
  font-size: 12px;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.form-group select,
.form-group textarea {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.rating-input {
  display: flex;
  gap: 8px;
  cursor: pointer;
}

.rating-input i {
  font-size: 24px;
  color: #d1d5db;
  transition: all 0.2s;
}

.rating-input i.filled {
  color: #fbbf24;
}

.rating-input i:hover {
  transform: scale(1.2);
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}

@media (max-width: 768px) {
  .buyer-page {
    margin-left: 0;
    padding: 20px;
  }

  .review-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
}
</style>
