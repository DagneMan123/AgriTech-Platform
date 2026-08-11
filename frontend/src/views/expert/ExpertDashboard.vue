<template>
  <div class="expert-layout">
    <ExpertSidebar @logout="handleLogout" />
    <div class="expert-dashboard">
      <!-- Header -->
      <div class="dashboard-header">
        <h1>Expert Dashboard</h1>
        <p>Manage consultations and agricultural content</p>
      </div>

      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="card-icon consultations">
            <i class="fas fa-comments"></i>
          </div>
          <div class="card-content">
            <h3>Total Consultations</h3>
            <p class="card-value">{{ dashboard?.summary.total_consultations || 0 }}</p>
            <p class="card-sub">All time</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon pending">
            <i class="fas fa-hourglass-half"></i>
          </div>
          <div class="card-content">
            <h3>Pending Requests</h3>
            <p class="card-value">{{ dashboard?.summary.pending_consultations || 0 }}</p>
            <p class="card-sub">Awaiting response</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon completed">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="card-content">
            <h3>Completed</h3>
            <p class="card-value">{{ dashboard?.summary.completed_consultations || 0 }}</p>
            <p class="card-sub">Finished</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon training">
            <i class="fas fa-graduation-cap"></i>
          </div>
          <div class="card-content">
            <h3>Training Materials</h3>
            <p class="card-value">{{ dashboard?.summary.total_training || 0 }}</p>
            <p class="card-sub">Published</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon articles">
            <i class="fas fa-file-alt"></i>
          </div>
          <div class="card-content">
            <h3>Articles</h3>
            <p class="card-value">{{ dashboard?.summary.total_articles || 0 }}</p>
            <p class="card-sub">Published</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon rating">
            <i class="fas fa-star"></i>
          </div>
          <div class="card-content">
            <h3>Avg Rating</h3>
            <p class="card-value">{{ dashboard?.summary.avg_rating || 0 }}/5</p>
            <p class="card-sub">From farmers</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="dashboard-tabs">
        <div class="tab-buttons">
          <button 
            v-for="tab in tabs" 
            :key="tab"
            :class="['tab-btn', { active: activeTab === tab }]"
            @click="activeTab = tab"
          >
            {{ formatTabName(tab) }}
          </button>
        </div>

        <!-- Consultations Tab -->
        <div v-show="activeTab === 'consultations'" class="tab-content">
          <div class="section-header">
            <h2>Consultations Management</h2>
            <div class="filter-controls">
              <select v-model="consultationFilter" class="filter-select">
                <option value="">All Consultations</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
              </select>
            </div>
          </div>

          <div class="consultations-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Farmer</th>
                  <th>Topic</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="consultation in dashboard?.consultations || []" :key="consultation.id">
                  <td>#{{ consultation.id }}</td>
                  <td>{{ consultation.farmer_name }}</td>
                  <td>{{ consultation.topic }}</td>
                  <td><span :class="['status-badge', `status-${consultation.status}`]">{{ consultation.status }}</span></td>
                  <td>{{ formatDate(consultation.created_at) }}</td>
                  <td class="action-buttons">
                    <button v-if="consultation.status === 'pending'" class="btn-small btn-respond">Respond</button>
                    <button v-else class="btn-small btn-view">View</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Training Materials Tab -->
        <div v-show="activeTab === 'training'" class="tab-content">
          <div class="section-header">
            <h2>Training Materials</h2>
            <button class="btn-primary" @click="showTrainingDialog = true">+ Create Training</button>
          </div>

          <div class="training-grid">
            <div v-for="training in dashboard?.training_materials || []" :key="training.id" class="training-card">
              <div class="training-header">
                <h4>{{ training.title }}</h4>
                <span :class="['status-badge', `status-${training.status}`]">{{ training.status }}</span>
              </div>
              <p class="training-description">{{ training.description }}</p>
              <div class="training-stats">
                <span><i class="fas fa-eye"></i> {{ training.views || 0 }} views</span>
                <span><i class="fas fa-download"></i> {{ training.downloads || 0 }} downloads</span>
              </div>
              <div class="training-actions">
                <button class="btn-small btn-edit">Edit</button>
                <button class="btn-small btn-delete">Delete</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Articles Tab -->
        <div v-show="activeTab === 'articles'" class="tab-content">
          <div class="section-header">
            <h2>Articles</h2>
            <button class="btn-primary" @click="showArticleDialog = true">+ Write Article</button>
          </div>

          <div class="articles-list">
            <div v-for="article in dashboard?.articles || []" :key="article.id" class="article-item">
              <div class="article-header">
                <div>
                  <h3>{{ article.title }}</h3>
                  <p class="article-meta">{{ formatDate(article.created_at) }} • {{ article.category }}</p>
                </div>
                <span :class="['status-badge', `status-${article.status}`]">{{ article.status }}</span>
              </div>
              <p class="article-excerpt">{{ article.excerpt }}</p>
              <div class="article-engagement">
                <span><i class="fas fa-heart"></i> {{ article.likes || 0 }}</span>
                <span><i class="fas fa-comment"></i> {{ article.comments || 0 }}</span>
                <span><i class="fas fa-share"></i> {{ article.shares || 0 }}</span>
              </div>
              <div class="article-actions">
                <button class="btn-small btn-edit">Edit</button>
                <button class="btn-small btn-preview">Preview</button>
                <button class="btn-small btn-delete">Delete</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Engagement Tab -->
        <div v-show="activeTab === 'engagement'" class="tab-content">
          <div class="section-header">
            <h2>Engagement & Analytics</h2>
            <div class="period-selector">
              <button 
                v-for="period in [7, 30, 90]"
                :key="period"
                :class="['period-btn', { active: selectedPeriod === period }]"
                @click="selectedPeriod = period"
              >
                {{ period }} Days
              </button>
            </div>
          </div>

          <div class="engagement-grid">
            <div class="engagement-card">
              <h3>Unique Farmers Reached</h3>
              <p class="big-number">{{ dashboard?.engagement.unique_farmers || 0 }}</p>
            </div>
            <div class="engagement-card">
              <h3>Total Interactions</h3>
              <p class="big-number">{{ dashboard?.engagement.total_interactions || 0 }}</p>
            </div>
            <div class="engagement-card">
              <h3>Avg Response Time</h3>
              <p class="big-number">{{ dashboard?.engagement.avg_response_time || 0 }}h</p>
            </div>
            <div class="engagement-card">
              <h3>Satisfaction Rate</h3>
              <p class="big-number">{{ dashboard?.engagement.satisfaction_rate || 0 }}%</p>
            </div>
          </div>

          <div class="top-content">
            <div class="top-card">
              <h3>Top Training Materials</h3>
              <div class="top-list">
                <div v-for="item in dashboard?.top_training || []" :key="item.id" class="top-item">
                  <span class="item-rank">{{ item.rank }}</span>
                  <span class="item-title">{{ item.title }}</span>
                  <span class="item-metric">{{ item.views }} views</span>
                </div>
              </div>
            </div>

            <div class="top-card">
              <h3>Top Articles</h3>
              <div class="top-list">
                <div v-for="item in dashboard?.top_articles || []" :key="item.id" class="top-item">
                  <span class="item-rank">{{ item.rank }}</span>
                  <span class="item-title">{{ item.title }}</span>
                  <span class="item-metric">{{ item.likes }} likes</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Farm Visits Tab -->
        <div v-show="activeTab === 'visits'" class="tab-content">
          <div class="section-header">
            <h2>Farm Visits</h2>
            <button class="btn-primary" @click="showVisitDialog = true">+ Schedule Visit</button>
          </div>

          <div class="visits-timeline">
            <div v-for="visit in dashboard?.farm_visits || []" :key="visit.id" class="visit-card">
              <div class="visit-date">
                <span class="date-badge">{{ formatDate(visit.visit_date) }}</span>
              </div>
              <div class="visit-details">
                <h4>{{ visit.farm_name }}</h4>
                <p><strong>Farmer:</strong> {{ visit.farmer_name }}</p>
                <p><strong>Location:</strong> {{ visit.location }}</p>
                <p><strong>Purpose:</strong> {{ visit.purpose }}</p>
                <div class="visit-actions">
                  <button class="btn-small btn-complete" v-if="visit.status !== 'completed'">Mark Complete</button>
                  <button class="btn-small btn-report" v-if="visit.status === 'completed'">View Report</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="recent-section">
        <div class="recent-card">
          <h2>Recent Consultations</h2>
          <div class="list-items">
            <div v-for="consultation in dashboard?.recent_consultations?.slice(0, 5) || []" :key="consultation.id" class="list-item">
              <span class="item-name">{{ consultation.farmer_name }}</span>
              <span class="item-topic">{{ consultation.topic }}</span>
              <span :class="['item-status', `status-${consultation.status}`]">{{ consultation.status }}</span>
            </div>
          </div>
        </div>

        <div class="recent-card">
          <h2>Content Performance</h2>
          <div class="list-items">
            <div class="list-item">
              <span class="label">Total Views:</span>
              <span class="value">{{ dashboard?.content_performance?.total_views || 0 }}</span>
            </div>
            <div class="list-item">
              <span class="label">Total Downloads:</span>
              <span class="value">{{ dashboard?.content_performance?.total_downloads || 0 }}</span>
            </div>
            <div class="list-item">
              <span class="label">Engagement Rate:</span>
              <span class="value">{{ dashboard?.content_performance?.engagement_rate || 0 }}%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import ExpertSidebar from '@/components/Sidebar/ExpertSidebar.vue'

