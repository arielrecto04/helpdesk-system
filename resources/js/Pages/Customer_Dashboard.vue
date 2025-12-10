<script setup>

import { Head, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import CreateTicket from '@/Pages/Customer_Dashboard/Create.vue';
import { ref, computed } from 'vue';

const props = defineProps({
	tickets: {
		type: Object,
		required: true,
	},
	pageTitle: {
		type: String,
		required: true,
	},
	teams: { type: Array, default: () => [] },
	priorities: { type: Array, default: () => ['Low', 'Medium', 'High', 'Urgent'] },
	customer_id: { type: [Number, null], default: null },
	tags: { type: Array, default: () => [] },
});

const showCreateModal = ref(false);
const showProfileModal = ref(false);
const searchQuery = ref('');
const activeFilter = ref('all');

const filteredTickets = computed(() => {
	let filtered = props.tickets.data || [];
	
	// Search filter
	if (searchQuery.value) {
		const query = searchQuery.value.toLowerCase();
		filtered = filtered.filter(ticket => 
			ticket.subject.toLowerCase().includes(query) ||
			ticket.id.toString().includes(query) ||
			(ticket.team?.team_name || '').toLowerCase().includes(query)
		);
	}
	
	// Status filter
	if (activeFilter.value !== 'all') {
		filtered = filtered.filter(ticket => ticket.stage.toLowerCase() === activeFilter.value.toLowerCase());
	}
	
	return filtered;
});

const ticketStats = computed(() => {
	const data = props.tickets.data || [];
	return {
		total: data.length,
		open: data.filter(t => t.stage === 'Open').length,
		inProgress: data.filter(t => t.stage === 'In Progress').length,
		resolved: data.filter(t => ['Resolved', 'Closed'].includes(t.stage)).length,
		closed: data.filter(t => t.stage === 'Closed').length,
	};
});

const getPriorityClass = (priority) => {
	return {
		'bg-red-50 text-red-700 border-red-200': priority === 'Urgent',
		'bg-orange-50 text-orange-700 border-orange-200': priority === 'High',
		'bg-blue-50 text-blue-700 border-blue-200': priority === 'Medium',
		'bg-slate-50 text-slate-700 border-slate-200': priority === 'Low',
	};
};

const getStageClass = (stage) => {
	return {
		'bg-emerald-50 text-emerald-700 border-emerald-200': ['Resolved', 'Closed'].includes(stage),
		'bg-blue-50 text-blue-700 border-blue-200': stage === 'Open',
		'bg-amber-50 text-amber-700 border-amber-200': stage === 'In Progress',
	};
};

const getPriorityIcon = (priority) => {
	const icons = {
		'Urgent': '🔴',
		'High': '🟠',
		'Medium': '🟡',
		'Low': '⚪'
	};
	return icons[priority] || '⚪';
};

const viewTicket = (ticketId) => {
	router.visit(route('customer.tickets.show', ticketId));
};

const logout = () => {
	router.post(route('logout'));
};

</script>

<template>
	<Head :title="pageTitle" />

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
				<a href="#" @click.prevent="activeFilter = 'all'" 
					:class="activeFilter === 'all' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-slate-600 hover:bg-slate-50'"
					class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 border border-transparent">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
					</svg>
					<span class="font-medium text-sm">All Tickets</span>
					<span class="ml-auto bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full font-semibold">{{ ticketStats.total }}</span>
				</a>

				<a href="#" @click.prevent="activeFilter = 'open'"
					:class="activeFilter === 'open' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-slate-600 hover:bg-slate-50'"
					class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 border border-transparent">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<span class="font-medium text-sm">Open</span>
					<span class="ml-auto bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-semibold">{{ ticketStats.open }}</span>
				</a>

				<a href="#" @click.prevent="activeFilter = 'in progress'"
					:class="activeFilter === 'in progress' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-slate-600 hover:bg-slate-50'"
					class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 border border-transparent">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
					</svg>
					<span class="font-medium text-sm">In Progress</span>
					<span class="ml-auto bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full font-semibold">{{ ticketStats.inProgress }}</span>
				</a>

				<a href="#" @click.prevent="activeFilter = 'resolved'"
					:class="activeFilter === 'resolved' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-slate-600 hover:bg-slate-50'"
					class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 border border-transparent">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<span class="font-medium text-sm">Resolved</span>
					<span class="ml-auto bg-emerald-100 text-emerald-700 text-xs px-2 py-0.5 rounded-full font-semibold">{{ ticketStats.resolved }}</span>
				</a>

				<a href="#" @click.prevent="activeFilter = 'closed'"
					:class="activeFilter === 'closed' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'text-slate-600 hover:bg-slate-50'"
					class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 border border-transparent">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
					</svg>
					<span class="font-medium text-sm">Closed</span>
					<span class="ml-auto bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full font-semibold">{{ ticketStats.closed }}</span>
				</a>
			</nav>

			<!-- Bottom User Section -->
			<div class="p-4 border-t border-slate-200">
				<button @click="showProfileModal = true" class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 transition">
					<div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold">
						{{ $page.props.auth.user?.first_name?.[0] || 'U' }}
					</div>
					<div class="flex-1 min-w-0">
						<p class="text-sm font-medium text-slate-800 truncate">{{ $page.props.auth.user?.first_name || 'User' }}</p>
						<p class="text-xs text-slate-500 truncate">{{ $page.props.auth.user?.email || '' }}</p>
					</div>
					<svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
					</svg>
				</button>
			</div>
		</aside>

		<!-- Main Content Area -->
		<main class="flex-1 flex flex-col overflow-hidden">
			<!-- Top Header Bar -->
			<header class="bg-white border-b border-slate-200 px-8 py-6">
				<div class="flex items-center justify-between">
					<div>
						<h2 class="text-2xl font-bold text-slate-800">My Tickets</h2>
						<p class="text-sm text-slate-500 mt-1">Manage and track your support requests</p>
					</div>
					<button @click="showCreateModal = true"
						class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-lg shadow-blue-500/30 hover:shadow-xl hover:shadow-blue-500/40 font-medium">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
						</svg>
						New Ticket
					</button>
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

			<!-- Tickets List -->
			<div class="flex-1 overflow-y-auto px-8 py-6 space-y-4">
				<!-- Empty State -->
				<div v-if="filteredTickets.length === 0" class="flex flex-col items-center justify-center py-16">
					<div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
						<svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
						</svg>
					</div>
					<h3 class="text-lg font-semibold text-slate-800 mb-2">No tickets found</h3>
					<p class="text-sm text-slate-500 text-center max-w-md">
						{{ searchQuery ? 'Try adjusting your search or filter criteria' : 'Get started by creating your first support ticket' }}
					</p>
				</div>

				<!-- Ticket Cards -->
				<div v-for="ticket in filteredTickets" :key="ticket.id"
					@click="viewTicket(ticket.id)"
					class="bg-white border border-slate-200 rounded-2xl p-6 hover:shadow-xl hover:border-blue-300 transition-all duration-300 cursor-pointer group">
					<div class="flex items-start justify-between mb-4">
						<div class="flex items-start gap-4 flex-1">
							<div class="w-12 h-12 bg-gradient-to-br from-slate-100 to-slate-200 rounded-xl flex items-center justify-center text-slate-600 font-bold group-hover:from-blue-50 group-hover:to-blue-100 group-hover:text-blue-600 transition-all duration-300">
								#{{ ticket.id }}
							</div>
							<div class="flex-1 min-w-0">
								<h3 class="text-lg font-semibold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors duration-200">
									{{ ticket.subject }}
								</h3>
								<div class="flex items-center gap-3 flex-wrap">
									<span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border" :class="getStageClass(ticket.stage)">
										<span class="w-1.5 h-1.5 rounded-full bg-current"></span>
										{{ ticket.stage }}
									</span>
									<span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border" :class="getPriorityClass(ticket.priority)">
										<span>{{ getPriorityIcon(ticket.priority) }}</span>
										{{ ticket.priority }}
									</span>
								</div>

								<!-- Tags -->
								<div class="mt-3 flex flex-wrap gap-2">
									<span v-for="tag in ticket.tags || []" :key="tag.id" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border">
										{{ tag.name }}
									</span>
									<span v-if="!(ticket.tags && ticket.tags.length)" class="text-xs text-slate-500">No tags</span>
								</div>
							</div>
						</div>
						<svg class="w-6 h-6 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
						</svg>
					</div>

					<div class="flex items-center gap-6 text-sm text-slate-500">
						<div class="flex items-center gap-2">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
							</svg>
							<span class="font-medium">{{ ticket.team?.team_name || 'Unassigned' }}</span>
						</div>
						<div class="flex items-center gap-2">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
							</svg>
							<span>{{ new Date(ticket.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
						</div>
						<div v-if="ticket.deadline" class="flex items-center gap-2">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
							<span>Due: {{ ticket.deadline }}</span>
						</div>
					</div>
				</div>

				<!-- Pagination -->
				<div v-if="tickets.links.length > 3" class="pt-4">
					<Pagination :links="tickets.links" />
				</div>
			</div>
		</main>

		<!-- Create Ticket Modal -->
		<Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="2xl">
			<CreateTicket :teams="teams" :priorities="priorities" :customer_id="customer_id" :tags="tags" @close="showCreateModal = false" />
		</Modal>

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

