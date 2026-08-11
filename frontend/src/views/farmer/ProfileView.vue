<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <h1>My Profile</h1>
        <p>Manage your account information</p>
      </div>

      <!-- Profile Header Section -->
      <div class="profile-header">
        <div class="profile-avatar">
          <img v-if="profileData?.avatar" :src="profileData.avatar" :alt="profileData.name">
          <div v-else class="avatar-placeholder">
            <i class="fas fa-user"></i>
          </div>
        </div>
        <div class="profile-info">
          <h2>{{ profileData?.name || 'Farmer Name' }}</h2>
          <p class="role">{{ profileData?.role || 'Farmer' }}</p>
          <p class="email">{{ profileData?.email || 'email@example.com' }}</p>
          <button class="btn-primary" @click="editMode = !editMode">
            {{ editMode ? 'Cancel' : 'Edit Profile' }}
          </button>
        </div>
      </div>

      <!-- Personal Information Section -->
      <div class="content-section">
        <h2>Personal Information</h2>
        <div class="form-grid">
          <div class="form-group">
            <label>Full Name</label>
            <input v-if="editMode" v-model="formData.name" type="text" class="form-input">
            <p v-else class="form-value">{{ profileData?.name }}</p>
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input v-if="editMode" v-model="formData.email" type="email" class="form-input">
            <p v-else class="form-value">{{ profileData?.email }}</p>
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input v-if="editMode" v-model="formData.phone" type="tel" class="form-input">
            <p v-else class="form-value">{{ profileData?.phone || 'Not provided' }}</p>
          </div>
          <div class="form-group">
            <label>Date of Birth</label>
            <input v-if="editMode" v-model="formData.dob" type="date" class="form-input">
            <p v-else class="form-value">{{ profileData?.dob || 'Not provided' }}</p>
          </div>
        </div>
      </div>

      <!-- Farm Information Section -->
      <div class="content-section">
        <h2>Farm Information</h2>
        <div class="form-grid">
          <div class="form-group">
            <label>Farm Name</label>
            <input v-if="editMode" v-model="formData.farmName" type="text" class="form-input">
            <p v-else class="form-value">{{ profileData?.farmName || 'Not provided' }}</p>
          </div>
          <div class="form-group">
            <label>Location/Region</label>
            <input v-if="editMode" v-model="formData.location" type="text" class="form-input">
            <p v-else class="form-value">{{ profileData?.location || 'Not provided' }}</p>
          </div>
          <div class="form-group">
            <label>Farm Size (hectares)</label>
            <input v-if="editMode" v-model="formData.farmSize" type="number" class="form-input">
            <p v-else class="form-value">{{ profileData?.farmSize || '0' }} ha</p>
          </div>
          <div class="form-group">
            <label>Primary Crops</label>
            <input v-if="editMode" v-model="formData.crops" type="text" class="form-input" placeholder="e.g., Maize, Wheat, Tomatoes">
            <p v-else class="form-value">{{ profileData?.crops || 'Not provided' }}</p>
          </div>
        </div>
      </div>

      <!-- Contact Information Section -->
      <div class="content-section">
        <h2>Contact Information</h2>
        <div class="form-grid">
          <div class="form-group">
            <label>Address</label>
            <input v-if="editMode" v-model="formData.address" type="text" class="form-input">
            <p v-else class="form-value">{{ profileData?.address || 'Not provided' }}</p>
          </div>
          <div class="form-group">
            <label>City</label>
            <input v-if="editMode" v-model="formData.city" type="text" class="form-input">
            <p v-else class="form-value">{{ profileData?.city || 'Not provided' }}</p>
          </div>
          <div class="form-group">
            <label>State/Province</label>
            <input v-if="editMode" v-model="formData.state" type="text" class="form-input">
            <p v-else class="form-value">{{ profileData?.state || 'Not provided' }}</p>
          </div>
          <div class="form-group">
            <label>Postal Code</label>
            <input v-if="editMode" v-model="formData.postalCode" type="text" class="form-input">
            <p v-else class="form-value">{{ profileData?.postalCode || 'Not provided' }}</p>
          </div>
        </div>
      </div>

      <!-- Bank Information Section -->
      <div class="content-section">
        <h2>Bank Information</h2>
        <div class="form-grid">
          <div class="form-group">
            <label>Bank Name</label>
            <input v-if="editMode" v-model="formData.bankName" type="text" class="form-input">
            <p v-else class="form-value">{{ profileData?.bankName || 'Not provided' }}</p>
          </div>
          <div class="form-group">
            <label>Account Number</label>
            <input v-if="editMode" v-model="formData.accountNumber" type="text" class="form-input">
            <p v-else class="form-value">{{ profileData?.accountNumber ? '****' + profileData.accountNumber.slice(-4) : 'Not provided' }}</p>
          </div>
          <div class="form-group">
            <label>Account Holder Name</label>
            <input v-if="editMode" v-model="formData.accountHolder" type="text" class="form-input">
            <p v-else class="form-value">{{ profileData?.accountHolder || 'Not provided' }}</p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div v-if="editMode" class="action-buttons">
        <button class="btn-primary" @click="saveProfile">Save Changes</button>
        <button class="btn-secondary" @click="editMode = false">Cancel</button>
      </div>

      <!-- Additional Sections -->
      <div class="content-section">
        <h2>Account Settings</h2>
        <div class="settings-list">
          <div class="setting-item">
            <div class="setting-info">
              <h4>Change Password</h4>
              <p>Update your account password</p>
            </div>
            <button class="btn-small">Change</button>
          </div>
          <div class="setting-item">
            <div class="setting-info">
              <h4>Two-Factor Authentication</h4>
              <p>Enable 2FA for added security</p>
            </div>
            <button class="btn-small">Enable</button>
          </div>
          <div class="setting-item">
            <div class="setting-info">
              <h4>Privacy Settings</h4>
              <p>Control who can see your profile</p>
            </div>
            <button class="btn-small">Configure</button>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="content-section danger-zone">
        <h2>Danger Zone</h2>
        <div class="danger-action">
          <div class="danger-info">
            <h4>Delete Account</h4>
            <p>Permanently delete your account and all associated data</p>
          </div>
          <button class="btn-danger">Delete Account</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()