const auth = useAuthStore()
const router = useRouter()
const activeTab = ref('consultations')
const tabs = ['consultations', 'training', 'articles', 'engagement', 'visits']

const dashboard = ref(null)
const consultationFilter = ref('')
const selectedPeriod = ref(30)
const showTrainingDialog = ref(false)
const showArticleDialog = ref(false)
const showVisitDialog = ref(false)

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  try {
    const res = await fetch('/api/expert/dashboard', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    const data = await res.json()
    dashboard.value = data.data
  } catch (error) {
    console.error('Error fetching dashboard:', error)
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatTabName = (tab) => {
  const names = {
    consultations: 'Consultations',
    training: 'Training Materials',
    articles: 'Articles',
    engagement: 'Engagement',
    visits: 'Farm Visits'
  }
  return names[tab] || tab
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.expert-layout {
  display: flex;
  height: 100vh;
}

.expert-dashboard {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f5f5;
  padding: 20px;
}

.dashboard-header {
  margin-bottom: 30px;
}

.dashboard-header h1 {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.dashboard-header p {
  color: #666;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.summary-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.card-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.card-icon.consultations { background-color: #06b6d4; }
.card-icon.pending { background-color: #f59e0b; }
.card-icon.completed { background-color: #10b981; }
.card-icon.training { background-color: #3b82f6; }
.card-icon.articles { background-color: #8b5cf6; }
.card-icon.rating { background-color: #ec4899; }

.card-content h3 {
  font-size: 12px;
  color: #666;
  margin-bottom: 5px;
  text-transform: uppercase;
  font-weight: 600;
}

.card-value {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.card-sub {
  font-size: 12px;
  color: #999;
}

.dashboard-tabs {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 30px;
}

.tab-buttons {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
  overflow-x: auto;
}

.tab-btn {
  flex: 1;
  padding: 15px 20px;
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
  font-weight: 500;
  transition: all 0.3s;
  border-bottom: 3px solid transparent;
  white-space: nowrap;
}

.tab-btn:hover {
  color: #06b6d4;
}

.tab-btn.active {
  color: #06b6d4;
  border-bottom-color: #06b6d4;
}

.tab-content {
  padding: 25px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2 {
  font-size: 20px;
  font-weight: bold;
  color: #333;
}

.filter-controls {
  display: flex;
  gap: 10px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
}

.btn-primary {
  background: #06b6d4;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover {
  background: #0891b2;
}

.consultations-table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background-color: #f9fafb;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.status-completed {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-published {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-draft {
  background-color: #dbeafe;
  color: #1e40af;
}

.action-buttons {
  display: flex;
  gap: 10px;
}

.btn-small {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-respond {
  background-color: #06b6d4;
  color: white;
}

.btn-view {
  background-color: #06b6d4;
  color: white;
}

.btn-edit {
  background-color: #3b82f6;
  color: white;
}

.btn-delete {
  background-color: #ef4444;
  color: white;
}

.btn-preview {
  background-color: #8b5cf6;
  color: white;
}

.btn-complete {
  background-color: #10b981;
  color: white;
}

.btn-report {
  background-color: #06b6d4;
  color: white;
}

.training-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.training-card {
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 15px;
  transition: all 0.3s;
}

.training-card:hover {
  border-color: #06b6d4;
  box-shadow: 0 2px 8px rgba(6, 182, 212, 0.1);
}

.training-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 10px;
}

.training-header h4 {
  margin: 0;
  color: #333;
  flex: 1;
}

.training-description {
  font-size: 14px;
  color: #666;
  margin: 10px 0;
}

.training-stats {
  display: flex;
  gap: 15px;
  margin: 10px 0;
  font-size: 13px;
  color: #666;
}

.training-stats span {
  display: flex;
  align-items: center;
  gap: 5px;
}

.training-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.articles-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.article-item {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 15px;
  transition: all 0.3s;
}

.article-item:hover {
  border-color: #06b6d4;
  box-shadow: 0 2px 8px rgba(6, 182, 212, 0.1);
}

.article-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 10px;
}

.article-header h3 {
  margin: 0 0 5px 0;
  color: #333;
}

.article-meta {
  font-size: 12px;
  color: #999;
  margin: 0;
}

.article-excerpt {
  font-size: 14px;
  color: #666;
  margin: 10px 0;
}

.article-engagement {
  display: flex;
  gap: 15px;
  margin: 10px 0;
  font-size: 13px;
  color: #666;
}

.article-engagement span {
  display: flex;
  align-items: center;
  gap: 5px;
}

.article-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.engagement-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.engagement-card {
  background-color: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.engagement-card h3 {
  color: #666;
  margin-bottom: 10px;
  font-size: 14px;
}

.big-number {
  font-size: 28px;
  font-weight: bold;
  color: #333;
}

.period-selector {
  display: flex;
  gap: 10px;
}

.period-btn {
  padding: 8px 16px;
  background: #f3f4f6;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s;
}

.period-btn.active {
  background: #06b6d4;
  color: white;
}

.top-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.top-card {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.top-card h3 {
  margin: 0 0 15px 0;
  color: #333;
}

.top-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.top-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 10px;
  background: white;
  border-radius: 4px;
}

.item-rank {
  background: #06b6d4;
  color: white;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 12px;
}

.item-title {
  flex: 1;
  font-weight: 500;
  color: #333;
}

.item-metric {
  font-size: 13px;
  color: #666;
}

.visits-timeline {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.visit-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 15px;
  display: flex;
  gap: 15px;
}

.visit-date {
  display: flex;
  align-items: center;
}

.date-badge {
  background: #06b6d4;
  color: white;
  padding: 8px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.visit-details {
  flex: 1;
}

.visit-details h4 {
  margin: 0 0 5px 0;
  color: #333;
}

.visit-details p {
  margin: 5px 0;
  font-size: 14px;
  color: #666;
}

.visit-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.recent-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.recent-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recent-card h2 {
  font-size: 18px;
  margin-bottom: 15px;
  color: #333;
}

.list-items {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: #f9fafb;
  border-radius: 4px;
}

.item-name {
  font-weight: 600;
  color: #333;
}

.item-topic {
  font-size: 13px;
  color: #666;
}

.item-status {
  font-size: 12px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 3px;
}

.label {
  font-weight: 600;
  color: #666;
}

.value {
  font-weight: 700;
  color: #333;
}

@media (max-width: 768px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .tab-buttons {
    flex-wrap: wrap;
  }
  
  .training-grid {
    grid-template-columns: 1fr;
  }
}
</style>
