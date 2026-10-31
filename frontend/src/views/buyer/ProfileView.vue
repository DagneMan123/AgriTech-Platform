<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">
    <!-- Responsive Sidebar Container -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar @logout="handleLogout" />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area with proper 64-unit left margin -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen lg:ml-64 transition-all" role="main">
      <!-- ── PAGE HEADER ── -->
      <div :class="['border-b transition-all -mx-4 lg:-mx-8 -mt-4 lg:-mt-8 mb-6 px-6 lg:px-8 py-[24px]', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <!-- Mobile Menu Toggle Button -->
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="lg:hidden p-2 rounded-xl border transition-all text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800"
              aria-label="Toggle menu"
            >
              <Menu class="w-5 h-5" />
            </button>
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-500/10 text-blue-500 border border-blue-500/20">
                  <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                  Verified Commercial Entity
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Account &gt; Business Profile
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <Building2 class="w-7 h-7 text-blue-500" />
                Enterprise Business Profile
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Commercial trading credentials, Ministry of Trade registration, TIN validation, regional warehouse locations, and security settings
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── SUCCESS NOTIFICATION BANNER ── -->
      <div
        v-if="successMessage"
        :class="['p-4 rounded-2xl border mb-6 flex items-center justify-between gap-3', darkMode ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-200' : 'bg-emerald-50 border-emerald-200 text-emerald-800']"
      >
        <div class="flex items-center gap-3">
          <CheckCircle2 class="w-5 h-5 text-emerald-500 shrink-0" />
          <div class="text-xs font-semibold">{{ successMessage }}</div>
        </div>
        <button @click="successMessage = ''" class="text-slate-400 hover:text-slate-600">
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- ── ERROR BANNER ── -->
      <div
        v-if="errorMessage"
        :class="['p-4 rounded-2xl border mb-6 flex items-start justify-between gap-3', darkMode ? 'bg-rose-500/10 border-rose-500/20 text-rose-200' : 'bg-rose-50 border-rose-200 text-rose-800']"
      >
        <div class="flex items-center gap-3">
          <AlertCircle class="w-5 h-5 text-rose-500 shrink-0" />
          <div>
            <div class="text-xs font-bold">Operation Notice</div>
            <div class="text-xs opacity-90">{{ errorMessage }}</div>
          </div>
        </div>
        <button @click="errorMessage = ''" class="text-slate-400 hover:text-slate-600">
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- ── COMMERCIAL IDENTITY TOP BANNER CARD ── -->
      <div :class="['p-6 rounded-3xl border mb-6 transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-black text-2xl uppercase shrink-0 border border-blue-500/20">
              {{ form.business_name ? form.business_name.substring(0, 2) : 'BY' }}
            </div>
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-500/10 text-blue-500 border border-blue-500/20">
                  {{ form.buyer_type }}
                </span>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-500">
                  <ShieldCheck class="w-3.5 h-3.5" />
                  Verified Buyer
                </span>
              </div>
              <h2 class="text-xl font-bold m-0 tracking-tight">{{ form.business_name || form.name }}</h2>
              <div class="text-xs text-slate-400 mt-1 flex items-center gap-3 flex-wrap">
                <span>Contact: <strong class="text-slate-200">{{ form.name }}</strong></span>
                <span>&bull;</span>
                <span>{{ form.email }}</span>
                <span v-if="form.phone">&bull; {{ form.phone }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Metrics Column -->
          <div class="flex items-center gap-4 border-t sm:border-t-0 sm:border-l border-slate-200 dark:border-slate-800 pt-4 sm:pt-0 sm:pl-6">
            <div>
              <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Procurement</div>
              <div class="text-base font-bold font-mono text-blue-500 mt-0.5">
                ETB {{ formatCurrency(stats.total_spent) }}
              </div>
            </div>
            <div>
              <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Orders Fulfilled</div>
              <div class="text-base font-bold text-emerald-500 mt-0.5">
                {{ stats.completed_orders || 0 }} Completed
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── PROFILE NAVIGATION TABS ── -->
      <div :class="['rounded-3xl border overflow-hidden transition-all mb-8', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
        <!-- Tabs Header -->
        <div class="border-b border-slate-200 dark:border-slate-800 flex items-center overflow-x-auto no-scrollbar">
          <button
            v-for="t in tabs"
            :key="t.id"
            @click="activeTab = t.id"
            :class="[
              'px-6 py-4 text-xs font-bold transition-all border-b-2 whitespace-nowrap flex items-center gap-2 cursor-pointer',
              activeTab === t.id
                ? 'border-blue-500 text-blue-500 bg-blue-50/30 dark:bg-blue-500/5'
                : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
            ]"
          >
            <component :is="t.icon" class="w-4 h-4" />
            <span>{{ t.label }}</span>
          </button>
        </div>

        <!-- ── TAB 1: COMMERCIAL IDENTITY ── -->
        <div v-if="activeTab === 'identity'" class="p-6">
          <form @submit.prevent="saveProfile" class="space-y-5 max-w-2xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Business / Enterprise Name *</label>
                <input
                  v-model="form.business_name"
                  type="text"
                  required
                  placeholder="e.g., Ethiopian Grain Wholesalers PLC"
                  :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Buyer Classification *</label>
                <select
                  v-model="form.buyer_type"
                  class="w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900'"
                >
                  <option value="wholesaler">Commercial Wholesaler</option>
                  <option value="retailer">Food Retailer / Supermarket</option>
                  <option value="processor">Agro-Processing Industry</option>
                  <option value="exporter">Commodity Exporter</option>
                  <option value="institution">Institutional / Cooperative Buyer</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Trade Registration Number</label>
                <input
                  v-model="form.business_registration"
                  type="text"
                  placeholder="e.g., MOT-AA-2024-8910"
                  :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">TIN / Tax Identification Number</label>
                <input
                  v-model="form.tax_id"
                  type="text"
                  placeholder="e.g., TIN-00981248"
                  :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Commercial Bio &amp; Purchasing Scope</label>
              <textarea
                v-model="form.bio"
                rows="4"
                placeholder="Describe your organization's agricultural purchasing volume, preferred regional commodity hubs, and standard crop specifications..."
                :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              ></textarea>
            </div>

            <div class="pt-3">
              <button
                type="submit"
                :disabled="savingProfile"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition disabled:opacity-50 cursor-pointer"
              >
                <Check class="w-4 h-4" />
                <span>{{ savingProfile ? 'Saving...' : 'Save Commercial Details' }}</span>
              </button>
            </div>
          </form>
        </div>

        <!-- ── TAB 2: REPRESENTATIVE & CONTACT ── -->
        <div v-if="activeTab === 'contact'" class="p-6">
          <form @submit.prevent="saveProfile" class="space-y-5 max-w-2xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Authorized Representative Name *</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Official Representative Phone *</label>
                <input
                  v-model="form.phone"
                  type="text"
                  required
                  placeholder="0911000000"
                  :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Corporate Email Address (Read-Only)</label>
                <input
                  :value="form.email"
                  disabled
                  type="email"
                  :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs opacity-70 cursor-not-allowed', darkMode ? 'bg-slate-900 border-slate-700 text-slate-400' : 'bg-slate-100 border-slate-300 text-slate-500']"
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Business Direct Landline / Hot-line</label>
                <input
                  v-model="form.business_phone"
                  type="text"
                  placeholder="0115000000"
                  :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                />
              </div>
            </div>

            <div class="pt-3">
              <button
                type="submit"
                :disabled="savingProfile"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition disabled:opacity-50 cursor-pointer"
              >
                <Check class="w-4 h-4" />
                <span>{{ savingProfile ? 'Saving...' : 'Update Contact Information' }}</span>
              </button>
            </div>
          </form>
        </div>

        <!-- ── TAB 3: WAREHOUSE & DELIVERY LOCATION ── -->
        <div v-if="activeTab === 'location'" class="p-6">
          <form @submit.prevent="saveProfile" class="space-y-5 max-w-2xl">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Region *</label>
                <input
                  v-model="form.region"
                  type="text"
                  required
                  placeholder="e.g., Addis Ababa / Oromia"
                  :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Zone / Sub-City</label>
                <input
                  v-model="form.zone"
                  type="text"
                  placeholder="e.g., Bole / Adama"
                  :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                />
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Woreda / Kebele</label>
                <input
                  v-model="form.woreda"
                  type="text"
                  placeholder="e.g., Woreda 03"
                  :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Central Warehouse Delivery Address *</label>
              <textarea
                v-model="form.address"
                rows="3"
                required
                placeholder="Specific warehouse complex, street address, depot number, and landmark for freight delivery trucks..."
                :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              ></textarea>
            </div>

            <div class="pt-3">
              <button
                type="submit"
                :disabled="savingProfile"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition disabled:opacity-50 cursor-pointer"
              >
                <Check class="w-4 h-4" />
                <span>{{ savingProfile ? 'Saving...' : 'Update Warehouse Location' }}</span>
              </button>
            </div>
          </form>
        </div>

        <!-- ── TAB 4: SECURITY & CREDENTIALS ── -->
        <div v-if="activeTab === 'security'" class="p-6">
          <form @submit.prevent="changePassword" class="space-y-5 max-w-md">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Current Password *</label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                required
                :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">New Password (Min 8 Characters) *</label>
              <input
                v-model="passwordForm.password"
                type="password"
                required
                minlength="8"
                :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Confirm New Password *</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                required
                minlength="8"
                :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              />
            </div>

            <div class="pt-3">
              <button
                type="submit"
                :disabled="savingPassword"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition disabled:opacity-50 cursor-pointer"
              >
                <KeyRound class="w-4 h-4" />
                <span>{{ savingPassword ? 'Updating Password...' : 'Update Account Password' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { buyerAPI } from '@/api/buyer'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import {
  Menu, Building2, ShieldCheck, CheckCircle2, AlertCircle, X,
  User, MapPin, KeyRound, Check
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const savingProfile = ref(false)
const savingPassword = ref(false)

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

const activeTab = ref<'identity' | 'contact' | 'location' | 'security'>('identity')

const tabs = [
  { id: 'identity', label: 'Commercial Identity', icon: Building2 },
  { id: 'contact', label: 'Representative & Contact', icon: User },
  { id: 'location', label: 'Warehouse & Regional Hub', icon: MapPin },
  { id: 'security', label: 'Security & Password', icon: KeyRound },
]

const form = ref({
  name: '',
  email: '',
  phone: '',
  region: '',
  zone: '',
  woreda: '',
  address: '',
  // Business fields
  business_name: '',
  buyer_type: 'wholesaler',
  business_registration: '',
  tax_id: '',
  business_address: '',
  business_phone: '',
  bio: '',
})

const stats = ref({
  total_orders: 0,
  completed_orders: 0,
  total_spent: 0,
  currency: 'ETB'
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const fetchProfile = async () => {
  errorMessage.value = ''
  try {
    const res = await buyerAPI.getProfile()
    if (res.data && res.data.success) {
      const u = res.data.data.user || {}
      const b = res.data.data.buyer || {}
      const s = res.data.data.stats || {}

      form.value = {
        name: u.name || '',
        email: u.email || '',
        phone: u.phone || '',
        region: u.region || '',
        zone: u.zone || '',
        woreda: u.woreda || '',
        address: u.address || b.business_address || '',
        business_name: b.business_name || u.name || '',
        buyer_type: b.buyer_type || 'wholesaler',
        business_registration: b.business_registration || '',
        tax_id: b.tax_id || '',
        business_address: b.business_address || u.address || '',
        business_phone: b.business_phone || u.phone || '',
        bio: b.bio || ''
      }

      stats.value = s
    }
  } catch (err: any) {
    console.error('Failed to load profile from database:', err)
    errorMessage.value = err.response?.data?.message || 'Unable to retrieve profile from database.'
  }
}

const saveProfile = async () => {
  savingProfile.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    const res = await buyerAPI.updateProfile(form.value)
    if (res.data && res.data.success) {
      successMessage.value = res.data.message || 'Business profile successfully updated in database.'
    } else {
      errorMessage.value = res.data?.message || 'Failed to update profile.'
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Error saving profile updates.'
  } finally {
    savingProfile.value = false
  }
}

const changePassword = async () => {
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    errorMessage.value = 'New password and confirmation do not match.'
    return
  }
  savingPassword.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    const res = await buyerAPI.updatePassword(passwordForm.value)
    if (res.data && res.data.success) {
      successMessage.value = res.data.message || 'Password successfully updated.'
      passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    } else {
      errorMessage.value = res.data?.message || 'Failed to update password.'
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || err.response?.data?.errors?.current_password?.[0] || 'Unable to update password.'
  } finally {
    savingPassword.value = false
  }
}

const formatCurrency = (val: number | string | undefined) => {
  if (!val) return '0.00'
  return Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

onMounted(() => {
  fetchProfile()
})
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
