<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">
    <!-- Responsive Sidebar Container -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar @logout="handleLogout" />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area with proper 64-unit left margin -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen lg:ml-64 transition-all flex flex-col" role="main">
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
                  Producer &amp; Supplier Inquiries
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Account &gt; Messages
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <Mail class="w-7 h-7 text-blue-500" />
                Messages &amp; Direct Negotiations
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Direct communication channel with agricultural input suppliers, farming cooperatives, and logistics transport dispatchers
              </p>
            </div>
          </div>

          <!-- Header Right Actions -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <button
              @click="openNewChatModal"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all transform active:scale-95 cursor-pointer"
            >
              <Plus class="w-4 h-4" />
              <span>New Conversation</span>
            </button>
            <BuyerHeaderNav />
          </div>
        </div>
      </div>

      <!-- ── DATABASE ERROR BANNER ── -->
      <div
        v-if="errorMessage"
        :class="['p-4 rounded-2xl border mb-6 flex items-start justify-between gap-3', darkMode ? 'bg-rose-500/10 border-rose-500/20 text-rose-200' : 'bg-rose-50 border-rose-200 text-rose-800']"
      >
        <div class="flex items-center gap-3">
          <AlertCircle class="w-5 h-5 text-rose-500 shrink-0" />
          <div>
            <div class="text-xs font-bold">Database Error</div>
            <div class="text-xs opacity-90">{{ errorMessage }}</div>
          </div>
        </div>
        <button
          @click="fetchConversations"
          class="px-3 py-1 rounded-lg text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition"
        >
          Retry
        </button>
      </div>

      <!-- ── CHAT CONTAINER (SPLIT PANE) ── -->
      <div :class="['flex-1 rounded-3xl border overflow-hidden transition-all flex flex-col md:flex-row min-h-[580px]', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
        <!-- LEFT COLUMN: Conversation List -->
        <div :class="['w-full md:w-80 lg:w-96 border-b md:border-b-0 md:border-r flex flex-col shrink-0', darkMode ? 'border-slate-800 bg-slate-900/40' : 'border-slate-200 bg-slate-50/50']">
          <!-- Search Bar in conversation list -->
          <div class="p-3.5 border-b border-slate-200 dark:border-slate-800">
            <div class="relative">
              <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search conversations..."
                :class="['w-full pl-9 pr-3 py-2 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              />
            </div>
          </div>

          <!-- Conversations scroll list -->
          <div class="flex-1 overflow-y-auto divide-y divide-slate-200 dark:divide-slate-800">
            <div v-if="loading" class="p-4 space-y-3">
              <div v-for="i in 3" :key="i" class="animate-pulse flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-300 dark:bg-slate-700"></div>
                <div class="flex-1 space-y-1.5">
                  <div class="w-1/2 h-3.5 rounded bg-slate-300 dark:bg-slate-700"></div>
                  <div class="w-3/4 h-2.5 rounded bg-slate-200 dark:bg-slate-800"></div>
                </div>
              </div>
            </div>

            <!-- Empty Conversation List -->
            <div v-else-if="filteredConversations.length === 0" class="p-8 text-center">
              <div class="w-12 h-12 mx-auto mb-2 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                <Mail class="w-6 h-6" />
              </div>
              <p class="text-xs font-semibold mb-1">No Active Threads</p>
              <p class="text-[11px] text-slate-400 mb-3">Reach out to agricultural suppliers or farming partners.</p>
              <button
                @click="openNewChatModal"
                class="px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm cursor-pointer"
              >
                Start Chat
              </button>
            </div>

            <!-- Conversation cards -->
            <button
              v-for="c in filteredConversations"
              :key="c.id"
              @click="selectConversation(c)"
              :class="[
                'w-full text-left p-3.5 transition-colors flex items-start gap-3 cursor-pointer',
                selectedConversation && selectedConversation.id === c.id
                  ? darkMode
                    ? 'bg-blue-600/15 border-l-4 border-l-blue-500'
                    : 'bg-blue-50 border-l-4 border-l-blue-600'
                  : darkMode
                    ? 'hover:bg-slate-800/40'
                    : 'hover:bg-slate-100'
              ]"
            >
              <!-- Avatar -->
              <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 font-bold text-xs flex items-center justify-center uppercase shrink-0 border border-blue-500/20">
                {{ c.partner_name ? c.partner_name.substring(0, 2) : 'PR' }}
              </div>

              <!-- Meta -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1 mb-0.5">
                  <span class="font-bold text-xs truncate">{{ c.partner_name }}</span>
                  <span class="text-[10px] text-slate-400 shrink-0">{{ c.last_message_formatted }}</span>
                </div>
                <div class="flex items-center gap-1.5 mb-1">
                  <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                    {{ c.partner_role }}
                  </span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate m-0">
                  {{ c.last_message }}
                </p>
              </div>

              <!-- Unread badge -->
              <span
                v-if="c.unread_count > 0"
                class="w-5 h-5 rounded-full bg-blue-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0 shadow-sm"
              >
                {{ c.unread_count }}
              </span>
            </button>
          </div>
        </div>

        <!-- RIGHT COLUMN: Active Chat Thread -->
        <div class="flex-1 flex flex-col min-w-0 bg-transparent">
          <!-- Active Conversation Header -->
          <div
            v-if="selectedConversation"
            class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 font-bold text-xs flex items-center justify-center uppercase shrink-0 border border-blue-500/20">
                {{ selectedConversation.partner_name ? selectedConversation.partner_name.substring(0, 2) : 'PR' }}
              </div>
              <div class="min-w-0">
                <h3 class="font-bold text-sm m-0 truncate">{{ selectedConversation.partner_name }}</h3>
                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                  <span class="text-blue-500 font-semibold">{{ selectedConversation.partner_role }}</span>
                  <span v-if="selectedConversation.partner_phone">&bull; {{ selectedConversation.partner_phone }}</span>
                </div>
              </div>
            </div>

            <!-- Call / Contact shortcut -->
            <a
              v-if="selectedConversation.partner_phone"
              :href="'tel:' + selectedConversation.partner_phone"
              class="p-2 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-blue-500/10 text-blue-500 transition flex items-center justify-center"
              title="Direct Phone Call"
            >
              <PhoneCall class="w-4 h-4" />
            </a>
          </div>

          <!-- Empty thread state -->
          <div
            v-if="!selectedConversation"
            class="flex-1 flex flex-col items-center justify-center p-8 text-center"
          >
            <div class="w-16 h-16 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center mb-4">
              <MessageSquare class="w-8 h-8" />
            </div>
            <h3 class="text-base font-bold mb-1">Select a Conversation</h3>
            <p class="text-xs text-slate-400 max-w-sm mb-4">
              Choose an active thread from the left or click 'New Conversation' to initiate commercial inquiries with suppliers or farmers.
            </p>
            <button
              @click="openNewChatModal"
              class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm cursor-pointer"
            >
              Start New Chat
            </button>
          </div>

          <!-- Messages Chat History -->
          <div
            v-else
            ref="messagesContainer"
            class="flex-1 p-4 lg:p-6 overflow-y-auto space-y-4"
          >
            <div v-if="loadingMessages" class="py-12 text-center text-xs text-slate-400">
              Loading chat messages...
            </div>

            <div v-else-if="activeMessages.length === 0" class="py-12 text-center text-xs text-slate-400">
              No messages in this conversation yet. Send the first message below.
            </div>

            <div
              v-for="msg in activeMessages"
              :key="msg.id"
              :class="['flex', msg.is_me ? 'justify-end' : 'justify-start']"
            >
              <div
                :class="[
                  'max-w-[78%] rounded-2xl px-4 py-2.5 text-xs shadow-sm',
                  msg.is_me
                    ? 'bg-blue-600 text-white rounded-br-xs'
                    : darkMode
                      ? 'bg-slate-800 text-slate-100 rounded-bl-xs border border-slate-700'
                      : 'bg-slate-100 text-slate-900 rounded-bl-xs border border-slate-200'
                ]"
              >
                <p class="m-0 leading-relaxed whitespace-pre-wrap">{{ msg.message }}</p>
                <div :class="['text-[9px] mt-1 text-right flex items-center justify-end gap-1', msg.is_me ? 'text-blue-200' : 'text-slate-400']">
                  <span>{{ msg.time_formatted }}</span>
                  <Check v-if="msg.is_me" class="w-3 h-3 inline" />
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Message Input Bar -->
          <div
            v-if="selectedConversation"
            class="p-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40"
          >
            <form @submit.prevent="sendReply" class="flex items-center gap-2">
              <input
                v-model="replyText"
                type="text"
                placeholder="Type your message, quote response, or specifications..."
                :class="['flex-1 px-4 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              />
              <button
                type="submit"
                :disabled="!replyText.trim() || sendingReply"
                class="px-4 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition disabled:opacity-50 flex items-center gap-1.5 cursor-pointer shrink-0"
              >
                <Send class="w-3.5 h-3.5" />
                <span class="hidden sm:inline">{{ sendingReply ? 'Sending...' : 'Send' }}</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </main>

    <!-- ── START NEW CONVERSATION MODAL ── -->
    <div
      v-if="newChatModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="newChatModalOpen = false"
    >
      <div :class="['w-full max-w-md rounded-3xl border shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
          <h2 class="text-base font-bold m-0">Start New Conversation</h2>
          <button @click="newChatModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitNewChat" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Select Partner / Seller *</label>
            <select
              v-model="newChatForm.partner_id"
              required
              :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            >
              <option value="" disabled>Choose a farmer, supplier, or contact...</option>
              <option v-for="c in contactsList" :key="c.id" :value="c.id">
                {{ c.name }} ({{ c.role.toUpperCase() }}) - {{ c.region || 'Ethiopia' }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Initial Message *</label>
            <textarea
              v-model="newChatForm.message"
              rows="4"
              required
              placeholder="State your produce inquiry, delivery terms, or quotation request..."
              :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            ></textarea>
          </div>

          <div class="pt-2 flex items-center justify-end gap-3">
            <button
              type="button"
              @click="newChatModalOpen = false"
              class="px-4 py-2 rounded-xl border text-xs font-bold text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="startingChat || !newChatForm.partner_id || !newChatForm.message.trim()"
              class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm disabled:opacity-50 cursor-pointer"
            >
              <Send class="w-3.5 h-3.5" />
              <span>{{ startingChat ? 'Initiating...' : 'Send Message' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { buyerAPI } from '@/api/buyer'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import BuyerHeaderNav from '@/components/layout/BuyerHeaderNav.vue'
import {
  Menu, Mail, Plus, AlertCircle, X, Search,
  PhoneCall, MessageSquare, Send, Check
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const loading = ref(true)
const errorMessage = ref('')

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

// Conversation and Chat State
const conversations = ref<any[]>([])
const searchQuery = ref('')
const selectedConversation = ref<any>(null)
const activeMessages = ref<any[]>([])
const loadingMessages = ref(false)
const replyText = ref('')
const sendingReply = ref(false)
const messagesContainer = ref<HTMLElement | null>(null)

// New Chat Modal
const newChatModalOpen = ref(false)
const contactsList = ref<any[]>([])
const startingChat = ref(false)
const newChatForm = ref({
  partner_id: '',
  message: ''
})

const fetchConversations = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await buyerAPI.getConversations()
    if (res.data && res.data.success) {
      conversations.value = res.data.data || []
      // Auto-select first if none selected
      if (!selectedConversation.value && conversations.value.length > 0) {
        selectConversation(conversations.value[0])
      }
    } else {
      conversations.value = []
    }
  } catch (err: any) {
    console.error('Failed to load conversations:', err)
    errorMessage.value = err.response?.data?.message || 'Failed to retrieve messaging conversations from database.'
    conversations.value = []
  } finally {
    loading.value = false
  }
}

const selectConversation = async (conv: any) => {
  selectedConversation.value = conv
  loadingMessages.value = true
  try {
    const res = await buyerAPI.getMessages(conv.id)
    if (res.data && res.data.success) {
      activeMessages.value = res.data.data.messages || []
      conv.unread_count = 0
      scrollToBottom()
    }
  } catch (err) {
    console.error('Failed to load messages thread:', err)
  } finally {
    loadingMessages.value = false
  }
}

const sendReply = async () => {
  if (!selectedConversation.value || !replyText.value.trim()) return
  sendingReply.value = true
  const text = replyText.value.trim()
  try {
    const res = await buyerAPI.sendMessage(selectedConversation.value.id, { message: text })
    if (res.data && res.data.success) {
      activeMessages.value.push(res.data.data)
      selectedConversation.value.last_message = text
      selectedConversation.value.last_message_formatted = 'Just now'
      replyText.value = ''
      scrollToBottom()
    }
  } catch (err: any) {
    console.error('Failed to send message:', err)
  } finally {
    sendingReply.value = false
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const openNewChatModal = async () => {
  newChatForm.value = { partner_id: '', message: '' }
  newChatModalOpen.value = true
  try {
    const res = await buyerAPI.getMessageContacts()
    if (res.data && res.data.success) {
      contactsList.value = res.data.data || []
    }
  } catch (err) {
    console.error('Failed to load contacts list:', err)
  }
}

const submitNewChat = async () => {
  if (!newChatForm.value.partner_id || !newChatForm.value.message.trim()) return
  startingChat.value = true
  try {
    const res = await buyerAPI.startConversation(newChatForm.value)
    if (res.data && res.data.success) {
      newChatModalOpen.value = false
      await fetchConversations()
      const newConv = conversations.value.find(c => c.id === res.data.data.conversation_id)
      if (newConv) {
        selectConversation(newConv)
      }
    }
  } catch (err: any) {
    console.error('Error starting conversation:', err)
  } finally {
    startingChat.value = false
  }
}

const filteredConversations = computed(() => {
  if (!searchQuery.value.trim()) return conversations.value
  const q = searchQuery.value.trim().toLowerCase()
  return conversations.value.filter(c => {
    const name = (c.partner_name || '').toLowerCase()
    const msg = (c.last_message || '').toLowerCase()
    const role = (c.partner_role || '').toLowerCase()
    return name.includes(q) || msg.includes(q) || role.includes(q)
  })
})

onMounted(() => {
  fetchConversations()
})
</script>
