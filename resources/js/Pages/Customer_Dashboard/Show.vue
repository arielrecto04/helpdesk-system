<script setup>
import { Head, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const ticket = page.props.ticket;

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

const goBack = () => {
  router.visit(route('customer.dashboard'));
};
</script>

<template>
  <Head :title="`Ticket #${ticket.id}`" />

  <div class="min-h-screen bg-slate-50 flex">
    <!-- Left Sidebar Navigation -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col">
      <!-- Logo/Brand -->
      <div class="p-6 border-b border-slate-200">
        <h1 class="text-xl font-bold text-slate-800">Support Hub</h1>
        <p class="text-xs text-slate-500 mt-1">Customer Portal</p>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 p-4 space-y-1">
        <a href="#" @click.prevent="goBack"
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 border border-transparent text-slate-600 hover:bg-slate-50">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <span class="font-medium text-sm">Back to Tickets</span>
        </a>

        <a href="#" @click.prevent="goBack"
          class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 border border-transparent text-slate-600 hover:bg-slate-50">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <span class="font-medium text-sm">All Tickets</span>
        </a>
      </nav>

      <!-- Bottom User Section -->
      <div class="p-4 border-t border-slate-200">
        <div class="flex items-center gap-3 px-3 py-2">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold">
            {{ $page.props.auth.user?.first_name?.[0] || 'U' }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-800 truncate">{{ $page.props.auth.user?.first_name || 'User' }}</p>
            <p class="text-xs text-slate-500 truncate">{{ $page.props.auth.user?.email || '' }}</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto">
      <!-- Top Header -->
      <header class="bg-white border-b border-slate-200 px-8 py-6 sticky top-0 z-10">
        <div class="flex items-center gap-4">
          <button @click="goBack" 
            class="p-2 hover:bg-slate-100 rounded-lg transition-colors duration-200">
            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          <div>
            <h1 class="text-2xl font-bold text-slate-800">Ticket #{{ ticket.id }}</h1>
            <p class="text-sm text-slate-500 mt-1">View ticket details and status</p>
          </div>
        </div>
      </header>

      <!-- Ticket Content -->
      <div class="px-8 py-8 max-w-5xl">
        <!-- Ticket Header Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-8 mb-6 shadow-sm">
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
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t border-slate-200">
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
        <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
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
  </div>
</template>
