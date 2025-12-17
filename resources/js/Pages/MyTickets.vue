<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import { ref, watch } from 'vue';

// Custom debounce function
const debounce = (func, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
};

const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

const props = defineProps({
    tickets: {
        type: Object,
        required: true,
    },
    pageTitle: {
        type: String,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    teams: {
        type: Array,
        default: () => []
    },
    totalAll: {
        type: Number,
        default: 0
    }
});

// Restore persisted state from localStorage
const getPersistedState = (key, defaultValue) => {
    const stored = localStorage.getItem(`myTicketsFilters_${key}`);
    return stored !== null ? JSON.parse(stored) : defaultValue;
};

// Filter and search state (with persistence)
const search = ref(props.filters.search || getPersistedState('search', ''));
const stageFilter = ref(props.filters.stage || getPersistedState('stage', ''));
const priorityFilter = ref(props.filters.priority || getPersistedState('priority', ''));
const teamFilter = ref(props.filters.team || getPersistedState('team', ''));
const assignedFilter = ref(props.filters.assigned || getPersistedState('assigned', ''));
const dateFrom = ref(props.filters.date_from || getPersistedState('dateFrom', ''));
const dateTo = ref(props.filters.date_to || getPersistedState('dateTo', ''));
const deadlineFrom = ref(props.filters.deadline_from || getPersistedState('deadlineFrom', ''));
const deadlineTo = ref(props.filters.deadline_to || getPersistedState('deadlineTo', ''));
const sortField = ref(props.filters.sort_field || getPersistedState('sortField', ''));
const sortDirection = ref(props.filters.sort_direction || getPersistedState('sortDirection', 'asc'));
const showFilters = ref(getPersistedState('showFilters', true));

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
    localStorage.setItem('myTicketsFilters_showFilters', JSON.stringify(showFilters.value));
};

// Persist filter changes to localStorage
watch([search, stageFilter, priorityFilter, teamFilter, assignedFilter, dateFrom, dateTo, deadlineFrom, deadlineTo, sortField, sortDirection], () => {
    localStorage.setItem('myTicketsFilters_search', JSON.stringify(search.value));
    localStorage.setItem('myTicketsFilters_stage', JSON.stringify(stageFilter.value));
    localStorage.setItem('myTicketsFilters_priority', JSON.stringify(priorityFilter.value));
    localStorage.setItem('myTicketsFilters_team', JSON.stringify(teamFilter.value));
    localStorage.setItem('myTicketsFilters_assigned', JSON.stringify(assignedFilter.value));
    localStorage.setItem('myTicketsFilters_dateFrom', JSON.stringify(dateFrom.value));
    localStorage.setItem('myTicketsFilters_dateTo', JSON.stringify(dateTo.value));
    localStorage.setItem('myTicketsFilters_deadlineFrom', JSON.stringify(deadlineFrom.value));
    localStorage.setItem('myTicketsFilters_deadlineTo', JSON.stringify(deadlineTo.value));
    localStorage.setItem('myTicketsFilters_sortField', JSON.stringify(sortField.value));
    localStorage.setItem('myTicketsFilters_sortDirection', JSON.stringify(sortDirection.value));
});

const getPriorityClass = (priority) => {
    return {
        'bg-gradient-to-r from-red-100 to-rose-100 text-red-800 border border-red-200': priority === 'Urgent',
        'bg-gradient-to-r from-orange-100 to-amber-100 text-orange-800 border border-orange-200': priority === 'High',
        'bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-800 border border-blue-200': priority === 'Medium',
        'bg-gradient-to-r from-gray-100 to-slate-100 text-gray-800 border border-gray-200': priority === 'Low',
    };
};

const getStageClass = (stage) => {
    return {
        'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200': ['Resolved', 'Closed'].includes(stage),
        'bg-gradient-to-r from-blue-100 to-sky-100 text-blue-800 border border-blue-200': stage === 'Open',
        'bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-800 border border-yellow-200': stage === 'In Progress',
    };
};

