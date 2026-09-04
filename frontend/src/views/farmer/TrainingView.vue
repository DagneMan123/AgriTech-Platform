<template>
  <div class="training-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="training-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Training Material</h1>
          <p>Access comprehensive agricultural training courses and resources</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading training materials...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchTrainingMaterials" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="training-content">
        <!-- Search and Filter -->
        <div class="search-bar">
          <input v-model="searchQuery" type="text" placeholder="Search training materials..." class="search-input" />
          <select v-model="selectedCategory" class="filter-select">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
          <select v-model="selectedLevel" class="filter-select">
            <option value="">All Levels</option>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
          </select>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <BookOpen size="20" class="stat-icon" />
            <div>
              <span class="stat-label">Total Courses</span>
              <span class="stat-value">{{ filteredTraining.length }}</span>
            </div>
          </div>
          <div class="stat-card">
            <CheckCircle size="20" class="stat-icon completed" />
            <div>
              <span class="stat-label">Completed</span>
              <span class="stat-value">{{ stats.completed }}</span>
            </div>
          </div>
          <div class="stat-card">
            <Clock size="20" class="stat-icon inprogress" />
            <div>
              <span class="stat-label">In Progress</span>
              <span class="stat-value">{{ stats.inProgress }}</span>
            </div>
          </div>
          <div class="stat-card">
            <Award size="20" class="stat-icon certificates" />
            <div>
              <span class="stat-label">Certificates</span>
              <span class="stat-value">{{ stats.certificates }}</span>
            </div>
          </div>
        </div>

        <!-- Training Cards -->
        <div class="training-grid">
          <div v-for="training in filteredTraining" :key="training.id" class="training-card" @click="selectTraining(training)">
            <div class="card-image">
              <component :is="training.icon" size="64" class="icon" />
              <span class="level-badge" :class="`level-${training.level}`">{{ capitalize(training.level) }}</span>
            </div>

            <div class="card-content">
              <h3>{{ training.title }}</h3>
              <p class="category">{{ training.category }}</p>
              <p class="description">{{ training.description }}</p>

              <div class="course-info">
                <span class="info-item">
                  <Users size="14" />
                  {{ training.enrolled }} students
                </span>
                <span class="info-item">
                  <Clock size="14" />
                  {{ training.duration }}h
                </span>
                <span class="info-item">
                  <Star size="14" />
                  {{ training.rating }}
                </span>
              </div>

              <div class="progress-section">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: training.progress + '%' }"></div>
                </div>
                <span class="progress-text">{{ training.progress }}% complete</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Training Details Modal -->
        <div v-if="selectedTrainingDetail" class="modal-overlay" @click="selectedTrainingDetail = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>{{ selectedTrainingDetail.title }}</h2>
              <button @click="selectedTrainingDetail = null" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <!-- Course Overview -->
              <div class="section">
                <h3>Course Overview</h3>
                <p class="description">{{ selectedTrainingDetail.fullDescription }}</p>
              </div>

              <!-- Course Stats -->
              <div class="section">
                <h3>Course Details</h3>
                <div class="details-grid">
                  <div class="detail">
                    <span class="label">Instructor</span>
                    <span class="value">{{ selectedTrainingDetail.instructor }}</span>
                  </div>
                  <div class="detail">
                    <span class="label">Duration</span>
                    <span class="value">{{ selectedTrainingDetail.duration }} hours</span>
                  </div>
                  <div class="detail">
                    <span class="label">Level</span>
                    <span class="value">{{ capitalize(selectedTrainingDetail.level) }}</span>
                  </div>
                  <div class="detail">
                    <span class="label">Enrolled Students</span>
                    <span class="value">{{ selectedTrainingDetail.enrolled }}</span>
                  </div>
                  <div class="detail">
                    <span class="label">Rating</span>
                    <span class="value">
                      <span v-for="i in 5" :key="i" class="star" :class="{ filled: i <= 4 }">★</span>
                    </span>
                  </div>
                  <div class="detail">
                    <span class="label">Progress</span>
                    <span class="value">{{ selectedTrainingDetail.progress }}%</span>
                  </div>
                </div>
              </div>

              <!-- Learning Objectives -->
              <div class="section">
                <h3>Learning Objectives</h3>
                <ul class="objectives-list">
                  <li v-for="objective in selectedTrainingDetail.objectives" :key="objective">
                    <CheckCircle size="16" />
                    {{ objective }}
                  </li>
                </ul>
              </div>

              <!-- Course Modules -->
              <div class="section">
                <h3>Course Modules</h3>
                <div class="modules-list">
                  <div v-for="(module, index) in selectedTrainingDetail.modules" :key="index" class="module-item">
                    <span class="module-number">Module {{ index + 1 }}</span>
                    <span class="module-title">{{ module.title }}</span>
                    <span class="module-duration">{{ module.duration }}m</span>
                    <span class="module-status" :class="{ completed: module.completed }">
                      {{ module.completed ? 'Completed' : 'Not Started' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Prerequisites -->
              <div class="section">
                <h3>Prerequisites</h3>
                <ul class="prerequisites-list">
                  <li v-for="prereq in selectedTrainingDetail.prerequisites" :key="prereq">
                    {{ prereq }}
                  </li>
                </ul>
              </div>

              <!-- Reviews -->
              <div class="section">
                <h3>Student Reviews</h3>
                <div class="reviews-list">
                  <div v-for="review in selectedTrainingDetail.reviews" :key="review.id" class="review-item">
                    <div class="review-header">
                      <span class="review-name">{{ review.name }}</span>
                      <span class="review-rating">
                        <span v-for="i in 5" :key="i" class="star" :class="{ filled: i <= review.rating }">★</span>
                      </span>
                    </div>
                    <p class="review-text">{{ review.text }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedTrainingDetail = null" class="btn-secondary">Close</button>
              <button v-if="selectedTrainingDetail.progress === 0" @click="startCourse" class="btn-primary">
                <Play size="16" />
                Start Course
              </button>
              <button v-else-if="selectedTrainingDetail.progress < 100" @click="continueCourse" class="btn-primary">
                <Play size="16" />
                Continue Learning
              </button>
              <button v-else @click="downloadCertificate" class="btn-success">
                <Download size="16" />
                Download Certificate
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
  BookOpen, CheckCircle, Clock, Award, AlertCircle, RotateCcw, X, Users, Star,
  Sprout, FlaskConical, Leaf, Droplets, Bug, Zap, Play, Download
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedLevel = ref('')
const selectedTrainingDetail = ref(null)

const categories = ['Crop Management', 'Pest Management', 'Soil Science', 'Irrigation', 'Machinery']

const mockTraining = ref([
  {
    id: 1,
    title: 'Sustainable Crop Management',
    category: 'Crop Management',
    description: 'Learn advanced techniques for sustainable and productive crop cultivation',
    fullDescription: 'This comprehensive course covers sustainable crop management practices including soil health, crop rotation, organic farming methods, and modern agricultural techniques to maximize yields while maintaining environmental sustainability.',
    level: 'beginner',
    icon: Sprout,
    instructor: 'Dr. Ahmed Hassan',
    duration: 12,
    enrolled: 1250,
    rating: 4.8,
    progress: 45,
    objectives: [
      'Understand soil health and nutrient management',
      'Master crop rotation techniques',
      'Learn integrated pest management',
      'Implement sustainable farming practices'
    ],
    modules: [
      { title: 'Introduction to Sustainable Farming', duration: 45, completed: true },
      { title: 'Soil Health and Preparation', duration: 60, completed: true },
      { title: 'Crop Selection and Planning', duration: 45, completed: false },
      { title: 'Nutrient Management', duration: 50, completed: false }
    ],
    prerequisites: ['Basic agricultural knowledge', 'English language proficiency'],
    reviews: [
      { id: 1, name: 'Farmer John', rating: 5, text: 'Excellent course! Very practical advice.' },
      { id: 2, name: 'Sarah Miller', rating: 4, text: 'Great content, but could use more video examples.' }
    ]
  },
  {
    id: 2,
    title: 'Integrated Pest Management',
    category: 'Pest Management',
    description: 'Comprehensive guide to managing crop pests naturally and effectively',
    fullDescription: 'Learn how to identify, monitor, and manage agricultural pests using integrated pest management (IPM) strategies, including biological controls, cultural practices, and when to use chemical treatments.',
    level: 'intermediate',
    icon: Bug,
    instructor: 'Prof. Sarah Williams',
    duration: 10,
    enrolled: 980,
    rating: 4.7,
    progress: 20,
    objectives: [
      'Identify common agricultural pests',
      'Understand pest life cycles',
      'Implement biological controls',
      'Create integrated pest management plans'
    ],
    modules: [
      { title: 'Pest Identification', duration: 40, completed: true },
      { title: 'Natural Enemies and Biocontrols', duration: 50, completed: false },
      { title: 'Cultural Control Methods', duration: 35, completed: false },
      { title: 'Pesticide Safety and Application', duration: 45, completed: false }
    ],
    prerequisites: ['Basic crop knowledge', 'Understanding of plant biology'],
    reviews: [
      { id: 1, name: 'Amit Kumar', rating: 5, text: 'Perfect for eco-friendly farming!' }
    ]
  },
  {
    id: 3,
    title: 'Soil Science Fundamentals',
    category: 'Soil Science',
    description: 'Master soil testing, analysis, and improvement techniques',
    fullDescription: 'Understand soil composition, texture, fertility, and pH management. Learn how to test soil, interpret results, and apply targeted improvements for optimal crop production.',
    level: 'beginner',
    icon: Leaf,
    instructor: 'Mr. James Smith',
    duration: 8,
    enrolled: 1420,
    rating: 4.9,
    progress: 0,
    objectives: [
      'Understand soil composition and structure',
      'Learn soil testing procedures',
      'Interpret soil analysis results',
      'Improve soil fertility and health'
    ],
    modules: [
      { title: 'Soil Structure and Composition', duration: 35, completed: false },
      { title: 'Soil Testing Methods', duration: 50, completed: false },
      { title: 'Nutrient Availability and Management', duration: 45, completed: false },
      { title: 'Soil Amendments and Improvements', duration: 40, completed: false }
    ],
    prerequisites: ['None'],
    reviews: []
  },
  {
    id: 4,
    title: 'Drip Irrigation Systems',
    category: 'Irrigation',
    description: 'Design and manage efficient drip irrigation systems',
    fullDescription: 'Learn to design, install, maintain, and troubleshoot drip irrigation systems for maximum water efficiency and crop productivity.',
    level: 'intermediate',
    icon: Droplets,
    instructor: 'Dr. Amira Khan',
    duration: 9,
    enrolled: 750,
    rating: 4.6,
    progress: 100,
    objectives: [
      'Design efficient drip systems',
      'Calculate water requirements',
      'Maintain and troubleshoot systems',
      'Optimize water use efficiency'
    ],
    modules: [
      { title: 'Drip Irrigation Basics', duration: 40, completed: true },
      { title: 'System Design and Installation', duration: 60, completed: true },
      { title: 'Maintenance and Troubleshooting', duration: 45, completed: true },
      { title: 'Water Management and Scheduling', duration: 50, completed: true }
    ],
    prerequisites: ['Basic irrigation knowledge'],
    reviews: [
      { id: 1, name: 'Farm Manager Ali', rating: 5, text: 'Saved us significant water costs!' }
    ]
  },
  {
    id: 5,
    title: 'Modern Farm Machinery',
    category: 'Machinery',
    description: 'Operate and maintain modern agricultural machinery safely',
    fullDescription: 'Comprehensive training on the operation, maintenance, and safety protocols for modern farm machinery including tractors, harvesters, and planting equipment.',
    level: 'advanced',
    icon: Zap,
    instructor: 'Mr. Robert Wilson',
    duration: 15,
    enrolled: 580,
    rating: 4.8,
    progress: 65,
    objectives: [
      'Operate farm machinery safely',
      'Perform routine maintenance',
      'Troubleshoot common issues',
      'Apply best practices for efficiency'
    ],
    modules: [
      { title: 'Safety and Regulations', duration: 50, completed: true },
      { title: 'Equipment Operation', duration: 70, completed: true },
      { title: 'Maintenance Procedures', duration: 60, completed: true },
      { title: 'Troubleshooting and Repairs', duration: 80, completed: false }
    ],
    prerequisites: ['Basic mechanical knowledge', 'Valid driver license'],
    reviews: [
      { id: 1, name: 'Equipment Tech', rating: 5, text: 'Comprehensive and well-structured!' }
    ]
  },
  {
    id: 6,
    title: 'Organic Farming Certification',
    category: 'Crop Management',
    description: 'Complete guide to organic farming certification process',
    fullDescription: 'Learn the requirements and processes for obtaining organic farming certification, including record keeping, pesticide alternatives, and compliance standards.',
    level: 'advanced',
    icon: Sprout,
    instructor: 'Dr. Margaret Green',
    duration: 14,
    enrolled: 450,
    rating: 4.9,
    progress: 0,
    objectives: [
      'Understand organic standards',
      'Maintain compliance records',
      'Source approved inputs',
      'Plan certification transition'
    ],
    modules: [
      { title: 'Organic Standards Overview', duration: 45, completed: false },
      { title: 'Record Keeping Systems', duration: 40, completed: false },
      { title: 'Approved Practices and Materials', duration: 55, completed: false },
      { title: 'Certification Application Process', duration: 50, completed: false }
    ],
    prerequisites: ['2+ years farming experience'],
    reviews: []
  }
])

const filteredTraining = computed(() => {
  let filtered = mockTraining.value

  if (searchQuery.value) {
    filtered = filtered.filter(t => 
      t.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      t.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (selectedCategory.value) {
    filtered = filtered.filter(t => t.category === selectedCategory.value)
  }

  if (selectedLevel.value) {
    filtered = filtered.filter(t => t.level === selectedLevel.value)
  }

  return filtered
})

const stats = computed(() => ({
  completed: mockTraining.value.filter(t => t.progress === 100).length,
  inProgress: mockTraining.value.filter(t => t.progress > 0 && t.progress < 100).length,
  certificates: mockTraining.value.filter(t => t.progress === 100).length
}))

const fetchTrainingMaterials = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)
const selectTraining = (training) => { selectedTrainingDetail.value = training }
const startCourse = () => { alert('Starting course: ' + selectedTrainingDetail.value.title) }
const continueCourse = () => { alert('Continuing course: ' + selectedTrainingDetail.value.title) }
const downloadCertificate = () => { alert('Downloading certificate...') }

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchTrainingMaterials() })
</script>

<style scoped>
.training-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.training-container {
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

.training-content {
  padding: 30px;
  flex: 1;
}

.search-bar {
  background: white;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.search-input,
.filter-select {
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 13px;
  color: #4b5563;
  background: white;
  font-family: inherit;
}

.search-input {
  flex: 1;
  min-width: 200px;
}

.search-input::placeholder {
  color: #9ca3af;
}

.search-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
  color: #3b82f6;
  flex-shrink: 0;
}

.stat-icon.completed { color: #10b981; }
.stat-icon.inprogress { color: #f59e0b; }
.stat-icon.certificates { color: #8b5cf6; }

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

.training-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.training-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: all 0.3s;
  cursor: pointer;
  display: flex;
  flex-direction: column;
}

.training-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.card-image {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  padding: 40px 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  height: 160px;
}

.icon {
  color: #1e40af;
  opacity: 0.8;
}

.level-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
}

.level-beginner { background: #ecfdf5; color: #065f46; }
.level-intermediate { background: #fef3c7; color: #92400e; }
.level-advanced { background: #fee2e2; color: #991b1b; }

.card-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.card-content h3 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.category {
  font-size: 12px;
  color: #6b7280;
  margin: 0 0 8px 0;
  text-transform: uppercase;
  font-weight: 600;
}

.description {
  font-size: 13px;
  color: #4b5563;
  line-height: 1.5;
  margin: 0 0 12px 0;
  flex: 1;
}

.course-info {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 12px;
  font-size: 12px;
}

.info-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #6b7280;
}

.progress-section {
  margin-top: auto;
}

.progress-bar {
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 6px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #10b981);
  transition: width 0.3s;
}

.progress-text {
  font-size: 11px;
  color: #6b7280;
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
  max-width: 700px;
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

.section {
  margin-bottom: 24px;
}

.section h3 {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 16px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.description {
  font-size: 13px;
  color: #4b5563;
  line-height: 1.6;
  margin: 0;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.detail {
  background: #f9fafb;
  border-radius: 6px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail .label {
  font-size: 11px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.detail .value {
  font-size: 13px;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 4px;
}

.star {
  color: #d1d5db;
  font-size: 14px;
}

.star.filled {
  color: #f59e0b;
}

.objectives-list,
.prerequisites-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.objectives-list li,
.prerequisites-list li {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-size: 13px;
  color: #4b5563;
}

.objectives-list li svg {
  color: #10b981;
  flex-shrink: 0;
  margin-top: 2px;
}

.modules-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.module-item {
  display: grid;
  grid-template-columns: auto 1fr auto auto;
  gap: 12px;
  align-items: center;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
  font-size: 12px;
}

.module-number {
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
}

.module-title {
  color: #1f2937;
  font-weight: 600;
}

.module-duration {
  color: #6b7280;
}

.module-status {
  padding: 4px 8px;
  background: #fef3c7;
  color: #92400e;
  border-radius: 4px;
  font-weight: 600;
}

.module-status.completed {
  background: #ecfdf5;
  color: #065f46;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.review-item {
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
}

.review-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.review-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 13px;
}

.review-rating {
  display: inline-flex;
  gap: 2px;
}

.review-text {
  font-size: 12px;
  color: #4b5563;
  margin: 0;
  line-height: 1.5;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-primary,
.btn-secondary,
.btn-success {
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

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

@media (max-width: 768px) {
  .training-container {
    margin-left: 0;
  }

  .search-bar {
    flex-direction: column;
  }

  .training-grid {
    grid-template-columns: 1fr;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }

  .module-item {
    grid-template-columns: auto 1fr;
  }

  .module-duration,
  .module-status {
    display: none;
  }
}
</style>
