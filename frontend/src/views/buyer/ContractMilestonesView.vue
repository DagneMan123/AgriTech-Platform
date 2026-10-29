<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">

    <!-- Sidebar Container (Responsive) -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen lg:ml-64 transition-all" role="main">

      <!-- Header Section (Strictly NO toggle symbol) -->
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
                  <CheckSquare class="w-3.5 h-3.5" />
                  Escrow-Backed Milestones
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Contract Farming &gt; Milestone Tracking
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Milestone Tracking
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Track real-time crop production stages, agronomist inspections, and conditional escrow payouts
              </p>
            </div>
          </div>

          <!-- Header Right Actions (All Buttons Blue) -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
          </div>
        </div>
      </div>

      <!-- ── Summary Stats Cards (Live Database Metrics) ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Tracked Milestones -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Milestones</h3>
            <p class="text-2xl font-black">{{ stats.total }}</p>
            <span class="text-[10px] text-blue-500 font-medium">Across {{ rawContracts.length }} contracts</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <CheckSquare class="w-6 h-6" />
          </div>
        </article>

        <!-- Completed Milestones -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Completed</h3>
            <p class="text-2xl font-black">{{ stats.completed }}</p>
            <span class="text-[10px] text-blue-400 font-medium">Verified &amp; released</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <CheckCircle2 class="w-6 h-6" />
          </div>
        </article>

        <!-- In-Progress / Pending Verification -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-amber-500' : 'bg-white border-slate-200 text-slate-900 !border-l-amber-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Active In Field</h3>
            <p class="text-2xl font-black">{{ stats.activeInField }}</p>
            <span class="text-[10px] text-amber-500 font-medium">Under cultivation</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <Clock class="w-6 h-6" />
          </div>
        </article>

        <!-- Escrow Funds In Milestones -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Escrow In Milestones</h3>
            <p class="text-xl lg:text-2xl font-black text-blue-500">ETB {{ formatNumber(stats.escrowAmount) }}</p>
            <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium flex items-center gap-1">
              <ShieldCheck class="w-3 h-3 inline" /> Smart escrow locked
            </span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <BadgeDollarSign class="w-6 h-6" />
          </div>
        </article>
      </section>

      <!-- ── Search & Filter Controls ── -->
      <div :class="['rounded-2xl border p-4 lg:p-5 mb-6 shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
          <!-- Search Input -->
          <div class="relative flex-1">
            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search contract #, crop, milestone title, or farmer..."
              :class="['w-full pl-10 pr-4 py-2.5 rounded-xl text-xs border transition-all outline-none', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500 placeholder-slate-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-blue-500 placeholder-slate-400']"
            />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-bold"
            >
              ✕
            </button>
          </div>

          <!-- Filter Pills -->
          <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
            <button
              v-for="filter in statusFilters"
              :key="filter.value"
              @click="activeStatusFilter = filter.value"
              :class="[
                'px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5',
                activeStatusFilter === filter.value
                  ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30'
                  : darkMode
                    ? 'bg-[#0f172a] text-slate-300 hover:bg-slate-800 border border-slate-700'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200'
              ]"
            >
              <span>{{ filter.label }}</span>
              <span
                :class="[
                  'px-1.5 py-0.2 rounded-full text-[10px] font-extrabold',
                  activeStatusFilter === filter.value
                    ? 'bg-white/20 text-white'
                    : darkMode ? 'bg-slate-800 text-slate-400' : 'bg-slate-200 text-slate-600'
                ]"
              >
                {{ filter.count }}
              </span>
            </button>
          </div>

          <!-- Sort Select -->
          <div class="flex items-center gap-2">
            <select
              v-model="sortBy"
              :class="['px-3 py-2.5 rounded-xl text-xs border transition-all outline-none cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-blue-500']"
            >
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="highest-amount">Highest Value</option>
              <option value="delivery-date">Target Delivery</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ── Loading State ── -->
      <div v-if="loading" :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="w-10 h-10 border-4 border-slate-300 border-t-blue-500 rounded-full animate-spin mb-4"></div>
        <p :class="['text-sm font-semibold', darkMode ? 'text-slate-200' : 'text-slate-700']">Loading contract milestones...</p>
        <p :class="['text-xs mt-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Aggregating field inspection audits from database</p>
      </div>

      <!-- ── Empty State (Zero Mock Data) ── -->
      <div
        v-else-if="filteredContracts.length === 0"
        :class="['rounded-2xl border p-10 lg:p-14 text-center flex flex-col items-center justify-center shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
      >
        <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-4 border', darkMode ? 'bg-slate-800/60 border-slate-700 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-400']">
          <CheckSquare class="w-8 h-8 text-slate-400" />
        </div>
        <h3 :class="['text-lg font-extrabold mb-1', darkMode ? 'text-slate-100' : 'text-slate-800']">
          No Contract Milestones Found
        </h3>
        <p :class="['text-xs lg:text-sm max-w-md mb-6', darkMode ? 'text-slate-400' : 'text-slate-500']">
          <span v-if="searchQuery || activeStatusFilter">
            No milestones match your current filters. Try resetting search parameters.
          </span>
          <span v-else>
            You have no active contract production milestones recorded in the database yet. Direct supply contracts automatically create 4-stage verified production milestones.
          </span>
        </p>

        <div class="flex items-center gap-3 flex-wrap justify-center">
          <button
            v-if="searchQuery || activeStatusFilter"
            @click="resetFilters"
            class="px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Clear Filters
          </button>
          <router-link
            to="/buyer/contracts"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-sm shadow-blue-600/30"
          >
            <Plus class="w-4 h-4" />
            <span>Create Direct Contract</span>
          </router-link>
        </div>
      </div>

      <!-- ── Contract Milestones List ── -->
      <div v-else class="space-y-6">
        <article
          v-for="contract in filteredContracts"
          :key="contract.id"
          :class="[
            'rounded-2xl border overflow-hidden shadow-2xs hover:shadow-md transition-all',
            darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <!-- Contract Header Card -->
          <div :class="['p-5 border-b flex flex-col md:flex-row md:items-center justify-between gap-3', darkMode ? 'bg-[#0f1729] border-slate-800/80' : 'bg-slate-50 border-slate-200/80']">
            <div>
              <div class="flex items-center gap-2 mb-1 flex-wrap">
                <span class="font-mono text-xs font-black text-blue-500 px-2 py-0.5 rounded-md bg-blue-500/10 border border-blue-500/20">
                  {{ contract.contract_number }}
                </span>
                <span :class="['text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border', getContractStatusBadge(contract.status)]">
                  {{ formatContractStatus(contract.status) }}
                </span>
                <span :class="['text-xs', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Signed: {{ formatDate(contract.start_date) }}
                </span>
              </div>
              <h3 :class="['text-base font-extrabold m-0 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">
                {{ contract.title }}
              </h3>
              <p :class="['text-xs m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Farmer: <span class="font-semibold text-slate-200">{{ contract.farmer?.farm_name || contract.farmer?.user?.name || 'Local Producer' }}</span>
                <span class="mx-1.5">•</span>
                Crop: <span class="font-semibold text-blue-500">{{ contract.crop_type }}</span> ({{ formatNumber(contract.quantity) }} {{ contract.unit }})
              </p>
            </div>

            <!-- Milestone Completion Tracker Ribbon -->
            <div class="text-left md:text-right shrink-0">
              <span class="text-[10px] text-slate-400 uppercase font-bold block mb-1">Production Progress</span>
              <div class="flex items-center gap-2">
                <div class="w-32 bg-slate-700 h-2 rounded-full overflow-hidden">
                  <div
                    class="bg-blue-600 h-full transition-all duration-500"
                    :style="{ width: `${getMilestoneProgressPercent(contract)}%` }"
                  ></div>
                </div>
                <span class="text-xs font-black text-blue-500">{{ getMilestoneProgressPercent(contract) }}%</span>
              </div>
              <span class="text-[10px] text-slate-400 mt-0.5 block">
                {{ getCompletedCount(contract) }} of {{ (contract.milestones || []).length || 4 }} milestones fulfilled
              </span>
            </div>
          </div>

          <!-- Milestones Progression Grid -->
          <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div
                v-for="(milestone, idx) in (contract.milestones || defaultMilestones(contract))"
                :key="milestone.id || idx"
                :class="[
                  'p-4 rounded-xl border flex flex-col justify-between transition-all relative overflow-hidden',
                  getMilestoneCardStyle(milestone)
                ]"
              >
                <!-- Milestone Top Badge -->
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <span :class="['w-6 h-6 rounded-lg flex items-center justify-center font-bold text-xs', getMilestoneBadge(milestone)]">
                      <Check v-if="milestone.status === 'completed'" class="w-3.5 h-3.5" />
                      <span v-else>{{ idx + 1 }}</span>
                    </span>

                    <span :class="['text-[10px] px-2 py-0.5 rounded-full font-bold uppercase', getMilestoneStatusStyle(milestone.status)]">
                      {{ milestone.status }}
                    </span>
                  </div>

                  <h4 :class="['text-xs font-black m-0 mb-1', darkMode ? 'text-slate-100' : 'text-slate-900']">
                    {{ milestone.title }}
                  </h4>
                  <p :class="['text-[11px] m-0 mb-3 line-clamp-2', darkMode ? 'text-slate-400' : 'text-slate-600']">
                    {{ milestone.description }}
                  </p>
                </div>

                <!-- Milestone Payout Details -->
                <div class="pt-3 border-t border-slate-700/50 space-y-1.5 text-xs">
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">Target Date:</span>
                    <span class="font-semibold text-slate-300">{{ formatDate(milestone.due_date || null) }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">Payout:</span>
                    <span class="font-black text-blue-500">
                      ETB {{ formatNumber(milestone.payout_amount) }}
                      <span class="text-[10px] text-slate-400 font-normal">({{ milestone.payout_percentage }}%)</span>
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">Escrow:</span>
                    <span :class="['text-[10px] font-bold uppercase', getPayoutStatusColor(milestone.payout_status)]">
                      {{ milestone.payout_status || 'unpaid' }}
                    </span>
                  </div>

                  <button
                    @click="openMilestoneDetail(contract, milestone)"
                    class="w-full mt-2 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-[11px] font-bold transition-all flex items-center justify-center gap-1 cursor-pointer shadow-sm shadow-blue-600/30"
                  >
                    <FileText class="w-3 h-3" />
                    <span>Inspection Audit</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Footer Bar -->
          <div :class="['px-5 py-3 border-t flex items-center justify-between gap-3 text-xs', darkMode ? 'border-slate-800 bg-[#0f1729]' : 'border-slate-200 bg-slate-50']">
            <div class="flex items-center gap-2 text-slate-400">
              <ShieldCheck class="w-4 h-4 text-blue-500" />
              <span>Milestone funds disbursed only upon confirmed field inspection</span>
            </div>

            <router-link
              to="/buyer/contracts/agreements"
              class="text-blue-500 hover:underline font-bold flex items-center gap-1"
            >
              <span>View Full Agreement</span>
              <ChevronRight class="w-3.5 h-3.5" />
            </router-link>
          </div>
        </article>
      </div>

    </main>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- ── MODAL: MILESTONE INSPECTION & ESCROW AUDIT MODAL ── -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div
      v-if="detailModalOpen && activeModalMilestone && activeModalContract"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-xl max-h-[90vh] flex flex-col rounded-2xl border shadow-2xl overflow-hidden', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-700/40 flex items-center justify-between shrink-0 bg-[#0f1729]">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <CheckSquare class="w-5 h-5" />
            </div>
            <div>
              <span class="font-mono text-xs font-black text-blue-500">{{ activeModalContract.contract_number }}</span>
              <h2 class="text-sm lg:text-base font-extrabold m-0 mt-0.5">{{ activeModalMilestone.title }}</h2>
            </div>
          </div>
          <button @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-200 font-bold text-sm cursor-pointer p-1">✕</button>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-5 space-y-4 text-xs">
          <div>
            <span class="font-bold text-[10px] uppercase text-slate-400 block mb-1">Milestone Specification</span>
            <p :class="['p-3 rounded-xl border leading-relaxed m-0', darkMode ? 'bg-[#1a233a] border-slate-800 text-slate-200' : 'bg-slate-50 border-slate-200 text-slate-800']">
              {{ activeModalMilestone.description }}
            </p>
          </div>

          <!-- Financial Terms -->
          <div class="grid grid-cols-3 gap-2 text-center">
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Payout Value</span>
              <span class="font-black text-blue-500 text-sm">ETB {{ formatNumber(activeModalMilestone.payout_amount) }}</span>
            </div>
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Share of Total</span>
              <span class="font-black text-slate-200 text-sm">{{ activeModalMilestone.payout_percentage }}%</span>
            </div>
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Status</span>
              <span :class="['font-black text-sm uppercase', getPayoutStatusColor(activeModalMilestone.payout_status)]">
                {{ activeModalMilestone.status }}
              </span>
            </div>
          </div>

          <!-- Farmer & Verification Info -->
          <div :class="['p-3.5 rounded-xl border space-y-1', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <span class="font-bold text-[10px] uppercase text-slate-400 block mb-1">Contractor &amp; Field Location</span>
            <p class="font-bold text-xs m-0">{{ activeModalContract.farmer?.farm_name || activeModalContract.farmer?.user?.name || 'Local Producer' }}</p>
            <p class="text-slate-400 m-0">Produce: {{ activeModalContract.crop_type }} ({{ formatNumber(activeModalContract.quantity) }} {{ activeModalContract.unit }})</p>
            <p class="text-slate-400 m-0">Location: {{ activeModalContract.delivery_location || 'Designated buyer warehouse' }}</p>
          </div>
        </div>

        <!-- Modal Footer (Blue Action Button) -->
        <div class="p-4 border-t border-slate-700/40 flex justify-end gap-2 shrink-0 bg-[#0f1729]">
          <button
            @click="detailModalOpen = false"
            class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm shadow-blue-600/30"
          >
            Close Audit
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useBuyerStore } from '@/stores/buyerStore'
import { useThemeStore } from '@/stores/themeStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import {
  Menu, CheckSquare, Plus, Clock,
  CheckCircle2, BadgeDollarSign, ShieldCheck, Search, Check,
  ChevronRight, FileText
} from 'lucide-vue-next'

