<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true
    },
    priorityStats: {
        type: Object,
        required: true
    },
    stageDistribution: {
        type: Array,
        required: true
    },
    monthlyTrends: {
        type: Array,
        required: true
    },
    teams: {
        type: Array,
        required: true
    },
    customers: {
        type: Array,
        required: false,
        default: () => []
    },
    avgResponseTime: {
        type: String,
        required: true
    },
    recentTickets: {
        type: Array,
        required: true
    },
    slaCompliance: {
        type: Number,
        required: true
    },
    avgCustomerRating: {
        type: Number,
        required: true
    },
    ratingDistribution: {
        type: Array,
        required: true
    }
    ,
    employees: {
        type: Array,
        required: false,
        default: () => []
    }
});

const selectedTeam = ref('all');
const selectedPriority = ref('all');
const selectedStage = ref('all');
const selectedEmployee = ref('all');
const selectedCustomer = ref('all');
const selectedStartDate = ref('');
const selectedEndDate = ref('');
const isExporting = ref(false);
const showFilters = ref(true);
const createdFrom = ref('');
const createdTo = ref('');
const deadlineFrom = ref('');
const deadlineTo = ref('');

const toggleFilters = () => {
    showFilters.value = !showFilters.value;
};

// Clear all export/filter inputs back to defaults
const clearFilters = () => {
    selectedTeam.value = 'all';
    selectedPriority.value = 'all';
    selectedStage.value = 'all';
    selectedEmployee.value = 'all';
    selectedCustomer.value = 'all';
    selectedStartDate.value = '';
    selectedEndDate.value = '';
    createdFrom.value = '';
    createdTo.value = '';
    deadlineFrom.value = '';
    deadlineTo.value = '';
};
// Calculate max value for chart scaling
const maxMonthlyValue = computed(() => {
    const values = props.monthlyTrends.flatMap(t => [t.created, t.resolved]);
    return Math.max(...values, 10);
});

// Check if there's any data
const hasData = computed(() => {
    return props.stats.total > 0;
});

// Export (server-side) as .xlsx using current filters
const exportXLSX = () => {
    isExporting.value = true;

    try {
        const params = new URLSearchParams();
        if (selectedTeam.value && selectedTeam.value !== 'all') params.append('team', selectedTeam.value);
        if (selectedPriority.value && selectedPriority.value !== 'all') params.append('priority', selectedPriority.value);
        if (selectedStage.value && selectedStage.value !== 'all') params.append('stage', selectedStage.value);
        if (selectedStartDate.value) params.append('date_from', selectedStartDate.value);
        if (selectedEndDate.value) params.append('date_to', selectedEndDate.value);
        if (selectedEmployee.value && selectedEmployee.value !== 'all') {
            // send as 'assigned' so export handles employee id or special values
            params.append('assigned', selectedEmployee.value);
        }
        if (selectedCustomer.value && selectedCustomer.value !== 'all') params.append('customer_id', selectedCustomer.value);
        if (createdFrom.value) params.append('created_from', createdFrom.value);
        if (createdTo.value) params.append('created_to', createdTo.value);
        if (deadlineFrom.value) params.append('deadline_from', deadlineFrom.value);
        if (deadlineTo.value) params.append('deadline_to', deadlineTo.value);

        // navigate to export route to trigger .xlsx download (no `search` param included)
        const url = `/ticket-analysis/export?${params.toString()}`;
        window.location.href = url;
    } catch (error) {
        console.error('Export failed:', error);
        alert('Failed to export data. Please try again.');
    } finally {
        isExporting.value = false;
    }
};

const getPriorityColor = (priority) => {
    const colors = {
        'Low': 'bg-blue-100 text-blue-700',
        'Normal': 'bg-green-100 text-green-700',
        'High': 'bg-orange-100 text-orange-700',
        'Urgent': 'bg-red-100 text-red-700'
    };
    return colors[priority] || 'bg-gray-100 text-gray-700';
};

