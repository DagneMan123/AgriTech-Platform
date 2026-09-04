<template>
  <div class="consultations-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="consultations-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Consultations</h1>
          <p>Connect with agricultural experts for professional advice</p>
        </div>
        <button @click="showNewConsultation = true" class="btn-new">
          <Plus size="16" />
          <span>Book Consultation</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading consultations...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchConsultations" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="consultations-content">
        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <CheckCircle size="20" class="stat-icon completed" />
            <div>
              <span class="stat-label">Completed</span>
              <span class="stat-value">{{ stats.completed }}</span>
            </div>
          </div>
          <div class="stat-card">
            <Clock size="20" class="stat-icon scheduled" />
            <div>
              <span class="stat-label">Scheduled</span>
              <span class="stat-value">{{ stats.scheduled }}</span>
            </div>
          </div>
          <div class="stat-card">
            <MessageSquare size="20" class="stat-icon pending" />
            <div>
              <span class="stat-label">Pending Response</span>
              <span class="stat-value">{{ stats.pending }}</span>
            </div>
          </div>
          <div class="stat-card">
            <Star size="20" class="stat-icon rating" />
            <div>
              <span class="stat-label">Avg Rating</span>
              <span class="stat-value">{{ avgRating }}/5</span>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab" 
            @click="activeTab = tab"
            class="tab"
            :class="{ active: activeTab === tab }"
          >
            {{ capitalize(tab) }}
          </button>
        </div>

        <!-- Consultations List -->
        <div class="consultations-section">
          <div v-if="filteredConsultations.length > 0" class="consultations-list">
            <div v-for="consultation in filteredConsultations" :key="consultation.id" class="consultation-card">
              <div class="card-header">
                <div class="expert-info">
                  <div class="avatar">
                    {{ consultation.expertName.charAt(0) }}
                  </div>
                  <div>
                    <h3>{{ consultation.expertName }}</h3>
                    <p class="specialty">{{ consultation.specialty }}</p>
                  </div>
                </div>
                <span class="status-badge" :class="`status-${consultation.status}`">
                  {{ capitalize(consultation.status) }}
                </span>
              </div>

              <div class="card-content">
                <p class="topic">{{ consultation.topic }}</p>
                <div class="details-row">
                  <span class="detail">
                    <Calendar size="14" />
                    {{ formatDate(consultation.date) }}
                  </span>
                  <span class="detail">
                    <Clock size="14" />
                    {{ consultation.time }}
                  </span>
                  <span class="detail">
                    <Video size="14" />
                    {{ consultation.type }}
                  </span>
                </div>

                <div v-if="consultation.notes" class="notes">
                  <p>{{ consultation.notes }}</p>
                </div>

                <div v-if="consultation.rating" class="rating-display">
                  <span class="rating-stars">
                    <span v-for="i in 5" :key="i" class="star" :class="{ filled: i <= consultation.rating }">★</span>
                  </span>
                  <span class="rating-text">{{ consultation.rating }}/5</span>
                </div>
              </div>

              <div class="card-actions">
                <button @click="viewDetails(consultation)" class="action-btn primary">
                  <Eye size="16" />
                  <span>View Details</span>
                </button>
                <button v-if="consultation.status === 'completed' && !consultation.rating" @click="rateConsultation(consultation)" class="action-btn secondary">
                  <Star size="16" />
                  <span>Rate</span>
                </button>
                <button v-if="consultation.status === 'scheduled'" @click="reschedule(consultation)" class="action-btn secondary">
                  <Clock size="16" />
                  <span>Reschedule</span>
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <MessageSquare size="48" class="empty-icon" />
            <p>No {{ activeTab }} consultations</p>
            <button @click="showNewConsultation = true" class="btn-book">
              <Plus size="16" />
              Book Now
            </button>
          </div>
        </div>

        <!-- New Consultation Modal -->
        <div v-if="showNewConsultation" class="modal-overlay" @click="showNewConsultation = false">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Book a Consultation</h2>
              <button @click="showNewConsultation = false" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="submitConsultation" class="form">
                <div class="form-group">
                  <label>Select Expert</label>
                  <select v-model="newConsultation.expertId" required class="form-control">
                    <option value="">-- Choose an expert --</option>
                    <option v-for="expert in experts" :key="expert.id" :value="expert.id">
                      {{ expert.name }} - {{ expert.specialty }}
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Consultation Topic</label>
                  <input v-model="newConsultation.topic" type="text" required placeholder="e.g., Pest Management" class="form-control" />
                </div>

                <div class="form-group">
                  <label>Description</label>
                  <textarea v-model="newConsultation.description" required placeholder="Describe your issue or question..." class="form-control" rows="4"></textarea>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label>Preferred Date</label>
                    <input v-model="newConsultation.date" type="date" required class="form-control" />
                  </div>
                  <div class="form-group">
                    <label>Preferred Time</label>
                    <input v-model="newConsultation.time" type="time" required class="form-control" />
                  </div>
                </div>

                <div class="form-group">
                  <label>Consultation Type</label>
                  <select v-model="newConsultation.type" required class="form-control">
                    <option value="video">Video Call</option>
                    <option value="phone">Phone Call</option>
                    <option value="chat">Chat</option>
                    <option value="onsite">On-site Visit</option>
                  </select>
                </div>

                <div class="form-actions">
                  <button type="button" @click="showNewConsultation = false" class="btn-secondary">Cancel</button>
                  <button type="submit" class="btn-primary">Book Consultation</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Details Modal -->
        <div v-if="selectedConsultation" class="modal-overlay" @click="selectedConsultation = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Consultation Details</h2>
              <button @click="selectedConsultation = null" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <div class="detail-section">
                <h3>Expert Information</h3>
                <div class="expert-card">
                  <div class="expert-avatar">{{ selectedConsultation.expertName.charAt(0) }}</div>
                  <div class="expert-details">
                    <h4>{{ selectedConsultation.expertName }}</h4>
                    <p>{{ selectedConsultation.specialty }}</p>
                    <div class="expert-rating">
                      <span v-for="i in 5" :key="i" class="star" :class="{ filled: i <= 4 }">★</span>
                      <span>4.8/5 ({{ selectedConsultation.reviews }} reviews)</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="detail-section">
                <h3>Consultation Details</h3>
                <div class="detail-grid">
                  <div class="detail-item">
                    <span class="label">Topic:</span>
                    <span class="value">{{ selectedConsultation.topic }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Date:</span>
                    <span class="value">{{ formatDate(selectedConsultation.date) }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Time:</span>
                    <span class="value">{{ selectedConsultation.time }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Type:</span>
                    <span class="value">{{ capitalize(selectedConsultation.type) }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Status:</span>
                    <span class="status-badge" :class="`status-${selectedConsultation.status}`">
                      {{ capitalize(selectedConsultation.status) }}
                    </span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Duration:</span>
                    <span class="value">{{ selectedConsultation.duration }} minutes</span>
                  </div>
                </div>
              </div>

              <div class="detail-section">
                <h3>Your Question</h3>
                <p class="question-text">{{ selectedConsultation.notes }}</p>
              </div>

              <div v-if="selectedConsultation.status === 'completed'" class="detail-section">
                <h3>Expert's Response</h3>
                <p class="response-text">{{ selectedConsultation.expertResponse }}</p>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedConsultation = null" class="btn-secondary">Close</button>
              <button v-if="selectedConsultation.status === 'scheduled'" @click="scheduleCall" class="btn-primary">
                <Video size="16" />
                Join Call
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import {
  Plus, MessageSquare, AlertCircle, RotateCcw, X, Eye, Calendar, Clock, Video, Star, CheckCircle,
  Lightbulb
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const error = ref(null)
const activeTab = ref('all')
const showNewConsultation = ref(false)
const selectedConsultation = ref(null)

const tabs = ['all', 'scheduled', 'completed', 'pending']

const experts = [
  { id: 1, name: 'Dr. Ahmed Hassan', specialty: 'Crop Management' },
  { id: 2, name: 'Prof. Sarah Williams', specialty: 'Pest Control' },
  { id: 3, name: 'Mr. James Smith', specialty: 'Soil Science' },
  { id: 4, name: 'Dr. Amira Khan', specialty: 'Irrigation Management' }
]

const mockConsultations = [
  {
    id: 1,
    expertName: 'Dr. Ahmed Hassan',
    specialty: 'Crop Management',
    topic: 'Tomato Plant Disease',
    date: '2026-09-01',
    time: '10:00 AM',
    type: 'video',
    status: 'completed',
    notes: 'My tomato plants are showing yellow leaves and wilting symptoms',
    duration: 45,
    rating: 5,
    reviews: 32,
    expertResponse: 'The symptoms indicate early blight. Apply fungicide immediately and remove infected leaves.'
  },
  {
    id: 2,
    expertName: 'Prof. Sarah Williams',
    specialty: 'Pest Control',
    topic: 'Pest Management Strategy',
    date: '2026-09-05',
    time: '2:00 PM',
    type: 'phone',
    status: 'scheduled',
    notes: 'Need advice on integrated pest management for corn field',
    duration: 60,
    rating: 0,
    reviews: 28,
    expertResponse: ''
  },
  {
    id: 3,
    expertName: 'Mr. James Smith',
    specialty: 'Soil Science',
    topic: 'Soil Nutrient Analysis',
    date: '2026-08-28',
    time: '11:30 AM',
    type: 'onsite',
    status: 'completed',
    notes: 'Request for soil testing and recommendations',
    duration: 90,
    rating: 4,
    reviews: 45,
    expertResponse: 'Your soil is deficient in nitrogen and potassium. Recommend N-P-K 16-16-16 fertilizer.'
  },
  {
    id: 4,
    expertName: 'Dr. Amira Khan',
    specialty: 'Irrigation Management',
    topic: 'Water Management',
    date: '2026-09-08',
    time: '9:00 AM',
    type: 'chat',
    status: 'pending',
    notes: 'Questions about drip irrigation system efficiency',
    duration: 30,
    rating: 0,
    reviews: 19,
    expertResponse: ''
  },
  {
    id: 5,
    expertName: 'Dr. Ahmed Hassan',
    specialty: 'Crop Management',
    topic: 'Crop Rotation Planning',
    date: '2026-08-25',
    time: '3:30 PM',
    type: 'video',
    status: 'completed',
    notes: 'Planning for next season crop rotation',
    duration: 50,
    rating: 5,
    reviews: 32,
    expertResponse: 'Recommend: Corn → Soybeans → Alfalfa rotation for optimal soil health.'
  }
]

const consultations = ref(mockConsultations)

const newConsultation = ref({
  expertId: '',
  topic: '',
  description: '',
  date: '',
  time: '',
  type: 'video'
})

const filteredConsultations = computed(() => {
  if (activeTab.value === 'all') return consultations.value
  return consultations.value.filter(c => c.status === activeTab.value)
})

const stats = computed(() => ({
  completed: consultations.value.filter(c => c.status === 'completed').length,
  scheduled: consultations.value.filter(c => c.status === 'scheduled').length,
  pending: consultations.value.filter(c => c.status === 'pending').length
}))

const avgRating = computed(() => {
  const ratings = consultations.value.filter(c => c.rating > 0).map(c => c.rating)
  return ratings.length > 0 ? (ratings.reduce((a, b) => a + b) / ratings.length).toFixed(1) : 'N/A'
})

const fetchConsultations = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const formatDate = (date) => new Date(date).toLocaleDateString()
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)
const viewDetails = (consultation) => { selectedConsultation.value = consultation }
const rateConsultation = (consultation) => { alert('Rate consultation: ' + consultation.id) }
const reschedule = (consultation) => { alert('Reschedule consultation: ' + consultation.id) }
const scheduleCall = () => { alert('Joining video call...') }

const submitConsultation = () => {
  alert('Consultation booked successfully!')
  showNewConsultation.value = false
  newConsultation.value = { expertId: '', topic: '', description: '', date: '', time: '', type: 'video' }
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchConsultations() })
</script>

<style scoped>
.consultations-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.consultations-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.page-header {
  background: white;
  padding: 25px 30px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.header-content h1 {
  font-size: 28px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.header-content p {
  color: #6b7280;
  font-size: 14px;
  margin: 0;
}

.btn-new {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-new:hover {
  background: #2563eb;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  gap: 20px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-container {
  background: #fee2e2;
  border: 2px solid #fca5a5;
  border-radius: 12px;
  padding: 40px;
  margin: 30px;
  text-align: center;
}

.error-icon {
  color: #dc2626;
  margin-bottom: 15px;
}

.error-message {
  color: #991b1b;
  font-size: 16px;
  margin-bottom: 20px;
}

.btn-retry {
  background: #dc2626;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.consultations-content {
  padding: 30px;
  flex: 1;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.stat-icon {
  color: #d1d5db;
  flex-shrink: 0;
}

.stat-icon.completed { color: #10b981; }
.stat-icon.scheduled { color: #3b82f6; }
.stat-icon.pending { color: #f59e0b; }
.stat-icon.rating { color: #f59e0b; }

.stat-label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
}

.tabs {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  border-bottom: 2px solid #e5e7eb;
}

.tab {
  padding: 12px 20px;
  background: none;
  border: none;
  color: #6b7280;
  font-weight: 600;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition: all 0.2s;
  margin-bottom: -2px;
}

.tab:hover {
  color: #1f2937;
}

.tab.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

.consultations-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.consultations-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px;
}

.consultation-card {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 16px;
  transition: all 0.3s;
}

.consultation-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border-color: #d1d5db;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
  gap: 12px;
}

.expert-info {
  display: flex;
  gap: 12px;
  flex: 1;
}

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #dbeafe;
  color: #1e40af;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  flex-shrink: 0;
}

.expert-info h3 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.specialty {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.status-completed { background: #d1fae5; color: #065f46; }
.status-scheduled { background: #dbeafe; color: #1e40af; }
.status-pending { background: #fef3c7; color: #92400e; }

.card-content {
  margin-bottom: 16px;
}

.topic {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.details-row {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 12px;
}

.detail {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #6b7280;
}

.notes {
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
  margin: 12px 0;
}

.notes p {
  font-size: 13px;
  color: #4b5563;
  margin: 0;
}

.rating-display {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
}

.rating-stars {
  display: inline-flex;
  gap: 2px;
}

.star {
  color: #d1d5db;
  font-size: 16px;
}

.star.filled {
  color: #f59e0b;
}

.rating-text {
  font-size: 12px;
  font-weight: 600;
  color: #4b5563;
}

.card-actions {
  display: flex;
  gap: 12px;
}

.action-btn {
  flex: 1;
  padding: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.2s;
}

.action-btn.primary {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.action-btn.primary:hover {
  background: #2563eb;
}

.action-btn.secondary {
  background: white;
  color: #3b82f6;
}

.action-btn.secondary:hover {
  background: #eff6ff;
}

.empty-state {
  padding: 60px 20px;
  text-align: center;
}

.empty-icon {
  color: #d1d5db;
  margin-bottom: 16px;
}

.empty-state p {
  font-size: 16px;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 20px;
}

.btn-book {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #6b7280;
  padding: 0;
}

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #4b5563;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  color: #1f2937;
  font-family: inherit;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.btn-primary,
.btn-secondary {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.detail-section {
  margin-bottom: 24px;
}

.detail-section h3 {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 16px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.expert-card {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.expert-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #dbeafe;
  color: #1e40af;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 24px;
  flex-shrink: 0;
}

.expert-details h4 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.expert-details p {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
}

.expert-rating {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  font-size: 12px;
  color: #4b5563;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
}

.detail-item .label {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
}

.detail-item .value {
  font-size: 13px;
  color: #1f2937;
}

.question-text,
.response-text {
  font-size: 13px;
  color: #4b5563;
  line-height: 1.6;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
  margin: 0;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

@media (max-width: 768px) {
  .consultations-container {
    margin-left: 0;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-new {
    width: 100%;
    justify-content: center;
  }

  .tabs {
    flex-wrap: wrap;
  }

  .details-row {
    flex-direction: column;
    gap: 8px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