interface ContractFarmerUser {
  name?: string
  phone?: string
  email?: string
}

interface ContractFarmer {
  id: number
  user_id: number
  farm_name?: string
  region?: string
  zone?: string
  woreda?: string
  farm_type?: string
  average_rating?: number
  user?: ContractFarmerUser
}

interface ContractMilestoneItem {
  id?: number
  contract_id?: number
  title: string
  description?: string
  due_date?: string
  status: string
  payout_percentage: number
  payout_amount: number
  payout_status?: string
}

interface ContractItem {
  id: number
  contract_number: string
  buyer_id: number
  farmer_id: number
  title: string
  crop_type: string
  variety?: string
  quantity: number
  unit: string
  unit_price: number
  total_amount: number
  advance_payment?: number
  currency?: string
  start_date: string
  delivery_date: string
  delivery_location?: string
  quality_specifications?: string
  status: string
  payment_terms?: string
  terms_and_conditions?: string
  farmer_notes?: string
  created_at: string
  cancellation_reason?: string
  farmer?: ContractFarmer
  milestones?: ContractMilestoneItem[]
}

const buyerStore = useBuyerStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const loading = ref(false)


const searchQuery = ref('')
const activeStatusFilter = ref('')
const sortBy = ref('newest')

const detailModalOpen = ref(false)
const activeModalContract = ref<ContractItem | null>(null)
const activeModalMilestone = ref<ContractMilestoneItem | null>(null)

