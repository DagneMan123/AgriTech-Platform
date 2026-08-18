<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <h1>My Profile</h1>
        <p>Manage your farmer profile and account settings</p>
      </div>

      <div class="profile-container">
        <div class="profile-card">
          <div class="profile-header">
            <img :src="profile.profile_image || defaultAvatar" :alt="profile.name" class="profile-image" />
            <div class="profile-info">
              <h2>{{ profile.name }}</h2>
              <p class="role-badge">Farmer</p>
              <p class="email">{{ profile.email }}</p>
              <button class="btn-edit-photo" @click="triggerImageUpload">Change Photo</button>
              <input
                ref="imageInput"
                type="file"
                accept="image/*"
                style="display: none"
                @change="uploadProfileImage"
              />
            </div>
          </div>
        </div>

        <div class="two-column-layout">
          <!-- Personal Information -->
          <div class="info-section">
            <h3>Personal Information</h3>
            <div class="info-group">
              <label>Full Name</label>
              <input v-model="editForm.name" type="text" placeholder="Full Name" />
            </div>
            <div class="info-group">
              <label>Email</label>
              <input v-model="editForm.email" type="email" placeholder="Email" disabled />
            </div>
            <div class="info-group">
              <label>Phone</label>
              <input v-model="editForm.phone" type="tel" placeholder="Phone Number" />
            </div>
            <div class="info-group">
              <label>Location</label>
              <input v-model="editForm.location" type="text" placeholder="City/Town" />
            </div>
          </div>

          <!-- Farm Location -->
          <div class="info-section">
            <h3>Farm Location</h3>
            <div class="info-group">
              <label>Region</label>
              <input v-model="editForm.region" type="text" placeholder="Region" />
            </div>
            <div class="info-group">
              <label>Zone</label>
              <input v-model="editForm.zone" type="text" placeholder="Zone" />
            </div>
            <div class="info-group">
              <label>Woreda</label>
              <input v-model="editForm.woreda" type="text" placeholder="Woreda/District" />
            </div>
            <div class="info-group">
              <label>Address</label>
              <textarea v-model="editForm.address" placeholder="Full Address" rows="3"></textarea>
            </div>
          </div>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
          <div class="stat-card">
            <h4>Total Farms</h4>
            <p class="stat-value">{{ stats.total_farms }}</p>
          </div>
          <div class="stat-card">
            <h4>Active Crops</h4>
            <p class="stat-value">{{ stats.active_crops }}</p>
          </div>
          <div class="stat-card">
            <h4>Total Harvests</h4>
            <p class="stat-value">{{ stats.total_harvests }}</p>
          </div>
          <div class="stat-card">
            <h4>Total Area (ha)</h4>
            <p class="stat-value">{{ stats.total_area }}</p>
          </div>
        </div>

        <!-- Account Settings -->
        <div class="settings-section">
          <h3>Account Settings</h3>
          <div class="setting-item">
            <span>Member Since</span>
            <span>{{ formatDate(profile.created_at) }}</span>
          </div>
          <div class="setting-item">
            <span>Last Login</span>
            <span>{{ profile.last_login_at ? formatDate(profile.last_login_at) : 'N/A' }}</span>
          </div>
          <div class="setting-item">
            <span>Account Status</span>
            <span :class="profile.is_active ? 'status-active' : 'status-inactive'">
              {{ profile.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button class="btn-primary" @click="saveProfile" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save Changes' }}
          </button>
          <button class="btn-secondary" @click="changePassword">Change Password</button>
          <button class="btn-danger" @click="deleteAccount">Delete Account</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import apiClient from '@/api/config'

const router = useRouter()
const auth = useAuthStore()
const imageInput = ref(null)

const profile = ref({
  name: '',
  email: '',
  phone: '',
  location: '',
  region: '',
  zone: '',
  woreda: '',
  address: '',
  profile_image: null,
  created_at: '',
  last_login_at: '',
  is_active: true
})

const editForm = ref({...profile.value})

const stats = ref({
  total_farms: 0,
  active_crops: 0,
  total_harvests: 0,
  total_area: 0
})

const saving = ref(false)
const defaultAvatar = 'https://ui-avatars.com/api/?name=Farmer&background=10b981&color=fff'

onMounted(async () => {
  await loadProfile()
  await loadStats()
})

const loadProfile = async () => {
  try {
    const response = await apiClient.get('/auth/me')
    profile.value = response.data.data
    editForm.value = {...profile.value}
  } catch (error) {
    console.error('Error loading profile:', error)
  }
}

const loadStats = async () => {
  try {
    const response = await apiClient.get('/farmer/farms')
    const farmsData = response.data.data
    stats.value.total_farms = farmsData.total || 0
    stats.value.total_area = farmsData.data.reduce((sum, farm) => sum + (farm.size_hectares || 0), 0)
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

const triggerImageUpload = () => {
  imageInput.value.click()
}

const uploadProfileImage = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('profile_image', file)

  try {
    const response = await apiClient.post('/auth/update-profile-image', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    profile.value.profile_image = response.data.data.profile_image
    editForm.value.profile_image = profile.value.profile_image
  } catch (error) {
    console.error('Error uploading image:', error)
  }
}

const saveProfile = async () => {
  try {
    saving.value = true
    const response = await apiClient.put('/auth/update-profile', editForm.value)
    profile.value = response.data.data
    editForm.value = {...profile.value}
    alert('Profile updated successfully!')
  } catch (error) {
    console.error('Error saving profile:', error)
    alert('Error saving profile')
  } finally {
    saving.value = false
  }
}

const changePassword = () => {
  router.push('/app/change-password')
}

const deleteAccount = () => {
  if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
    apiClient.delete('/auth/delete-account')
      .then(() => {
        auth.logout()
        router.push('/')
      })
      .catch(error => console.error('Error deleting account:', error))
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout {
  display: flex;
  height: 100vh;
}

.farmer-page {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f5f5;
  padding: 20px;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.page-header p {
  color: #666;
}

.profile-container {
  max-width: 1200px;
  margin: 0 auto;
}

.profile-card {
  background: white;
  border-radius: 8px;
  padding: 30px;
  margin-bottom: 30px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-header {
  display: flex;
  gap: 30px;
  align-items: flex-start;
}

.profile-image {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #10b981;
}

.profile-info h2 {
  font-size: 24px;
  font-weight: bold;
  color: #333;
  margin: 0 0 10px 0;
}

.role-badge {
  display: inline-block;
  background-color: #10b981;
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 10px;
}

.email {
  color: #666;
  margin-bottom: 15px;
}

.btn-edit-photo {
  background-color: #3b82f6;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
}

.btn-edit-photo:hover {
  background-color: #2563eb;
}

.two-column-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 30px;
}

.info-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-section h3 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 18px;
}

.info-group {
  margin-bottom: 15px;
}

.info-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.info-group input,
.info-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
}

.info-group input:disabled {
  background-color: #f3f4f6;
  cursor: not-allowed;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-card h4 {
  margin: 0 0 10px 0;
  color: #666;
  font-size: 14px;
}

.stat-value {
  margin: 0;
  font-size: 32px;
  font-weight: bold;
  color: #10b981;
}

.settings-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 30px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.settings-section h3 {
  margin: 0 0 20px 0;
  color: #333;
  font-size: 18px;
}

.setting-item {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #e5e7eb;
}

.setting-item:last-child {
  border-bottom: none;
}

.status-active {
  color: #10b981;
  font-weight: 600;
}

.status-inactive {
  color: #ef4444;
  font-weight: 600;
}

.action-buttons {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.btn-primary {
  background-color: #10b981;
  color: white;
  padding: 12px 30px;
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
  padding: 12px 30px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background-color: #4b5563;
}

.btn-danger {
  background-color: #ef4444;
  color: white;
  padding: 12px 30px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-danger:hover {
  background-color: #dc2626;
}

@media (max-width: 768px) {
  .farmer-page {
    margin-left: 0;
  }

  .profile-header {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .two-column-layout {
    grid-template-columns: 1fr;
  }

  .action-buttons {
    flex-direction: column;
  }

  .action-buttons button {
    width: 100%;
  }
}
</style>
