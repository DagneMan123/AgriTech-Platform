<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Expert Consultations</h1>
          <p>Get expert agricultural advice tailored to your needs</p>
        </div>
        <button class="btn-primary btn-large" @click="openRequestConsultationDialog">
          <i class="fas fa-plus"></i> Request Consultation
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon total">
            <i class="fas fa-comments"></i>
          </div>
          <div class="stat-content">
            <h3>Total Requests</h3>
            <p class="stat-value">{{ consultations.length }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon pending">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-content">
            <h3>Pending Response</h3>
            <p class="stat-value">{{ pendingCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon resolved">
            <i class="fas fa-check-double"></i>
          </div>
          <div class="stat-content">
            <h3>Resolved</h3>
            <p class="stat-value">{{ resolvedCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon responded">
            <i class="fas fa-comments"></i>
          </div>
          <div class="stat-content">
            <h3>In Progress</h3>
            <p class="stat-value">{{ respondedCount }}</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="controls-section">
        <div class="filter-group">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search consultations..."
            class="search-input"
          />
          <select v-model="typeFilter" class="type-select">
            <option value="">All Types</option>
            <option value="crop">Crop</option>
            <option value="soil">Soil</option>
            <option value="pest">Pest</option>
            <option value="irrigation">Irrigation</option>
            <option value="fertilizer">Fertilizer</option>
            <option value="general">General</option>
          </select>
          <select v-model="statusFilter" class="status-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="responded">Responded</option>
            <option value="resolved">Resolved</option>
          </select>
        </div>
      </div>

      <!-- Consultations List -->
      <div class="consultations-section">
        <div v-if="loading" class="loading-state">
          <p><i class="fas fa-spinner fa-spin"></i> Loading consultations...</p>
        </div>

        <div v-else-if="filteredConsultations.length === 0" class="empty-state">
          <i class="fas fa-inbox"></i>
          <h3>No consultations found</h3>
          <p>{{ consultations.length === 0 ? 'Request your first consultation' : 'No consultations match your filters' }}</p>
        </div>

        <div v-else class="consultations-list">
          <div v-for="consultation in filteredConsultations" :key="consultation.id" class="consultation-card">
            <div class="consultation-header">
              <div class="consultation-info">
                <h3>{{ consultation.title }}</h3>
                <p class="type-badge">{{ formatType(consultation.consultation_type) }}</p>
              </div>
              <div class="header-right">
                <span class="priority-badge" :class="`priority-${consultation.priority}`">
                  {{ capitalizeFirstLetter(consultation.priority) }}
                </span>
                <span class="status-badge" :class="`status-${consultation.status}`">
                  {{ formatStatus(consultation.status) }}
                </span>
              </div>
            </div>

            <div class="consultation-body">
              <p class="description">{{ truncateText(consultation.description, 120) }}</p>
              
              <div class="consultation-meta">
                <div class="meta-item">
                  <i class="fas fa-user-tie"></i>
                  <span>{{ consultation.expert?.name || 'Awaiting Assignment' }}</span>
                </div>
                <div class="meta-item">
                  <i class="fas fa-calendar"></i>
                  <span>{{ formatDate(consultation.created_at) }}</span>
                </div>
                <div v-if="consultation.budget" class="meta-item">
                  <i class="fas fa-dollar-sign"></i>
                  <span>${{ consultation.budget }}</span>
                </div>
              </div>
            </div>

            <div class="consultation-footer">
              <div class="message-count" v-if="consultation.messages_count">
                <i class="fas fa-comments"></i>
                {{ consultation.messages_count }} {{ consultation.messages_count === 1 ? 'message' : 'messages' }}
              </div>
              <div class="actions">
                <button class="btn-small btn-view" @click="viewConsultation(consultation)">
                  <i class="fas fa-eye"></i> View
                </button>
                <button v-if="consultation.status === 'pending'" class="btn-small btn-edit" @click="editConsultation(consultation)">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn-small btn-delete" @click="deleteConsultation(consultation.id)">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Request Consultation Modal -->
      <div v-if="showRequestDialog" class="modal-overlay" @click="closeRequestConsultationDialog">
        <div class="modal-dialog" @click.stop>
          <div class="modal-header">
            <h2>{{ editingConsultation ? 'Edit Consultation' : 'Request Expert Consultation' }}</h2>
            <button class="close-btn" @click="closeRequestConsultationDialog">&times;</button>
          </div>

          <div class="modal-content">
            <form @submit.prevent="submitConsultationRequest">
              <!-- Expert Selection -->
              <div class="form-group">
                <label for="consultant-expert">Select Expert *</label>
                <select id="consultant-expert" v-model="consultationForm.expert_id" required>
                  <option value="">Choose an expert</option>
                  <option v-for="expert in experts" :key="expert.id" :value="expert.id">
                    {{ expert.name }} - {{ expert.specialization || 'General Expert' }}
                  </option>
                </select>
                <span v-if="formErrors.expert_id" class="error-text">{{ formErrors.expert_id }}</span>
              </div>

              <!-- Title -->
              <div class="form-group">
                <label for="consultant-title">Consultation Title *</label>
                <input
                  id="consultant-title"
                  v-model="consultationForm.title"
                  type="text"
                  placeholder="e.g., Crop Disease Diagnosis"
                  required
                />
                <span v-if="formErrors.title" class="error-text">{{ formErrors.title }}</span>
              </div>

              <!-- Consultation Type -->
              <div class="form-row">
                <div class="form-group">
                  <label for="consultant-type">Consultation Type *</label>
                  <select id="consultant-type" v-model="consultationForm.consultation_type" required>
                    <option value="">Select type</option>
                    <option value="crop">Crop Management</option>
                    <option value="soil">Soil Management</option>
                    <option value="pest">Pest Control</option>
                    <option value="irrigation">Irrigation</option>
                    <option value="fertilizer">Fertilizer/Nutrition</option>
                    <option value="general">General Agriculture</option>
                  </select>
                  <span v-if="formErrors.consultation_type" class="error-text">{{ formErrors.consultation_type }}</span>
                </div>

                <div class="form-group">
                  <label for="consultant-priority">Priority Level *</label>
                  <select id="consultant-priority" v-model="consultationForm.priority" required>
                    <option value="">Select priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                  </select>
                  <span v-if="formErrors.priority" class="error-text">{{ formErrors.priority }}</span>
                </div>
              </div>

              <!-- Description -->
              <div class="form-group">
                <label for="consultant-description">Detailed Description *</label>
                <textarea
                  id="consultant-description"
                  v-model="consultationForm.description"
                  placeholder="Describe your issue in detail..."
                  rows="4"
                  required
                ></textarea>
                <span v-if="formErrors.description" class="error-text">{{ formErrors.description }}</span>
              </div>

              <!-- Budget and Date -->
              <div class="form-row">
                <div class="form-group">
                  <label for="consultant-budget">Budget (Optional)</label>
                  <div class="input-with-currency">
                    <span class="currency">$</span>
                    <input
                      id="consultant-budget"
                      v-model.number="consultationForm.budget"
                      type="number"
                      placeholder="0.00"
                      step="0.01"
                      min="0"
                    />
                  </div>
                </div>

                <div class="form-group">
                  <label for="consultant-date">Preferred Date (Optional)</label>
                  <input
                    id="consultant-date"
                    v-model="consultationForm.preferred_date"
                    type="date"
                  />
                </div>
              </div>

              <!-- Form Actions -->
              <div class="form-actions">
                <button type="button" class="btn-secondary" @click="closeRequestConsultationDialog">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="submitting">
                  {{ submitting ? 'Submitting...' : (editingConsultation ? 'Update Request' : 'Request Consultation') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- View Consultation Modal -->
      <div v-if="showDetailsModal" class="modal-overlay" @click="closeDetailsModal">
        <div class="modal-dialog modal-large" @click.stop>
          <div class="modal-header">
            <h2>{{ selectedConsultation?.title }}</h2>
            <button class="close-btn" @click="closeDetailsModal">&times;</button>
          </div>

          <div class="modal-content">
            <div class="details-grid">
              <div class="detail-section">
                <h3>Consultation Details</h3>
                <div class="detail-row">
                  <span class="label">Type:</span>
                  <span class="value">{{ formatType(selectedConsultation?.consultation_type) }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Priority:</span>
                  <span class="value priority-badge" :class="`priority-${selectedConsultation?.priority}`">
                    {{ capitalizeFirstLetter(selectedConsultation?.priority) }}
                  </span>
                </div>
                <div class="detail-row">
                  <span class="label">Status:</span>
                  <span class="value status-badge" :class="`status-${selectedConsultation?.status}`">
                    {{ formatStatus(selectedConsultation?.status) }}
                  </span>
                </div>
              </div>

              <div class="detail-section">
                <h3>Expert Information</h3>
                <div class="detail-row">
                  <span class="label">Name:</span>
                  <span class="value">{{ selectedConsultation?.expert?.name || 'Awaiting Assignment' }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Specialization:</span>
                  <span class="value">{{ selectedConsultation?.expert?.specialization || 'N/A' }}</span>
                </div>
              </div>

              <div class="detail-section full-width">
                <h3>Your Request</h3>
                <p class="description-text">{{ selectedConsultation?.description }}</p>
              </div>

              <div class="detail-section full-width">
                <h3>Messages ({{ selectedConsultation?.messages?.length || 0 }})</h3>
                <div v-if="selectedConsultation?.messages?.length" class="messages-list">
                  <div v-for="msg in selectedConsultation.messages" :key="msg.id" class="message-item" :class="{ 'message-expert': msg.sender_id !== auth.user?.id }">
                    <div class="message-sender">
                      <strong>{{ msg.sender?.name }}</strong>
                      <span class="message-time">{{ formatDate(msg.created_at) }}</span>
                    </div>
                    <div class="message-content">{{ msg.message }}</div>
                  </div>
                </div>
                <p v-else class="no-messages">No messages yet</p>
              </div>
            </div>

            <div class="modal-actions">
              <button class="btn-secondary" @click="closeDetailsModal">Close</button>
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

const router = useRouter()
const auth = useAuthStore()

// State
const consultations = ref([])
const experts = ref([])
const loading = ref(true)
const searchQuery = ref('')
const typeFilter = ref('')
const statusFilter = ref('')
const showRequestDialog = ref(false)
const showDetailsModal = ref(false)
const editingConsultation = ref(null)
const selectedConsultation = ref(null)
const submitting = ref(false)
const formErrors = ref({})

// Form
const consultationForm = ref({
  expert_id: '',
  title: '',
  description: '',
  consultation_type: '',
  priority: 'medium',
  budget: '',
  preferred_date: '',
})

// Computed
const filteredConsultations = computed(() => {
  return consultations.value.filter(c => {
    const matchesSearch = 
      c.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      c.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesType = !typeFilter.value || c.consultation_type === typeFilter.value
    const matchesStatus = !statusFilter.value || c.status === statusFilter.value
    
    return matchesSearch && matchesType && matchesStatus
  })
})

const pendingCount = computed(() => consultations.value.filter(c => c.status === 'pending').length)
const respondedCount = computed(() => consultations.value.filter(c => c.status === 'responded').length)
const resolvedCount = computed(() => consultations.value.filter(c => c.status === 'resolved').length)

// Lifecycle
onMounted(async () => {
  await fetchConsultations()
  await fetchExperts()
})

// API Functions
const fetchConsultations = async () => {
  try {
    loading.value = true
    const res = await fetch('/api/farmer/consultations', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    
    if (!res.ok) {
      consultations.value = []
      return
    }

    const data = await res.json()
    consultations.value = data.data?.data || data.data || []
  } catch (error) {
    console.error('Error fetching consultations:', error)
    consultations.value = []
  } finally {
    loading.value = false
  }
}

const fetchExperts = async () => {
  try {
    // Assuming there's an endpoint to get available experts
    const res = await fetch('/api/experts', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    
    if (res.ok) {
      const data = await res.json()
      experts.value = data.data?.data || data.data || []
    }
  } catch (error) {
    console.error('Error fetching experts:', error)
  }
}

// Modal Functions
const openRequestConsultationDialog = () => {
  resetConsultationForm()
  editingConsultation.value = null
  showRequestDialog.value = true
}

const closeRequestConsultationDialog = () => {
  showRequestDialog.value = false
  resetConsultationForm()
}

const resetConsultationForm = () => {
  consultationForm.value = {
    expert_id: '',
    title: '',
    description: '',
    consultation_type: '',
    priority: 'medium',
    budget: '',
    preferred_date: '',
  }
  formErrors.value = {}
}

const editConsultation = (consultation) => {
  editingConsultation.value = consultation
  consultationForm.value = {
    expert_id: consultation.expert_id || '',
    title: consultation.title,
    description: consultation.description,
    consultation_type: consultation.consultation_type,
    priority: consultation.priority,
    budget: consultation.budget || '',
    preferred_date: consultation.preferred_date || '',
  }
  showRequestDialog.value = true
}

const viewConsultation = (consultation) => {
  selectedConsultation.value = consultation
  showDetailsModal.value = true
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedConsultation.value = null
}

// Form Submission
const submitConsultationRequest = async () => {
  try {
    submitting.value = true
    formErrors.value = {}

    const payload = {
      ...consultationForm.value,
      expert_id: parseInt(consultationForm.value.expert_id),
      budget: consultationForm.value.budget ? parseFloat(consultationForm.value.budget) : null,
    }

    const url = editingConsultation.value 
      ? `/api/farmer/consultations/${editingConsultation.value.id}`
      : '/api/farmer/consultations'

    const method = editingConsultation.value ? 'PUT' : 'POST'

    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    })

    const data = await response.json()

    if (!response.ok) {
      formErrors.value = data.errors || { general: data.message || 'Error occurred' }
      return
    }

    showRequestDialog.value = false
    resetConsultationForm()
    await fetchConsultations()
  } catch (error) {
    console.error('Error:', error)
    formErrors.value = { general: 'An error occurred' }
  } finally {
    submitting.value = false
  }
}

const deleteConsultation = async (id) => {
  if (!confirm('Delete this consultation request?')) return

  try {
    const response = await fetch(`/api/farmer/consultations/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })

    if (response.ok) {
      await fetchConsultations()
    }
  } catch (error) {
    console.error('Error deleting:', error)
  }
}

// Utility Functions
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const formatStatus = (status) => {
  const statuses = {
    pending: 'Pending',
    responded: 'Expert Responded',
    resolved: 'Resolved',
    closed: 'Closed'
  }
  return statuses[status] || status
}

const formatType = (type) => {
  const types = {
    crop: 'Crop Management',
    soil: 'Soil Management',
    pest: 'Pest Control',
    irrigation: 'Irrigation',
    fertilizer: 'Fertilizer/Nutrition',
    general: 'General'
  }
  return types[type] || type
}

const capitalizeFirstLetter = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const truncateText = (text, length) => {
  return text.length > length ? text.substring(0, length) + '...' : text
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout { display: flex; height: 100vh; }
.farmer-page { margin-left: 260px; flex: 1; overflow-y: auto; background-color: #f5f5f5; padding: 20px; }

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  gap: 20px;
}

.header-content h1 {
  font-size: 32px;
  font-weight: bold;
  color: #333;
  margin: 0 0 5px 0;
}

.header-content p {
  color: #666;
  margin: 0;
}

.btn-large {
  padding: 12px 24px;
  font-size: 16px;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  gap: 15px;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.stat-card:hover { transform: translateY(-2px); }

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.stat-icon.total { background-color: #10b981; }
.stat-icon.pending { background-color: #f59e0b; }
.stat-icon.resolved { background-color: #8b5cf6; }
.stat-icon.responded { background-color: #3b82f6; }

.stat-content h3 {
  margin: 0;
  font-size: 12px;
  color: #666;
  text-transform: uppercase;
  font-weight: 600;
}

.stat-value {
  margin: 5px 0 0 0;
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

/* Controls */
.controls-section {
  background: white;
  border-radius: 8px;
  padding: 15px 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-group {
  display: flex;
  gap: 15px;
  align-items: center;
}

.search-input,
.type-select,
.status-select {
  padding: 10px 15px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.search-input:focus,
.type-select:focus,
.status-select:focus {
  outline: none;
  border-color: #10b981;
}

.search-input { flex: 1; min-width: 250px; }

/* Consultations Section */
.consultations-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.empty-state i {
  font-size: 48px;
  color: #d1d5db;
  margin-bottom: 15px;
  display: block;
}

.empty-state h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

/* Consultations List */
.consultations-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.consultation-card {
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  transition: all 0.3s;
}

.consultation-card:hover {
  border-color: #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
  transform: translateY(-2px);
}

.consultation-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
  gap: 10px;
}

.consultation-info h3 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 18px;
}

.type-badge {
  display: inline-block;
  padding: 4px 8px;
  background: #e0e7ff;
  color: #3b82f6;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  margin: 0;
}

.header-right {
  display: flex;
  gap: 8px;
  align-items: center;
}

.priority-badge {
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.priority-badge.priority-low { background: #d1fae5; color: #065f46; }
.priority-badge.priority-medium { background: #fef3c7; color: #92400e; }
.priority-badge.priority-high { background: #fee2e2; color: #991b1b; }
.priority-badge.priority-urgent { background: #f5d5d5; color: #7f1d1d; font-weight: 700; }

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending { background-color: #fef3c7; color: #92400e; }
.status-badge.status-responded { background-color: #dbeafe; color: #1e40af; }
.status-badge.status-resolved { background-color: #d1fae5; color: #065f46; }
.status-badge.status-closed { background-color: #f3f4f6; color: #6b7280; }

.consultation-body {
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e5e7eb;
}

.description {
  color: #555;
  font-size: 14px;
  line-height: 1.5;
  margin: 0 0 12px 0;
}

.consultation-meta {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #666;
}

.meta-item i {
  color: #10b981;
}

.consultation-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.message-count {
  font-size: 12px;
  color: #666;
  display: flex;
  align-items: center;
  gap: 5px;
}

.message-count i {
  color: #3b82f6;
}

.actions {
  display: flex;
  gap: 8px;
}

.btn-small {
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  min-width: 70px;
}

.btn-view {
  background-color: #8b5cf6;
  color: white;
}

.btn-view:hover {
  background-color: #7c3aed;
}

.btn-edit {
  background-color: #f59e0b;
  color: white;
}

.btn-edit:hover {
  background-color: #d97706;
}

.btn-delete {
  background-color: #ef4444;
  color: white;
}

.btn-delete:hover {
  background-color: #dc2626;
}

/* Buttons */
.btn-primary {
  background-color: #10b981;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover:not(:disabled) {
  background-color: #059669;
}

.btn-primary:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background-color: #4b5563;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-dialog {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 650px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-dialog.modal-large {
  max-width: 800px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.modal-header h2 {
  margin: 0;
  color: #333;
  font-size: 20px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: #333;
}

.modal-content {
  padding: 25px;
}

/* Form */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-group textarea {
  resize: vertical;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.input-with-currency {
  display: flex;
  align-items: center;
}

.currency {
  padding: 10px 12px;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-right: none;
  border-radius: 4px 0 0 4px;
  font-weight: 600;
  color: #333;
}

.input-with-currency input {
  border-radius: 0 4px 4px 0;
  margin: 0;
}

.error-text {
  display: block;
  color: #ef4444;
  font-size: 12px;
  margin-top: 5px;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 25px;
  padding-top: 15px;
  border-top: 1px solid #e5e7eb;
}

/* Details Grid */
.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 25px;
}

.detail-section {
  background: #f9fafb;
  padding: 15px;
  border-radius: 6px;
}

.detail-section.full-width {
  grid-column: 1 / -1;
}

.detail-section h3 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
  text-transform: uppercase;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row .label {
  color: #666;
  font-weight: 500;
  font-size: 13px;
}

.detail-row .value {
  color: #333;
  font-weight: 600;
  text-align: right;
}

.description-text {
  color: #555;
  line-height: 1.6;
  margin: 0;
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.message-item {
  background: white;
  padding: 12px;
  border-radius: 4px;
  border-left: 3px solid #10b981;
}

.message-item.message-expert {
  border-left-color: #8b5cf6;
  background: #f5f3ff;
}

.message-sender {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
  font-size: 12px;
}

.message-sender strong {
  color: #333;
}

.message-time {
  color: #999;
  font-size: 11px;
}

.message-content {
  color: #555;
  font-size: 13px;
  line-height: 1.5;
}

.no-messages {
  text-align: center;
  color: #999;
  padding: 15px;
  margin: 0;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  padding-top: 15px;
  border-top: 1px solid #e5e7eb;
}

/* Responsive */
@media (max-width: 768px) {
  .farmer-page { margin-left: 0; }
  .page-header { flex-direction: column; align-items: stretch; }
  .btn-large { width: 100%; text-align: center; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .filter-group { flex-direction: column; }
  .search-input { width: 100%; }
  .form-row { grid-template-columns: 1fr; }
  .modal-dialog { width: 95%; max-height: 95vh; }
  .consultation-header { flex-direction: column; }
  .header-right { width: 100%; }
}
</style>