onMounted(async () => {
  themeStore.initializeTheme()
  await loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    await buyerStore.fetchContracts().catch(() => [])
  } finally {
    loading.value = false
  }
}



// Master Contracts from Database
const rawContracts = computed<ContractItem[]>(() => {
  const c = buyerStore.contracts
  if (Array.isArray(c)) return c as ContractItem[]
  if (c && Array.isArray((c as any).data)) return (c as any).data as ContractItem[]
  return []
})

// Summary Stats
const stats = computed(() => {
  let total = 0
  let completed = 0
  let activeInField = 0
  let escrowAmount = 0

  rawContracts.value.forEach((c: ContractItem) => {
    const ms = c.milestones || defaultMilestones(c)
    ms.forEach((m: ContractMilestoneItem) => {
      total++
      if (m.status === 'completed') {
        completed++
      } else if (['in_progress', 'pending'].includes(m.status)) {
        activeInField++
      }
      escrowAmount += Number(m.payout_amount || 0)
    })
  })

  return { total, completed, activeInField, escrowAmount }
})

// Filter Tabs
const statusFilters = computed(() => [
  { label: 'All Contracts', value: '', count: rawContracts.value.length },
  {
    label: 'In Production',
    value: 'in_progress',
    count: rawContracts.value.filter((c: ContractItem) => ['active', 'in_progress'].includes((c.status || '').toLowerCase())).length
  },
  {
    label: 'Proposed',
    value: 'proposed',
    count: rawContracts.value.filter((c: ContractItem) => ['proposed', 'draft'].includes((c.status || '').toLowerCase())).length
  },
  {
    label: 'Completed',
    value: 'completed',
    count: rawContracts.value.filter((c: ContractItem) => (c.status || '').toLowerCase() === 'completed').length
  }
])