const getStageColor = (stage) => {
    const colors = {
        'Open': 'bg-blue-500',
        'In Progress': 'bg-yellow-500',
        'Resolved': 'bg-green-500',
        'Closed': 'bg-gray-500'
    };
    return colors[stage] || 'bg-gray-500';
};

const getStagePercentage = (count) => {
    return props.stats.total > 0 ? ((count / props.stats.total) * 100).toFixed(1) : 0;
};

const formatDatetime = (value) => {
    if (!value) return '—';
    try {
        let s = String(value).trim();

        // If provided as 'YYYY-MM-DD HH:mm' add seconds
        if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(s)) {
            s = s + ':00';
        }

        // Normalize space to 'T' for Date parsing when needed
        if (!s.includes('T') && s.includes(' ')) {
            s = s.replace(' ', 'T');
        }

        const d = new Date(s);
        if (isNaN(d.getTime())) return value;

        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        const yyyy = d.getFullYear();

        let hours = d.getHours();
        const minutes = String(d.getMinutes()).padStart(2, '0');
        const seconds = String(d.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 -> 12

        return `${mm}/${dd}/${yyyy}, ${hours}:${minutes}:${seconds} ${ampm}`;
    } catch (e) {
        return value;
    }
};

const filteredTeams = computed(() => {
    if (selectedTeam.value === 'all') {
        return props.teams;
    }
    return props.teams.filter(team => team.id === parseInt(selectedTeam.value));
});

// Calculate additional metrics
const resolutionRate = computed(() => {
    return props.stats.total > 0 
        ? ((props.stats.resolved / props.stats.total) * 100).toFixed(1) 
        : 0;
});

const activeTicketsPercentage = computed(() => {
    const active = props.stats.open + props.stats.inProgress;
    return props.stats.total > 0 
        ? ((active / props.stats.total) * 100).toFixed(1) 
        : 0;
});
</script>

