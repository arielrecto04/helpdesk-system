<script setup>
import { Head, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

const page = usePage();
const ticket = page.props.ticket;

const showProfileModal = ref(false);
const isSidebarOpen = ref(true);
const searchQuery = ref('');

const getPriorityClass = (priority) => ({
  'bg-red-50 text-red-700 border-red-200': priority === 'Urgent',
  'bg-orange-50 text-orange-700 border-orange-200': priority === 'High',
  'bg-blue-50 text-blue-700 border-blue-200': priority === 'Medium',
  'bg-slate-50 text-slate-700 border-slate-200': priority === 'Low'
});

const getStageClass = (stage) => ({
  'bg-emerald-50 text-emerald-700 border-emerald-200': ['Resolved', 'Closed'].includes(stage),
  'bg-blue-50 text-blue-700 border-blue-200': stage === 'Open',
  'bg-amber-50 text-amber-700 border-amber-200': stage === 'In Progress',
});

const getPriorityIcon = (priority) => {
  const icons = {
    'Urgent': '🔴',
    'High': '🟠',
    'Medium': '🟡',
    'Low': '⚪'
  };
  return icons[priority] || '⚪';
};

const goBack = () => router.visit(route('customer.dashboard'));

const logout = () => router.post(route('logout'));
</script>

<template>
  <Head :title="`Ticket #${ticket.id}`" />

  <div class="min-h-screen bg-slate-50 flex overflow-hidden">
    <!-- Left Sidebar Navigation -->
    <aside 
      class="bg-white border-r border-slate-200 flex flex-col transition-all duration-300 overflow-hidden whitespace-nowrap"
      :class="[ isSidebarOpen ? 'w-64 opacity-100 shadow-[0_20px_40px_-10px_rgba(2,6,23,0.65)]' : 'w-0 opacity-0 border-none p-0 shadow-none' ]">
      <!-- Logo/Brand -->
      <div class="p-6 border-b border-slate-200 flex items-center gap-3">
        <img src="/images/iits_logo.png" alt="IITS Logo" class="w-11 h-10 object-contain" />
        <div>
          <h1 class="text-xl font-bold text-slate-800">Customer Portal</h1>
          <p class="text-xs text-slate-500 mt-1">Dashboard</p>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 p-4 space-y-1">
        <a href="#" @click.prevent="goBack" 
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 border border-transparent text-slate-600 hover:bg-slate-300">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <span class="font-medium text-sm">Back to Tickets</span>
        </a>
      </nav>

      <!-- Bottom User Section -->
      <div class="p-4 border-t border-slate-200">
        <button @click="showProfileModal = true" class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-300 transition">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold">
            {{ $page.props.auth.user?.first_name?.[0] || 'U' }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-800 truncate">{{ $page.props.auth.user?.first_name || 'User' }}</p>
            <p class="text-xs text-slate-700 truncate">{{ $page.props.auth.user?.email || '' }}</p>
          </div>
          <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Header Bar -->
      <header class="bg-white border-b border-slate-400 px-8 py-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <button 
              @click="isSidebarOpen = !isSidebarOpen"
              class="p-2 rounded-lg bg-slate-200 text-slate-500 hover:bg-slate-300 hover:text-blue-700 focus:outline-none transition-colors"
              title="Toggle Sidebar"
            >
              <svg v-if="!isSidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
              </svg>
            </button>

            <div>
              <h2 class="text-2xl font-bold text-slate-800">Ticket #{{ ticket.id }}</h2>
              <p class="text-sm text-slate-500 mt-1">View ticket details and status</p>
            </div>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="mt-6 relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input v-model="searchQuery" type="text" placeholder="Search tickets by ID, subject, or team..."
            class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" />
        </div>
      </header>

      <!-- Ticket Content -->
      <div class="px-8 py-8 max-w-5xl">
        <!-- Ticket Header Card -->
        <div class="bg-white border border-slate-500 rounded-2xl p-8 mb-6 shadow-sm">
          <div class="flex items-start justify-between mb-6">
            <div class="flex-1">
              <h2 class="text-2xl font-bold text-slate-800 mb-4">{{ ticket.subject }}</h2>
              <div class="flex items-center gap-3 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl border" :class="getStageClass(ticket.stage)">
                  <span class="w-2 h-2 rounded-full bg-current"></span>
                  {{ ticket.stage }}
                </span>
                <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl border" :class="getPriorityClass(ticket.priority)">
                  <span>{{ getPriorityIcon(ticket.priority) }}</span>
                  {{ ticket.priority }} Priority
                </span>
              </div>
            </div>
          </div>

          <!-- Ticket Meta Information -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-slate-400">
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Created</p>
                <p class="text-sm font-semibold text-slate-800">{{ new Date(ticket.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ new Date(ticket.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }}</p>
              </div>
            </div>

            <div class="flex items-start gap-3">
              <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Support Team</p>
                <p class="text-sm font-semibold text-slate-800">{{ ticket.team?.team_name || 'Unassigned' }}</p>
              </div>
            </div>

            <div v-if="ticket.deadline" class="flex items-start gap-3">
              <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Deadline</p>
                <p class="text-sm font-semibold text-slate-800">{{ ticket.deadline }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Description Section -->
        <div class="bg-white border border-slate-500 rounded-2xl p-8 shadow-sm">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Description</h3>
          </div>
          <div class="prose prose-slate max-w-none">
            <p class="text-slate-700 leading-relaxed whitespace-pre-wrap">{{ ticket.description }}</p>
          </div>
        </div>

          <!-- Tags -->
          <div class="bg-white border border-slate-500 rounded-2xl p-8 shadow-sm mt-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Tags</h3>
            <div class="flex flex-wrap gap-2">
              <span v-for="tag in ticket.tags || []" :key="tag.id" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-800 border">
                {{ tag.name }}
              </span>
              <span v-if="!(ticket.tags && ticket.tags.length)" class="text-sm text-slate-500">No tags</span>
            </div>
          </div>

        <!-- Assigned To Section (if assigned) -->
        <div v-if="ticket.assignedTo" class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm mt-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Assigned Agent</h3>
          </div>
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white font-bold text-lg">
              {{ ticket.assignedTo.first_name?.[0] || 'A' }}
            </div>
            <div>
              <p class="text-sm font-semibold text-slate-800">{{ ticket.assignedTo.first_name }} {{ ticket.assignedTo.last_name }}</p>
              <p class="text-xs text-slate-500">Support Agent</p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Profile Modal -->
    <Modal :show="showProfileModal" @close="showProfileModal = false" maxWidth="md">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Profile</h3>
        <div class="space-y-3 text-sm text-slate-700">
          <div>
            <span class="font-medium">First name:</span>
            <div class="text-slate-600">{{ $page.props.auth.user?.first_name || '-' }}</div>
          </div>
          <div>
            <span class="font-medium">Middle name:</span>
            <div class="text-slate-600">{{ $page.props.auth.user?.middle_name || '-' }}</div>
          </div>
          <div>
            <span class="font-medium">Last name:</span>
            <div class="text-slate-600">{{ $page.props.auth.user?.last_name || '-' }}</div>
          </div>
          <div>
            <span class="font-medium">Email:</span>
            <div class="text-slate-600">{{ $page.props.auth.user?.email || '-' }}</div>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button @click="showProfileModal = false" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700">Close</button>
          <button @click="logout" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Log Out</button>
        </div>
      </div>
    </Modal>

  </div>
</template>
