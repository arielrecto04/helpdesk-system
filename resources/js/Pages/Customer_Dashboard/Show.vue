<script setup>
import { Head, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import TicketChat from '@/Components/TicketChat.vue';
import StarRating from '@/Components/StarRating.vue';
import { ref, reactive, computed } from 'vue';

const page = usePage();
const ticket = page.props.ticket;
const messages = page.props.messages || [];
const canRate = page.props.can_rate || false;
const existingRating = page.props.existing_rating || null;

const authUser = page.props.auth ? page.props.auth.user : null;
const messagesCount = page.props.messages_count ?? messages.length ?? 0;

const rating = ref(existingRating ? existingRating.rating : 5);
const comment = ref(existingRating ? existingRating.comment : '');
const errors = reactive({ rating: null, comment: null });
const isSubmitting = ref(false);

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

const submitRating = () => {
  // Client-side validation
  errors.rating = null;
  errors.comment = null;

  if (!rating.value || rating.value < 1 || rating.value > 5) {
    errors.rating = 'Please select a rating between 1 and 5.';
  }
  if (comment.value && comment.value.length > 1000) {
    errors.comment = 'Comment must be 1000 characters or fewer.';
  }
  if (errors.rating || errors.comment) return;

  isSubmitting.value = true;
  router.post(route('customer.tickets.rating', ticket.id), {
    rating: rating.value,
    comment: comment.value,
  }, {
    onFinish: () => { isSubmitting.value = false; }
  });
};
const isAssignedToAuthUser = computed(() => {
  if (!authUser) return false;
  const authId = authUser.id ?? authUser.user_id ?? null;
  if (!authId) return false;

  const resolveId = (val) => {
    if (val == null) return null;
    if (typeof val === 'number' || typeof val === 'string') return String(val);
    if (val.user_id) return String(val.user_id);
    if (val.id) return String(val.id);
    return null;
  };

  const candidates = [
    resolveId(ticket?.assigned_to),
    resolveId(ticket?.assignedTo),
    resolveId(ticket?.assigned_employee),
    resolveId(ticket?.assigned_to?.user),
    resolveId(ticket?.assignedTo?.user),
    resolveId(ticket?.assigned_employee?.user),
    resolveId(ticket?.assigned_to_id),
    resolveId(ticket?.assignedTo?.user_id),
  ].filter(Boolean);

  return candidates.some(id => String(id) === String(authId));
});

</script>

<template>
  <Head :title="`Ticket #${ticket.id}`" />

  <div class="h-screen w-full bg-slate-50 flex overflow-hidden">
    <!-- Left Sidebar Navigation -->
    <aside 
      class="bg-gradient-to-b from-white via-slate-50 to-white border-r border-slate-200 flex flex-col transition-all duration-300 overflow-hidden whitespace-nowrap"
      :class="[ isSidebarOpen ? 'w-64 opacity-100 shadow-[0_8px_30px_rgb(0,0,0,0.12)]' : 'w-0 opacity-0 border-none p-0 shadow-none' ]">
      <!-- Logo/Brand -->
      <div class="p-6 border-b border-slate-200 flex items-center gap-3 bg-gradient-to-r from-blue-50/50 to-transparent">
        <div class="w-11 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 p-2 shadow-lg shadow-blue-500/20">
          <img src="/images/iits_logo.png" alt="IITS Logo" class="w-full h-full object-contain" />
        </div>
        <div>
          <h1 class="text-xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">Customer Portal</h1>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Support Dashboard</p>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 p-4 space-y-1">
        <a href="#" @click.prevent="goBack" 
          class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 border border-transparent text-slate-600 hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100/50 hover:border-blue-200 hover:text-blue-700 hover:shadow-sm">
          <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <span class="font-medium text-sm">Back to Tickets</span>
        </a>
      </nav>

      <!-- Bottom User Section -->
      <div class="p-4 border-t border-slate-200 bg-slate-50/50">
        <button @click="showProfileModal = true" class="group w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white hover:shadow-md transition-all duration-200 border border-transparent hover:border-slate-200">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 flex items-center justify-center text-white font-semibold shadow-md ring-2 ring-white">
            {{ $page.props.auth.user?.first_name?.[0] || 'U' }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-slate-800 truncate group-hover:text-blue-700 transition-colors">{{ $page.props.auth.user?.first_name || 'User' }}</p>
            <p class="text-xs text-slate-500 truncate">{{ $page.props.auth.user?.email || '' }}</p>
          </div>
          <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition-all group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-hidden">
      <!-- Top Header Bar -->
      <header class="bg-gradient-to-br from-white via-slate-50/30 to-blue-50/20 border-b border-slate-200 px-8 py-6 shadow-sm">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <button 
              @click="isSidebarOpen = !isSidebarOpen"
              class="p-2.5 rounded-xl bg-white text-slate-600 hover:bg-blue-50 hover:text-blue-600 focus:outline-none transition-all duration-200 border border-slate-200 hover:border-blue-300 hover:shadow-md"
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
        <div class="mt-6 relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input v-model="searchQuery" type="text" placeholder="Search tickets by ID, subject, or team..."
            class="w-full pl-12 pr-4 py-3.5 bg-white border-2 border-slate-200 rounded-xl text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all duration-200 shadow-sm hover:shadow-md" />
        </div>
      </header>

      <!-- Ticket Content -->
      <div class="flex-1 overflow-y-auto px-8 py-6 space-y-4">
        <!-- Ticket Header Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-8 mb-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
          <div class="flex items-start justify-between mb-6">
            <div class="flex-1">
              <h2 class="text-2xl font-bold text-slate-800 mb-4">{{ ticket.subject }}</h2>
              <div class="flex items-center gap-3 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl border-2 shadow-md" :class="getStageClass(ticket.stage)">
                  <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                  {{ ticket.stage }}
                </span>
                <span class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl border-2 shadow-md" :class="getPriorityClass(ticket.priority)">
                  <span class="text-base">{{ getPriorityIcon(ticket.priority) }}</span>
                  {{ ticket.priority }} Priority
                </span>
              </div>
            </div>
          </div>

          <!-- Ticket Meta Information -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-slate-200">
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
              <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-purple-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Support Team</p>
                <p class="text-sm font-semibold text-slate-800">{{ ticket.team?.team_name || 'Unassigned' }}</p>
              </div>
            </div>

            <div v-if="ticket.deadline" class="flex items-start gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-gradient-to-br from-slate-600 to-slate-700 rounded-xl flex items-center justify-center shadow-lg shadow-slate-500/30">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Description</h3>
          </div>
          <div class="prose prose-slate max-w-none">
            <p class="text-slate-700 leading-relaxed whitespace-pre-wrap bg-slate-50 p-4 rounded-xl border border-slate-100">{{ ticket.description }}</p>
          </div>
        </div>

          <!-- Tags -->
          <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 mt-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-slate-800">Tags</h3>
            </div>
            <div class="flex flex-wrap gap-2">
              <span v-for="tag in ticket.tags || []" :key="tag.id" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-indigo-50 to-blue-50 text-indigo-700 border-2 border-indigo-200 hover:shadow-md transition-shadow">
                {{ tag.name }}
              </span>
              <span v-if="!(ticket.tags && ticket.tags.length)" class="text-sm text-slate-500 italic">No tags assigned</span>
            </div>
          </div>

            <!-- Assigned To Section (if assigned) -->
        <div v-if="ticket.assignedTo" class="bg-white border border-slate-200 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 mt-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/30">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Assigned Agent</h3>
          </div>
          <div class="flex items-center gap-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white font-bold text-lg shadow-lg ring-2 ring-white">
              {{ ticket.assignedTo.first_name?.[0] || 'A' }}
            </div>
            <div>
              <p class="text-sm font-semibold text-slate-800">{{ ticket.assignedTo.first_name }} {{ ticket.assignedTo.last_name }}</p>
              <p class="text-xs text-green-700 font-medium">Support Agent</p>
            </div>
          </div>
        </div>

        <!-- Rating Section (customer-facing) -->
        <div class="bg-gradient-to-br from-white to-amber-50/30 border border-slate-200 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 mt-6">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/30">
              <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Rate Your Experience</h3>
          </div>

          <div v-if="existingRating" class="bg-white p-6 rounded-xl border-2 border-amber-200 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
              <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20" v-for="n in existingRating.rating" :key="n">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span class="font-bold text-slate-800 text-lg">{{ existingRating.rating }} / 5</span>
            </div>
            <p class="text-slate-700 mt-3 italic">{{ existingRating.comment || 'No comment provided' }}</p>
            <p class="mt-3 text-xs text-slate-500 flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Submitted: {{ existingRating.submitted_on }}
            </p>
          </div>

          <div v-else-if="canRate">
            <form @submit.prevent="submitRating" class="space-y-6">
              <div class="bg-white p-6 rounded-xl border border-slate-200">
                <label class="block text-sm font-semibold text-slate-700 mb-3">How would you rate your experience?</label>
                <StarRating v-model="rating" />
                <p v-if="errors.rating" class="text-xs text-red-600 mt-2 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  {{ errors.rating }}
                </p>
              </div>

              <div class="bg-white p-6 rounded-xl border border-slate-200">
                <label class="block text-sm font-semibold text-slate-700 mb-3">Share your feedback (optional)</label>
                <textarea v-model="comment" rows="4" placeholder="Tell us about your experience..." class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all resize-none"></textarea>
                <p v-if="errors.comment" class="text-xs text-red-600 mt-2 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  {{ errors.comment }}
                </p>
              </div>

              <div class="flex justify-end">
                <button type="submit" :disabled="isSubmitting" class="group px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-200 flex items-center gap-2">
                  <svg v-if="isSubmitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span v-if="isSubmitting">Submitting...</span>
                  <span v-else>Submit Rating</span>
                </button>
              </div>
            </form>
          </div>

          <div v-else class="bg-blue-50 border border-blue-200 rounded-xl p-6 flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-sm font-semibold text-blue-900 mb-1">Rating Not Available</p>
              <p class="text-sm text-blue-700">You can submit a rating after this ticket has been resolved or closed.</p>
            </div>
          </div>
        </div>

        <!-- Chat Component -->
        <div class="mt-6">
            <div v-if="ticket?.employee_id">
              <TicketChat :ticketId="ticket.id" :initialMessages="messages" :initialMessagesCount="messagesCount" />
            </div>
        </div>
      </div>
    </main>

    <!-- Profile Modal -->
    <Modal :show="showProfileModal" @close="showProfileModal = false" maxWidth="md">
      <div class="p-8">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-200">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 flex items-center justify-center text-white font-bold text-2xl shadow-lg ring-4 ring-blue-100">
            {{ $page.props.auth.user?.first_name?.[0] || 'U' }}
          </div>
          <div>
            <h3 class="text-xl font-bold text-slate-800">Your Profile</h3>
            <p class="text-sm text-slate-500 mt-1">Account Information</p>
          </div>
        </div>
        
        <div class="space-y-4 text-sm">
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
            <span class="font-semibold text-slate-500 text-xs uppercase tracking-wide">First name</span>
            <div class="text-slate-800 font-medium mt-1">{{ $page.props.auth.user?.first_name || '-' }}</div>
          </div>
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
            <span class="font-semibold text-slate-500 text-xs uppercase tracking-wide">Middle name</span>
            <div class="text-slate-800 font-medium mt-1">{{ $page.props.auth.user?.middle_name || '-' }}</div>
          </div>
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
            <span class="font-semibold text-slate-500 text-xs uppercase tracking-wide">Last name</span>
            <div class="text-slate-800 font-medium mt-1">{{ $page.props.auth.user?.last_name || '-' }}</div>
          </div>
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
            <span class="font-semibold text-slate-500 text-xs uppercase tracking-wide">Email</span>
            <div class="text-slate-800 font-medium mt-1">{{ $page.props.auth.user?.email || '-' }}</div>
          </div>
        </div>

        <div class="mt-8 flex justify-end gap-3">
          <button @click="showProfileModal = false" class="px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 font-medium hover:bg-slate-50 hover:border-slate-300 transition-all">Close</button>
          <button @click="logout" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold hover:shadow-lg hover:shadow-red-500/30 transition-all duration-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Log Out
          </button>
        </div>
      </div>
    </Modal>

  </div>
</template>
