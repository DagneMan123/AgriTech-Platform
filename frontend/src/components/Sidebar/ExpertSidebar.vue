<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Expert Portal</h2>
      <p>Agricultural Consultation</p>
    </div>

    <nav class="sidebar-menu">
      <!-- Dashboard -->
      <router-link to="/expert/dashboard" class="menu-item main-dashboard-link" active-class="active">
        <LayoutDashboard class="sub-icon" />
        <span>Dashboard</span>
      </router-link>

      <!-- Consultations -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('consultations')">
          <span class="section-title-wrapper">
            <Handshake class="section-icon" />
            <span>Consultations</span>
          </span>
          <component :is="expandedSections.consultations ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.consultations" class="section-items">
          <router-link to="/expert/consultations/requests" class="menu-item sub-item" active-class="active">
            <Inbox class="sub-icon" />
            <span>Consultation Requests</span>
          </router-link>
          <router-link to="/expert/consultations/scheduled" class="menu-item sub-item" active-class="active">
            <Calendar class="sub-icon" />
            <span>Scheduled Sessions</span>
          </router-link>
          <router-link to="/expert/consultations/completed" class="menu-item sub-item" active-class="active">
            <CheckCircle2 class="sub-icon" />
            <span>Completed Sessions</span>
          </router-link>
          <router-link to="/expert/consultations/notes" class="menu-item sub-item" active-class="active">
            <FileText class="sub-icon" />
            <span>Expert Notes</span>
          </router-link>
        </div>
      </div>

      <!-- Advisory & Content -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('advisory')">
          <span class="section-title-wrapper">
            <BookOpen class="section-icon" />
            <span>Advisory & Content</span>
          </span>
          <component :is="expandedSections.advisory ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.advisory" class="section-items">
          <router-link to="/expert/advisory/publish" class="menu-item sub-item" active-class="active">
            <PenTool class="sub-icon" />
            <span>Publish Articles</span>
          </router-link>
          <router-link to="/expert/advisory/training" class="menu-item sub-item" active-class="active">
            <Book class="sub-icon" />
            <span>Training Materials</span>
          </router-link>
          <router-link to="/expert/advisory/alerts" class="menu-item sub-item" active-class="active">
            <AlertTriangle class="sub-icon" />
            <span>Pest & Disease Alerts</span>
          </router-link>
        </div>
      </div>

      <!-- Farm Monitoring -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('monitoring')">
          <span class="section-title-wrapper">
            <MapPin class="section-icon" />
            <span>Farm Monitoring</span>
          </span>
          <component :is="expandedSections.monitoring ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.monitoring" class="section-items">
          <router-link to="/expert/monitoring/assigned" class="menu-item sub-item" active-class="active">
            <LandPlot class="sub-icon" />
            <span>Assigned Farms</span>
          </router-link>
          <router-link to="/expert/monitoring/reports" class="menu-item sub-item" active-class="active">
            <ClipboardList class="sub-icon" />
            <span>Soil & Crop Reports</span>
          </router-link>
          <router-link to="/expert/monitoring/weather" class="menu-item sub-item" active-class="active">
            <CloudSun class="sub-icon" />
            <span>Weather Insights</span>
          </router-link>
        </div>
      </div>

      <!-- Community -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('community')">
          <span class="section-title-wrapper">
            <Users class="section-icon" />
            <span>Community</span>
          </span>
          <component :is="expandedSections.community ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.community" class="section-items">
          <router-link to="/expert/community/qa" class="menu-item sub-item" active-class="active">
            <HelpCircle class="sub-icon" />
            <span>Farmer Q&A</span>
          </router-link>
          <router-link to="/expert/community/forums" class="menu-item sub-item" active-class="active">
            <MessageSquare class="sub-icon" />
            <span>Forums</span>
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Account -->
    <div class="sidebar-bottom">
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('account')">
          <span class="section-title-wrapper">
            <UserCog class="section-icon" />
            <span>Account</span>
          </span>
          <component :is="expandedSections.account ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.account" class="section-items">
          <router-link to="/expert/account/credentials" class="menu-item sub-item" active-class="active">
            <Award class="sub-icon" />
            <span>Expert Credentials</span>
          </router-link>
          <router-link to="/expert/account/notifications" class="menu-item sub-item" active-class="active">
            <Bell class="sub-icon" />
            <span>Notifications</span>
          </router-link>
          <router-link to="/expert/account/messages" class="menu-item sub-item" active-class="active">
            <Mail class="sub-icon" />
            <span>Messages</span>
          </router-link>
          <router-link to="/expert/account/profile" class="menu-item sub-item" active-class="active">
            <User class="sub-icon" />
            <span>Profile</span>
          </router-link>
          <button class="menu-item sub-item logout-btn" @click="emit('logout')" type="button">
            <LogOut class="sub-icon" />
            <span>Logout</span>
          </button>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { 
  ChevronRight, ChevronDown, LayoutDashboard, Handshake, Inbox, 
  Calendar, CheckCircle2, FileText, BookOpen, PenTool, Book, 
  AlertTriangle, MapPin, LandPlot, ClipboardList, CloudSun, 
  Users, HelpCircle, MessageSquare, UserCog, Award, Bell, 
  Mail, User, LogOut 
} from 'lucide-vue-next'

const emit = defineEmits(['logout'])

const expandedSections = ref({
  consultations: true,
  advisory: false,
  monitoring: false,
  community: false,
  account: false
})

const toggleSection = (section) => {
  expandedSections.value[section] = !expandedSections.value[section]
}
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

.menu-section {
  margin: 5px 0;
}

.section-toggle {
  padding: 12px 20px;
  color: #4b5563;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.3s;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.section-title-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-toggle:hover {
  color: #1f2937;
  background: #f9fafb;
}

.section-icon {
  width: 16px;
  height: 16px;
  color: #9ca3af;
}

.toggle-icon {
  width: 16px;
  height: 16px;
  color: #4b5563;
}

.section-items {
  display: flex;
  flex-direction: column;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
  }
  to {
    opacity: 1;
    max-height: 500px;
  }
}

.menu-item {
  padding: 10px 20px 10px 44px;
  color: #6b7280;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  transition: all 0.2s;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  position: relative;
}

.main-dashboard-link {
  padding: 12px 20px;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 5px;
  padding-left: 20px;
}

.main-dashboard-link:hover {
  color: #1f2937;
  background: #f9fafb;
}

.main-dashboard-link .sub-icon {
  color: #9ca3af;
  width: 16px;
  height: 16px;
}

.main-dashboard-link.active {
  color: #10b981;
  background: #ecfdf5;
  font-weight: 500;
}

.main-dashboard-link.active .sub-icon {
  color: #10b981;
}

.main-dashboard-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #10b981;
}

.sub-icon {
  width: 15px;
  height: 15px;
  color: #9ca3af;
}

.menu-item:hover {
  color: #1f2937;
  background: #f3f4f6;
}

.menu-item:hover .sub-icon {
  color: #1f2937;
}

.menu-item.active {
  color: #10b981;
  background: #ecfdf5;
  font-weight: 500;
}

.menu-item.active .sub-icon {
  color: #10b981;
}

.menu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #10b981;
}

.sidebar-bottom {
  padding: 10px 0;
  border-top: 1px solid #e5e7eb;
}

.logout-btn {
  color: #ef4444 !important;
}

.logout-btn .sub-icon {
  color: #ef4444 !important;
}

.logout-btn:hover {
  background: #fee2e2 !important;
}
</style>