const viewAllTicket = (ticketId) => {
    router.visit(route('mytickets.show', ticketId));
};

// Apply filters with debounce for search
const applyFilters = debounce(() => {
    router.get(route('mytickets.index'), {
        search: search.value,
        stage: stageFilter.value,
        priority: priorityFilter.value,
        team: teamFilter.value,
        assigned: assignedFilter.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        deadline_from: deadlineFrom.value,
        deadline_to: deadlineTo.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

// Watch for filter changes
watch([search, stageFilter, priorityFilter, teamFilter, assignedFilter, dateFrom, dateTo, deadlineFrom, deadlineTo], () => {
    applyFilters();
});

// Sort handler
const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    applyFilters();
};

// Clear all filters
const clearFilters = () => {
    search.value = '';
    stageFilter.value = '';
    priorityFilter.value = '';
    teamFilter.value = '';
    assignedFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    deadlineFrom.value = '';
    deadlineTo.value = '';
    sortField.value = '';
    sortDirection.value = 'asc';
    applyFilters();
};

// Apply quick date filters (today, week, month)
const applyQuickFilter = (value) => {
    if (!value) return;
    const today = new Date();
    if (value === 'today') {
        const d = today.toISOString().split('T')[0];
        dateFrom.value = d;
        dateTo.value = d;
    } else if (value === 'week') {
        const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
        dateFrom.value = weekAgo.toISOString().split('T')[0];
        dateTo.value = today.toISOString().split('T')[0];
    } else if (value === 'month') {
        const monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
        dateFrom.value = monthAgo.toISOString().split('T')[0];
        dateTo.value = today.toISOString().split('T')[0];
    }
    applyFilters();
};

// Bulk actions
const selectedTickets = ref([]);
const selectAll = ref(false);

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedTickets.value = props.tickets.data.map(t => t.id);
    } else {
        selectedTickets.value = [];
    }
};

watch(() => props.tickets.data, () => {
    // Clear selection if tickets change
    selectedTickets.value = selectedTickets.value.filter(id => 
        props.tickets.data.some(t => t.id === id)
    );
    selectAll.value = selectedTickets.value.length === props.tickets.data.length && props.tickets.data.length > 0;
});

watch(selectedTickets, () => {
    selectAll.value = selectedTickets.value.length === props.tickets.data.length && props.tickets.data.length > 0;
}, { deep: true });

const bulkAction = ref('');
const bulkStage = ref('');
const bulkPriority = ref('');
const bulkAssignTo = ref('');
const showBulkModal = ref(false);

const openBulkAction = (action) => {
    if (selectedTickets.value.length === 0) {
        alert('Please select at least one ticket.');
        return;
    }
    bulkAction.value = action;
    showBulkModal.value = true;
};

const executeBulkAction = () => {
    if (bulkAction.value === 'delete') {
        if (!confirm(`Are you sure you want to delete ${selectedTickets.value.length} ticket(s)?`)) {
            return;
        }
        router.post(route('mytickets.bulk-delete'), {
            ticket_ids: selectedTickets.value
        }, {
            onSuccess: () => {
                selectedTickets.value = [];
                showBulkModal.value = false;
            }
        });
    } else if (bulkAction.value === 'update') {
        const updates = {};
        if (bulkStage.value) updates.stage = bulkStage.value;
        if (bulkPriority.value) updates.priority = bulkPriority.value;
        if (bulkAssignTo.value) updates.assigned_to_employee_id = bulkAssignTo.value;
        
        if (Object.keys(updates).length === 0) {
            alert('Please select at least one field to update.');
            return;
        }
        
        router.post(route('mytickets.bulk-update'), {
            ticket_ids: selectedTickets.value,
            updates: updates
        }, {
            onSuccess: () => {
                selectedTickets.value = [];
                showBulkModal.value = false;
                bulkStage.value = '';
                bulkPriority.value = '';
                bulkAssignTo.value = '';
            }
        });
    }
};

const closeBulkModal = () => {
    showBulkModal.value = false;
    bulkAction.value = '';
    bulkStage.value = '';
    bulkPriority.value = '';
    bulkAssignTo.value = '';
};

</script>

<template>

    <Head :title="pageTitle" />

    <AuthenticatedLayout>
        <div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-8">
            <!-- Header Section -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-3 rounded-xl mr-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-3xl text-white">{{ pageTitle }}</h2>
                                <p class="text-blue-100 text-sm mt-1">Manage and track your assigned tickets</p>
                            </div>
                        </div>
                        <Link v-if="userPermissions && userPermissions.includes('create_mytickets')" :href="route('mytickets.create')" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Ticket
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Filters Section Toggle + Content -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                <div class="flex justify-end mb-2">
                    <button @click="toggleFilters" class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition-colors duration-200">
                        <svg v-if="showFilters" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/>
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h12"/>
                        </svg>
                        <span>{{ showFilters ? 'Hide Filters' : 'Show Filters' }}</span>
                    </button>
                </div>
                <div v-show="showFilters" class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filters & Search
                        </h3>
                        <button @click="clearFilters" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Clear All
                        </button>
                    </div>

                    <!-- Search Bar -->
                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input v-model="search" type="text" placeholder="Search by subject, customer name, employee name or ticket ID..." class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-200">
                        </div>
                    </div>

                    <!-- Filter Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Stage Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Stage</label>
                            <select v-model="stageFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                                <option value="">All Stages</option>
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>

                        <!-- Priority Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Priority</label>
                            <select v-model="priorityFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                                <option value="">All Priorities</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>

                        <!-- Team Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Team</label>
                                <select v-model="teamFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                                    <option value="">All Teams</option>
                                    <option v-for="t in teams" :key="t.id" :value="t.id">{{ t.team_name }}</option>
                                </select>
                        </div>

                        <!-- Assigned Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Assigned To</label>
                            <select v-model="assignedFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                                <option value="">All</option>
                                <option value="me">Assigned to Me</option>
                                <option value="unassigned">Unassigned</option>
                            </select>
                        </div>

                        <!-- Quick Date Filter -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Quick Filter</label>
                            <select @change="applyQuickFilter($event.target.value)" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                                <option value="">Select Period</option>
                                <option value="today">Today</option>
                                <option value="week">Last 7 Days</option>
                                <option value="month">Last 30 Days</option>
                            </select>
                        </div>
                    </div>

                    <!-- Date Range Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <!-- Created Date Range -->
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <h4 class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">Created Date Range</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">From</label>
                                    <input v-model="dateFrom" type="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">To</label>
                                    <input v-model="dateTo" type="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Deadline Date Range -->
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <h4 class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">Deadline Date Range</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">From</label>
                                    <input v-model="deadlineFrom" type="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">To</label>
                                    <input v-model="deadlineTo" type="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-400">
                    <div class="p-6 text-gray-900">
                        <div class="mb-5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-indigo-200">
                                    Showing: {{ tickets.from || (tickets.data && tickets.data.length ? 1 : 0) }}–{{ tickets.to || (tickets.data && tickets.data.length ? tickets.data.length : 0) }} of {{ tickets.total }}
                                </span>
                                <span class="text-sm text-gray-500"> (All: {{ totalAll }})</span>
                            </div>
                            <div v-if="selectedTickets.length > 0" class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">{{ selectedTickets.length }} selected</span>
                                <button @click="openBulkAction('update')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                                    Bulk Update
                                </button>
                                <button @click="openBulkAction('delete')" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">
                                    Bulk Delete
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-gray-300 to-gray-200">
                                    <tr>
                                        <th scope="col" class="px-4 py-4 text-left">
                                            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </th>
                                        <th @click="sortBy('id')" scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider cursor-pointer hover:bg-gray-300 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                    </svg>
                                                    ID
                                                </div>
                                                <svg v-if="sortField === 'id'" class="w-4 h-4 text-indigo-600" :class="{'rotate-180': sortDirection === 'desc'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            </div>
                                        </th>
                                        <th @click="sortBy('subject')" scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider cursor-pointer hover:bg-gray-300 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    Subject
                                                </div>
                                                <svg v-if="sortField === 'subject'" class="w-4 h-4 text-indigo-600" :class="{'rotate-180': sortDirection === 'desc'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                Customer
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                                </svg>
                                                Assigned To
                                            </div>
                                        </th>
                                        <th @click="sortBy('stage')" scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider cursor-pointer hover:bg-gray-300 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                    </svg>
                                                    Stage
                                                </div>
                                                <svg v-if="sortField === 'stage'" class="w-4 h-4 text-indigo-600" :class="{'rotate-180': sortDirection === 'desc'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            </div>
                                        </th>
                                        <th @click="sortBy('priority')" scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider cursor-pointer hover:bg-gray-300 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    Priority
                                                </div>
                                                <svg v-if="sortField === 'priority'" class="w-4 h-4 text-indigo-600" :class="{'rotate-180': sortDirection === 'desc'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                Team
                                            </div>
                                        </th>
                                        <th @click="sortBy('created_at')" scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider cursor-pointer hover:bg-gray-300 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    Created
                                                </div>
                                                <svg v-if="sortField === 'created_at'" class="w-4 h-4 text-indigo-600" :class="{'rotate-180': sortDirection === 'desc'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            </div>
                                        </th>
                                        <th @click="sortBy('deadline')" scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-900 uppercase tracking-wider cursor-pointer hover:bg-gray-300 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    Deadline
                                                </div>
                                                <svg v-if="sortField === 'deadline'" class="w-4 h-4 text-indigo-600" :class="{'rotate-180': sortDirection === 'desc'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-300">
                                    <tr v-if="tickets.data.length === 0">
                                        <td colspan="10" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 p-6 rounded-full mb-4">
                                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                    </svg>
                                                </div>
                                                <p class="text-lg font-semibold text-gray-600 mb-2">No Tickets Found</p>
                                                <p class="text-sm text-gray-500">There are no tickets to display.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-for="ticket in tickets.data" :key="ticket.id" class="group hover:bg-transparent transition-all duration-200">
                                        <td class="px-4 py-4 whitespace-nowrap" @click.stop>
                                            <input type="checkbox" :value="ticket.id" v-model="selectedTickets" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </td>
                                        <td @click="viewAllTicket(ticket.id)" class="px-6 py-4 whitespace-nowrap border-l-4 border-transparent group-hover:border-indigo-500 group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50 cursor-pointer">
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-indigo-100 to-blue-100 text-indigo-700 border border-indigo-200">
                                                    #{{ ticket.id }}
                                                </span>
                                            </div>
                                        </td>
                                        <td @click="viewAllTicket(ticket.id)" class="px-6 py-4 group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50 cursor-pointer">
                                            <div class="flex items-center">
                                                <div class="bg-gradient-to-br from-purple-100 to-indigo-100 p-2 rounded-lg mr-3">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </div>
                                                <div class="text-sm font-semibold text-gray-900">{{ ticket.subject }}</div>
                                            </div>
                                        </td>
                                        <td @click="viewAllTicket(ticket.id)" class="px-6 py-4 whitespace-nowrap group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50 cursor-pointer">
                                            <div class="flex items-center">
                                                <div class="bg-gradient-to-br from-blue-100 to-cyan-100 p-2 rounded-full mr-2">
                                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                </div>
                                                <span class="text-sm text-gray-700 font-medium">
                                                    {{ ticket.customer ? `${ticket.customer.first_name} ${ticket.customer.last_name}` : 'N/A' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td @click="viewAllTicket(ticket.id)" class="px-6 py-4 whitespace-nowrap group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50 cursor-pointer">
                                            <div class="flex items-center">
                                                <div v-if="ticket.assigned_employee_is_current_user" class="bg-gradient-to-br from-green-100 to-emerald-100 px-3 py-1 rounded-full flex items-center">
                                                    <svg class="w-3 h-3 text-green-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    <span class="text-xs font-semibold text-green-700">You</span>
                                                </div>
                                                <span v-else class="text-sm text-gray-700 font-medium">
                                                    {{ ticket.assigned_employee_name ? ticket.assigned_employee_name : 'Unassigned' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td @click="viewAllTicket(ticket.id)" class="px-6 py-4 whitespace-nowrap group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50 cursor-pointer">
                                            <span class="px-3 py-1.5 inline-flex items-center text-xs leading-5 font-bold rounded-xl shadow-sm" :class="getStageClass(ticket.stage)">
                                                <svg v-if="['Resolved', 'Closed'].includes(ticket.stage)" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <svg v-else-if="ticket.stage === 'In Progress'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <svg v-else class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                {{ ticket.stage }}
                                            </span>
                                        </td>
                                        <td @click="viewAllTicket(ticket.id)" class="px-6 py-4 whitespace-nowrap group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50 cursor-pointer">
                                            <span class="px-3 py-1.5 inline-flex items-center text-xs leading-5 font-bold rounded-xl shadow-sm" :class="getPriorityClass(ticket.priority)">
                                                <svg v-if="ticket.priority === 'Urgent'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                <svg v-else-if="ticket.priority === 'High'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                                </svg>
                                                <svg v-else class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                </svg>
                                                {{ ticket.priority }}
                                            </span>
                                        </td>
                                        <td @click="viewAllTicket(ticket.id)" class="px-6 py-4 whitespace-nowrap group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50 cursor-pointer">
                                            <div class="flex items-center">
                                                
                                                <span class="text-sm text-gray-700 font-medium">{{ ticket.team ? ticket.team.team_name : 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td @click="viewAllTicket(ticket.id)" class="px-6 py-4 whitespace-nowrap group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50 cursor-pointer">
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ new Date(ticket.created_at).toLocaleDateString() }}
                                            </div>
                                        </td>
                                        <td @click="viewAllTicket(ticket.id)" class="px-6 py-4 whitespace-nowrap group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50 cursor-pointer">
                                            <div class="flex items-center">
                                                <div class="bg-gradient-to-br from-amber-100 to-yellow-100 px-3 py-1 rounded-lg flex items-center">
                                                    <svg class="w-3 h-3 text-amber-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span class="text-xs font-semibold text-amber-700">{{ new Date(ticket.deadline).toLocaleDateString() }}</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="tickets.links.length > 3" class="mt-6">
                           <Pagination :links="tickets.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bulk Action Modal -->
        <div v-if="showBulkModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="closeBulkModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        {{ bulkAction === 'delete' ? 'Bulk Delete Tickets' : 'Bulk Update Tickets' }}
                    </h3>
                    
                    <div v-if="bulkAction === 'update'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stage</label>
                            <select v-model="bulkStage" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">— No Change —</option>
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select v-model="bulkPriority" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">— No Change —</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                            <select v-model="bulkAssignTo" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">— No Change —</option>
                                <option value="null">Unassigned</option>
                            </select>
                        </div>
                    </div>
                    
                    <div v-else class="text-sm text-gray-600">
                        Are you sure you want to delete {{ selectedTickets.length }} ticket(s)? This action cannot be undone.
                    </div>
                    
                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button @click="closeBulkModal" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium text-sm transition-colors">
                            Cancel
                        </button>
                        <button @click="executeBulkAction" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm transition-colors">
                            {{ bulkAction === 'delete' ? 'Delete' : 'Update' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>