<template>
    <Head title="Ticket Analysis" />

    <AuthenticatedLayout>
        <div class="py-8 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
            <div class="mx-auto max-w-screen-2xl sm:px-6 lg:px-8">

                <!-- Header Banner -->
                <div class="mb-8">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="bg-white/20 p-3 rounded-xl mr-4">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="font-bold text-3xl text-white">Ticket Analysis</h2>
                                    <p class="text-blue-100 text-sm mt-1">Comprehensive insights and performance metrics</p>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center space-x-3">
                                <button @click="exportXLSX" :disabled="isExporting || !hasData" 
                                        class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-lg font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="!isExporting" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ isExporting ? 'Exporting...' : 'Export .xlsx' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!hasData" class="bg-white rounded-2xl shadow-xl p-12 text-center border border-gray-100">
                    <div class="flex justify-center mb-4">
                        <div class="bg-gray-100 p-6 rounded-full">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">No Ticket Data Available</h3>
                    <p class="text-gray-600 mb-6">There are no tickets to analyze yet. Start by creating some tickets or check your permissions.</p>
                    <Link :href="route('dashboard')" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold text-sm shadow-lg hover:bg-indigo-700 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Dashboard
                    </Link>
                </div>
                <!-- Export Filters Toggle + Content -->
                <div v-if="hasData" class="mb-6">
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
                                Export Filters
                            </h3>
                            <button @click="clearFilters" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Clear All
                            </button>
                        </div>

                        <!-- Filter Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <!-- Team Filter -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Team</label>
                                <select v-model="selectedTeam" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                                    <option value="all">All Teams</option>
                                    <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                                </select>
                            </div>

                            <!-- Employee Filter -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Employee</label>
                                <select v-model="selectedEmployee" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                                    <option value="all">All Employees</option>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                                </select>
                            </div>

                            <!-- Customer Filter -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Customer</label>
                                <select v-model="selectedCustomer" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                                    <option value="all">All Customers</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
                                </select>
                            </div>

                            <!-- Priority Filter -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Priority</label>
                                <select v-model="selectedPriority" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                                    <option value="all">All Priorities</option>
                                    <option value="Urgent">Urgent</option>
                                    <option value="High">High</option>
                                    <option value="Normal">Normal</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>

                            <!-- Stage Filter -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Stage</label>
                                <select v-model="selectedStage" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all duration-200">
                                    <option value="all">All Stages</option>
                                    <option value="Open">Open</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Resolved">Resolved</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Date Range Filters -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 mt-4">
                            <!-- (Ticket Date Range removed) -->

                            <!-- Created Date Range -->
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <h4 class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">Created Date Range</h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">From</label>
                                        <input v-model="createdFrom" type="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">To</label>
                                        <input v-model="createdTo" type="date" class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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

                <!-- Quick Insights -->
                <div v-if="hasData" class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        Key Insights
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                            <div class="flex-shrink-0 bg-blue-500 p-3 rounded-lg mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-600">Resolution Rate</p>
                                <p class="text-2xl font-bold text-blue-700">{{ resolutionRate }}%</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-100">
                            <div class="flex-shrink-0 bg-yellow-500 p-3 rounded-lg mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-600">Active Tickets</p>
                                <p class="text-2xl font-bold text-yellow-700">{{ activeTicketsPercentage }}%</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-100">
                            <div class="flex-shrink-0 bg-green-500 p-3 rounded-lg mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-600">SLA Compliance</p>
                                <p class="text-2xl font-bold text-green-700">{{ slaCompliance }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Statistics Cards -->
                <div v-if="hasData" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                    <!-- Total Tickets -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Tickets</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total }}</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Open Tickets -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Open</p>
                                <p class="text-3xl font-bold text-blue-600 mt-2">{{ stats.open }}</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- In Progress -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">In Progress</p>
                                <p class="text-3xl font-bold text-yellow-600 mt-2">{{ stats.inProgress }}</p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Resolved -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Resolved</p>
                                <p class="text-3xl font-bold text-green-600 mt-2">{{ stats.resolved }}</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Closed -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Closed</p>
                                <p class="text-3xl font-bold text-gray-600 mt-2">{{ stats.closed }}</p>
                            </div>
                            <div class="bg-gray-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div v-if="hasData" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    
                    <!-- Monthly Trends Chart -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-800">Monthly Trends</h3>
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded mr-2"></div>
                                    <span class="text-xs text-gray-600">Created</span>
                                </div>
                                <div class="flex items-center ml-4">
                                    <div class="w-3 h-3 bg-green-500 rounded mr-2"></div>
                                    <span class="text-xs text-gray-600">Resolved</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="h-64 flex items-end justify-between space-x-2">
                            <div v-for="trend in monthlyTrends" :key="trend.month" class="flex-1 flex flex-col items-center">
                                <div class="w-full flex items-end justify-center space-x-1 h-48">
                                    <!-- Created Bar -->
                                    <div class="flex-1 bg-blue-500 rounded-t hover:bg-blue-600 transition-colors relative group"
                                         :style="{ height: `${(trend.created / maxMonthlyValue) * 100}%` }">
                                        <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">
                                            Created: {{ trend.created }}
                                        </div>
                                    </div>
                                    <!-- Resolved Bar -->
                                    <div class="flex-1 bg-green-500 rounded-t hover:bg-green-600 transition-colors relative group"
                                         :style="{ height: `${(trend.resolved / maxMonthlyValue) * 100}%` }">
                                        <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">
                                            Resolved: {{ trend.resolved }}
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-600 mt-2 text-center">{{ trend.month }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stage Distribution -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">Ticket Stage Distribution</h3>
                        
                        <div class="space-y-4">
                            <div v-for="stage in stageDistribution" :key="stage.stage" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">{{ stage.stage }}</span>
                                    <span class="text-sm font-bold text-gray-900">{{ stage.count }} ({{ getStagePercentage(stage.count) }}%)</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div :class="getStageColor(stage.stage)" 
                                         class="h-3 rounded-full transition-all duration-500"
                                         :style="{ width: `${getStagePercentage(stage.count)}%` }">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">SLA Compliance</p>
                                    <p class="text-2xl font-bold text-green-600">{{ slaCompliance }}%</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Avg Response</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ avgResponseTime }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Priority Breakdown & Customer Satisfaction -->
                <div v-if="hasData" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    
                    <!-- Priority Breakdown -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">Priority Breakdown</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-red-600">Urgent</p>
                                        <p class="text-2xl font-bold text-red-700 mt-1">{{ priorityStats.urgent }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="bg-orange-50 rounded-lg p-4 border border-orange-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-orange-600">High</p>
                                        <p class="text-2xl font-bold text-orange-700 mt-1">{{ priorityStats.high }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-green-600">Normal</p>
                                        <p class="text-2xl font-bold text-green-700 mt-1">{{ priorityStats.normal }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-blue-600">Low</p>
                                        <p class="text-2xl font-bold text-blue-700 mt-1">{{ priorityStats.low }}</p>
                                    </div>
                                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Satisfaction -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">Customer Satisfaction</h3>
                        
                        <div class="text-center mb-6">
                            <p class="text-sm text-gray-500 mb-2">Average Rating</p>
                            <div class="flex items-center justify-center space-x-2">
                                <p class="text-5xl font-bold text-yellow-500">{{ avgCustomerRating }}</p>
                                <svg class="w-12 h-12 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">out of 5 stars</p>
                        </div>

                        <div class="space-y-3">
                            <div v-for="rating in [5, 4, 3, 2, 1]" :key="rating" class="flex items-center space-x-3">
                                <span class="text-sm font-medium text-gray-700 w-8">{{ rating }}★</span>
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-400 h-2 rounded-full transition-all duration-500"
                                         :style="{ width: `${(ratingDistribution.find(r => r.rating === rating)?.count || 0) / Math.max(...ratingDistribution.map(r => r.count), 1) * 100}%` }">
                                    </div>
                                </div>
                                <span class="text-sm text-gray-600 w-12 text-right">{{ ratingDistribution.find(r => r.rating === rating)?.count || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Performance -->
                <div v-if="hasData && teams.length > 0" class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Team Performance</h3>
                        <select v-model="selectedTeam" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">All Teams</option>
                            <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tickets</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolved</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Rating</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="team in filteredTeams" :key="team.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-2 rounded-lg mr-3">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ team.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-700">{{ team.totalTickets }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-700">{{ team.resolved }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-green-500 h-2 rounded-full" :style="{ width: `${team.successRate}%` }"></div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700">{{ team.successRate }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700">{{ team.avgRating }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Tickets -->
                <div v-if="hasData && recentTickets.length > 0" class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">Recent Tickets</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stage</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="ticket in recentTickets" :key="ticket.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ ticket.id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">{{ ticket.subject }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ ticket.customer }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ ticket.team }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ ticket.assigned_to || '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getPriorityColor(ticket.priority)" class="px-3 py-1 text-xs font-semibold rounded-full">
                                            {{ ticket.priority }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full"
                                              :class="{
                                                  'bg-blue-100 text-blue-700': ticket.stage === 'Open',
                                                  'bg-yellow-100 text-yellow-700': ticket.stage === 'In Progress',
                                                  'bg-green-100 text-green-700': ticket.stage === 'Resolved',
                                                  'bg-gray-100 text-gray-700': ticket.stage === 'Closed'
                                              }">
                                            {{ ticket.stage }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDatetime(ticket.deadline) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDatetime(ticket.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    /* Hide non-essential elements during print */
    button,
    select,
    .no-print {
        display: none !important;
    }
    
    /* Optimize layout for printing */
    .bg-gradient-to-br,
    .bg-gradient-to-r {
        background: white !important;
        color: black !important;
    }
    
    .shadow-lg,
    .shadow-xl {
        box-shadow: none !important;
    }
    
    .rounded-xl,
    .rounded-2xl {
        border: 1px solid #e5e7eb;
    }
    
    /* Page breaks */
    .page-break {
        page-break-after: always;
    }
    
    /* Ensure charts and tables fit on page */
    table {
        page-break-inside: avoid;
    }
    
    .grid {
        display: block !important;
    }
    
    .grid > div {
        margin-bottom: 1rem;
        page-break-inside: avoid;
    }
}
</style>
