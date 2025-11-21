<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        required: true
    },
    recentActivities: {
        type: Array,
        required: true
    },
    teams: {
        type: Array,
        required: true
    },
    userStats: {
        type: Object,
        default: () => ({
            highPriorityTickets: 0,
            urgentTickets: 0,
            failedTickets: 0,
            closedTickets: 0,
            successRate: 0,
            averageRating: 0,
            weeklyAvg: {
            closed: 0,
            success: 0,
            rating: 0
            }
        })
    },
    isAdmin: {
        type: Boolean,
        default: false
    }
});

const getStageClass = (stage) => {
    return {
        'px-2 py-1 text-xs font-medium rounded-full': true,
        'bg-green-100 text-green-800': stage === 'Resolved',
        'bg-blue-100 text-blue-800': stage === 'Open',
        'bg-yellow-100 text-yellow-800': stage === 'In Progress'
    };
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- 2. Personalized Statistics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- My Tickets Card -->
                    <div class="bg-white rounded-lg shadow p-6" role="region" aria-labelledby="tickets-heading">
                        <div class="flex justify-between items-center mb-4">
                            <h3 id="tickets-heading" class="text-lg font-semibold">
                                {{ isAdmin ? 'All System Tickets' : 'My Assigned Tickets' }}
                            </h3>
                            <span v-if="isAdmin" class="text-sm text-blue-600 bg-blue-100 px-2 py-1 rounded">
                                Viewing as Admin
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-500" id="high-priority-label">High Priority</p>
                                <p class="text-2xl font-bold" aria-labelledby="high-priority-label">
                                    {{ userStats.highPriorityTickets }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500" id="urgent-label">Urgent</p>
                                <p class="text-2xl font-bold" aria-labelledby="urgent-label">
                                    {{ userStats.urgentTickets }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500" id="failed-label">Failed</p>
                                <p class="text-2xl font-bold" aria-labelledby="failed-label">
                                    {{ userStats.failedTickets }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- My Performance Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-4">My Performance Today</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Closed Tickets</p>
                                <p class="text-2xl font-bold">{{ userStats.closedTickets }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Success Rate</p>
                                <p class="text-2xl font-bold">{{ userStats.successRate }}%</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Average Rating</p>
                                <p class="text-2xl font-bold">{{ userStats.averageRating }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-sm text-gray-500 mb-2">Average Last 7 days</p>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-sm">
                                    <p class="text-gray-500">Closed</p>
                                    <p class="font-semibold">{{ userStats.weeklyAvg.closed }}</p>
                                </div>
                                <div class="text-sm">
                                    <p class="text-gray-500">Success</p>
                                    <p class="font-semibold">{{ userStats.weeklyAvg.success }}%</p>
                                </div>
                                <div class="text-sm">
                                    <p class="text-gray-500">Rating</p>
                                    <p class="font-semibold">{{ userStats.weeklyAvg.rating }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Team/Department Queues All Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="team in teams" 
                         :key="team.name" 
                         class="bg-white rounded-lg shadow p-6" 
                         role="region" 
                         :aria-label="`${team.name} Team Statistics`">
                        <h3 class="text-lg font-semibold mb-4">{{ team.name }}</h3>
                        
                        <!-- High-level Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div>
                                <p class="text-sm text-gray-500">Tickets Closed</p>
                                <p class="text-xl font-bold">{{ team.stats.closed }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Success Rate</p>
                                <p class="text-xl font-bold">{{ team.stats.success }}%</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Average Rating</p>
                                <p class="text-xl font-bold">{{ team.stats.rating }}</p>
                            </div>
                        </div>

                        <!-- Queue Status -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-sm text-gray-500">Open</p>
                                <p class="font-semibold">{{ team.queue.open }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Unassigned</p>
                                <p class="font-semibold">{{ team.queue.unassigned }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Urgent</p>
                                <p class="font-semibold">{{ team.queue.urgent }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Failed</p>
                                <p class="font-semibold">{{ team.queue.failed }}</p>
                            </div>
                        </div>

                        <!-- View Tickets Button (hidden if user lacks access) -->
                        <div v-if="team.canView">
                            <Link 
                                :href="route('teamtickets.index', { team: team.id })" 
                                class="w-full bg-blue-500 text-white rounded-md py-2 hover:bg-blue-600 transition-colors text-center block"
                            >
                                View Tickets
                            </Link>
                        </div>
                        <div v-else>
                            <button disabled class="w-full bg-gray-200 text-gray-500 rounded-md py-2 text-center block">
                                View Tickets
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 4. Recent Activity (preserved from original) -->
                <div class="mt-6 bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-for="activity in recentActivities" :key="activity.id" 
                             class="px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ activity.subject }}</p>
                                    <p class="text-sm text-gray-500">{{ activity.time }}</p>
                                </div>
                                <span :class="{
                                    'px-2 py-1 text-xs font-medium rounded-full': true,
                                    'bg-green-100 text-green-800': activity.stage === 'Resolved',
                                    'bg-blue-100 text-blue-800': activity.stage === 'Open',
                                    'bg-yellow-100 text-yellow-800': activity.stage === 'In Progress'
                                }">
                                    {{ activity.stage }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>