const editMode = ref(false)

const profileData = ref({
  name: 'John Farmer',
  email: 'john@farm.com',
  phone: '+1 234 567 8900',
  dob: '1985-06-15',
  farmName: 'Green Valley Farm',
  location: 'Midwest Region',
  farmSize: 150,
  crops: 'Maize, Wheat, Soybeans',
  address: '123 Farm Road',
  city: 'Springfield',
  state: 'Illinois',
  postalCode: '62701',
  bankName: 'Farmers Bank',
  accountNumber: '1234567890',
  accountHolder: 'John Farmer',
  role: 'Farmer',
  avatar: null
})

const formData = reactive({
  name: profileData.value.name,
  email: profileData.value.email,
  phone: profileData.value.phone,
  dob: profileData.value.dob,
  farmName: profileData.value.farmName,
  location: profileData.value.location,
  farmSize: profileData.value.farmSize,
  crops: profileData.value.crops,
  address: profileData.value.address,
  city: profileData.value.city,
  state: profileData.value.state,
  postalCode: profileData.value.postalCode,
  bankName: profileData.value.bankName,
  accountNumber: profileData.value.accountNumber,
  accountHolder: profileData.value.accountHolder
})

onMounted(() => {
  fetchProfileData()
})

const fetchProfileData = async () => {
  try {
    // In a real app, fetch from API
    // const res = await fetch('/api/farmer/profile', {
    //   headers: { 'Authorization': `Bearer ${auth.token}` }
    // })
    // profileData.value = await res.json()
    console.log('Profile data loaded')
  } catch (error) {
    console.error('Error fetching profile:', error)
  }
}

const saveProfile = async () => {
  try {
    // In a real app, save to API
    // const res = await fetch('/api/farmer/profile', {
    //   method: 'PUT',
    //   headers: { 'Authorization': `Bearer ${auth.token}`, 'Content-Type': 'application/json' },
    //   body: JSON.stringify(formData)
    // })
    
    // Update local data
    Object.assign(profileData.value, formData)
    editMode.value = false
    console.log('Profile saved successfully')
  } catch (error) {
    console.error('Error saving profile:', error)
  }
}

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

/* Profile Header */
.profile-header { background: white; border-radius: 8px; padding: 30px; display: flex; align-items: center; gap: 30px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.profile-avatar { width: 150px; height: 150px; border-radius: 50%; overflow: hidden; background: #f3f4f6; display: flex; align-items: center; justify-content: center; }
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.avatar-placeholder { font-size: 60px; color: #d1d5db; }
.profile-info { flex: 1; }
.profile-info h2 { margin: 0 0 10px 0; color: #333; font-size: 24px; }
.profile-info .role { margin: 5px 0; color: #10b981; font-weight: 600; }
.profile-info .email { margin: 5px 0; color: #666; }
.btn-primary { background-color: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; margin-top: 10px; }
.btn-primary:hover { background-color: #059669; }

/* Content Section */
.content-section { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.content-section h2 { font-size: 20px; font-weight: bold; color: #333; margin-bottom: 20px; }

/* Form Grid */
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
.form-group { display: flex; flex-direction: column; }
.form-group label { font-weight: 600; color: #333; margin-bottom: 8px; font-size: 14px; }
.form-input { padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 14px; }
.form-input:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
.form-value { margin: 0; color: #666; font-size: 14px; }

/* Action Buttons */
.action-buttons { display: flex; gap: 10px; margin-bottom: 20px; }
.btn-secondary { background-color: #e5e7eb; color: #333; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
.btn-secondary:hover { background-color: #d1d5db; }

/* Settings List */
.settings-list { display: flex; flex-direction: column; gap: 15px; }
.setting-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; background-color: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; }
.setting-info h4 { margin: 0 0 5px 0; color: #333; font-size: 14px; }
.setting-info p { margin: 0; color: #666; font-size: 12px; }
.btn-small { padding: 8px 16px; background-color: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; }
.btn-small:hover { background-color: #059669; }

/* Danger Zone */
.danger-zone { border-left: 4px solid #ef4444; }
.danger-zone h2 { color: #ef4444; }
.danger-action { display: flex; justify-content: space-between; align-items: center; padding: 15px; background-color: #fee2e2; border-radius: 8px; }
.danger-info h4 { margin: 0 0 5px 0; color: #991b1b; font-size: 14px; }
.danger-info p { margin: 0; color: #7f1d1d; font-size: 12px; }
.btn-danger { padding: 10px 20px; background-color: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
.btn-danger:hover { background-color: #dc2626; }

@media (max-width: 768px) {
  .farmer-page { margin-left: 0; }
  .profile-header { flex-direction: column; text-align: center; }
  .form-grid { grid-template-columns: 1fr; }
  .danger-action { flex-direction: column; align-items: flex-start; gap: 15px; }
}
</style>