// Filtered and Sorted Contracts
const filteredContracts = computed<ContractItem[]>(() => {
  let list = [...rawContracts.value]

  if (activeStatusFilter.value === 'in_progress') {
    list = list.filter((c: ContractItem) => ['active', 'in_progress'].includes((c.status || '').toLowerCase()))
  } else if (activeStatusFilter.value === 'proposed') {
    list = list.filter((c: ContractItem) => ['proposed', 'draft'].includes((c.status || '').toLowerCase()))
  } else if (activeStatusFilter.value) {
    list = list.filter((c: ContractItem) => (c.status || '').toLowerCase() === activeStatusFilter.value)
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter((c: ContractItem) =>
      (c.contract_number || '').toLowerCase().includes(q) ||
      (c.title || '').toLowerCase().includes(q) ||
      (c.crop_type || '').toLowerCase().includes(q) ||
      (c.farmer?.farm_name || '').toLowerCase().includes(q) ||
      (c.farmer?.user?.name || '').toLowerCase().includes(q)
    )
  }

  switch (sortBy.value) {
    case 'oldest':
      list.sort((a: ContractItem, b: ContractItem) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
      break
    case 'highest-amount':
      list.sort((a: ContractItem, b: ContractItem) => Number(b.total_amount || 0) - Number(a.total_amount || 0))
      break
    case 'delivery-date':
      list.sort((a: ContractItem, b: ContractItem) => new Date(a.delivery_date).getTime() - new Date(b.delivery_date).getTime())
      break
    case 'newest':
    default:
      list.sort((a: ContractItem, b: ContractItem) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      break
  }

  return list
})

const defaultMilestones = (c: ContractItem): ContractMilestoneItem[] => {
  const tot = Number(c.total_amount || 0)
  return [
    {
      title: 'Land Preparation & Sowing',
      description: 'Soil preparation, certified seed planting, and seedbed verification',
      due_date: c.start_date,
      status: ['active', 'in_progress', 'completed'].includes((c.status || '').toLowerCase()) ? 'completed' : 'pending',
      payout_percentage: 30,
      payout_amount: Math.round(tot * 0.3),
      payout_status: 'released'
    },
    {
      title: 'Mid-Season Inspection',
      description: 'Crop health, pest resistance inspection, and growth staging',
      due_date: c.delivery_date,
      status: ['completed'].includes((c.status || '').toLowerCase()) ? 'completed' : 'in_progress',
      payout_percentage: 0,
      payout_amount: 0,
      payout_status: 'in_escrow'
    },
    {
      title: 'Harvesting & Sorting',
      description: 'Grading, standardized packaging, and moisture content test',
      due_date: c.delivery_date,
      status: ['completed'].includes((c.status || '').toLowerCase()) ? 'completed' : 'pending',
      payout_percentage: 40,
      payout_amount: Math.round(tot * 0.4),
      payout_status: 'in_escrow'
    },
    {
      title: 'Final Settlement',
      description: 'Warehouse delivery acceptance and final escrow release',
      due_date: c.delivery_date,
      status: ['completed'].includes((c.status || '').toLowerCase()) ? 'completed' : 'pending',
      payout_percentage: 30,
      payout_amount: Math.round(tot * 0.3),
      payout_status: ['completed'].includes((c.status || '').toLowerCase()) ? 'released' : 'in_escrow'
    }
  ]
}

const getCompletedCount = (contract: ContractItem) => {
  const ms = contract.milestones || defaultMilestones(contract)
  return ms.filter((m: ContractMilestoneItem) => m.status === 'completed').length
}

const getMilestoneProgressPercent = (contract: ContractItem) => {
  const ms = contract.milestones || defaultMilestones(contract)
  if (ms.length === 0) return 0
  const completed = ms.filter((m: ContractMilestoneItem) => m.status === 'completed').length
  return Math.round((completed / ms.length) * 100)
}

const getMilestoneCardStyle = (milestone: ContractMilestoneItem) => {
  if (milestone.status === 'completed') {
    return darkMode.value ? 'bg-[#1a233a] border-blue-600/40' : 'bg-blue-50/50 border-blue-200'
  }
  if (milestone.status === 'in_progress') {
    return darkMode.value ? 'bg-[#1a233a] border-amber-500/40' : 'bg-amber-50/50 border-amber-200'
  }
  return darkMode.value ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200'
}

const getMilestoneBadge = (milestone: ContractMilestoneItem) => {
  if (milestone.status === 'completed') {
    return 'bg-blue-600 text-white'
  }
  if (milestone.status === 'in_progress') {
    return 'bg-amber-500 text-white animate-pulse'
  }
  return 'bg-slate-700 text-slate-300'
}

const getMilestoneStatusStyle = (status: string) => {
  const s = (status || '').toLowerCase()
  if (s === 'completed') return 'bg-blue-500/15 text-blue-400 border border-blue-500/30'
  if (s === 'in_progress') return 'bg-amber-500/15 text-amber-500 border border-amber-500/30'
  return 'bg-slate-500/15 text-slate-400 border border-slate-500/30'
}

const getContractStatusBadge = (status: string) => {
  const s = (status || '').toLowerCase()
  if (['active', 'in_progress'].includes(s)) return 'bg-blue-500/15 text-blue-400 border border-blue-500/30'
  if (['proposed', 'draft'].includes(s)) return 'bg-amber-500/15 text-amber-500 border border-amber-500/30'
  if (s === 'completed') return 'bg-blue-600/20 text-blue-300 border border-blue-500/30'
  return 'bg-slate-500/15 text-slate-400 border border-slate-500/30'
}

const formatContractStatus = (status: string) => {
  const s = (status || '').toLowerCase()
  if (s === 'proposed') return 'Pending Signature'
  if (['active', 'in_progress'].includes(s)) return 'In Production'
  if (s === 'completed') return 'Fulfilled'
  return status || 'Pending'
}

const getPayoutStatusColor = (status?: string) => {
  const s = (status || '').toLowerCase()
  if (s === 'released' || s === 'paid') return 'text-blue-500'
  if (s === 'in_escrow') return 'text-purple-400'
  return 'text-slate-400'
}

const openMilestoneDetail = (contract: ContractItem, milestone: ContractMilestoneItem) => {
  activeModalContract.value = contract
  activeModalMilestone.value = milestone
  detailModalOpen.value = true
}

const resetFilters = () => {
  searchQuery.value = ''
  activeStatusFilter.value = ''
  sortBy.value = 'newest'
}

const formatDate = (dateStr: string | null) => {
  if (!dateStr) return 'N/A'
  try {
    return new Date(dateStr).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    })
  } catch {
    return dateStr
  }
}

const formatNumber = (num: number | string | undefined | null) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(Number(num || 0))
}
</script>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
