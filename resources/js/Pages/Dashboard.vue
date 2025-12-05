<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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
            AllhighPriorityTickets: 0,
            AllurgentTickets: 0,
            AllfailedTickets: 0,
            AllClosedTickets: 0,
            TodayclosedTickets: 0,
            TodaysuccessRate: 0,
            TodayaverageRating: 0,
            weeklyAvg: {
            closed: 0,
            success: 0,
            rating: 0
            }
        })
    },
    globalStats: {
        type: Object,
        default: () => ({
            AllhighPriorityTickets: 0,
            AllurgentTickets: 0,
            AllfailedTickets: 0,
        }),
    },

});

const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];
const authUser = page.props.auth && page.props.auth.user ? page.props.auth.user : null;

const visibleTeams = computed(() => {
    const allTeams = props.teams || [];
    
    // If user is an admin or has the global permission, show all teams
    if ((userPermissions && userPermissions.includes('can_view_other_teams_tickets'))) {
        return allTeams;
    }

    // Otherwise show only teams the user belongs to
    let userTeamIds = [];

    if (authUser && Array.isArray(authUser.teams)) {
        userTeamIds = authUser.teams.map(t => t.id);
    }

    if (!userTeamIds.length) {
        return [];
    }

    return allTeams.filter(team => userTeamIds.includes(team.id));
});

// Determine if the current user can view all tickets
const hasPermission = computed(() => {
    const perms = userPermissions || [];
    return (
        perms.includes('view_alltickets_menu') ||
        perms.includes('show_alltickets')
    );
});

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- 2. Personalized Statistics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- All Tickets count -->
                    <div class="bg-white rounded-lg shadow p-6" role="region" aria-labelledby="tickets-heading">
                        <div class="flex justify-between items-center mb-4">
                            <h3 id="tickets-heading" class="text-lg font-semibold">
                                {{ hasPermission ? 'All System Tickets' : 'My Assigned Tickets' }}
                                <span class="text-sm text-gray-500 font-normal ml-2">
                                    {{ hasPermission ? '(Includes all tickets across teams)' : '(Tickets assigned to you)' }}
                                </span>
                            </h3>

                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <template v-if="hasPermission">
                                <div>
                                    <p class="text-sm text-gray-500" id="high-priority-label">High Priority</p>
                                    <p class="text-2xl font-bold" aria-labelledby="high-priority-label">
                                        {{ globalStats.AllhighPriorityTickets }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500" id="urgent-label">Urgent</p>
                                    <p class="text-2xl font-bold" aria-labelledby="urgent-label">
                                        {{ globalStats.AllurgentTickets }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500" id="failed-label">Failed</p>
                                    <p class="text-2xl font-bold" aria-labelledby="failed-label">
                                        {{ globalStats.AllfailedTickets }}
                                    </p>
                                </div>
                            </template>
                            <template v-else>
                                <div>
                                    <p class="text-sm text-gray-500" id="high-priority-label">High Priority</p>
                                    <p class="text-2xl font-bold" aria-labelledby="high-priority-label">
                                        {{ userStats.AllhighPriorityTickets }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500" id="urgent-label">Urgent</p>
                                    <p class="text-2xl font-bold" aria-labelledby="urgent-label">
                                        {{ userStats.AllurgentTickets }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500" id="failed-label">Failed</p>
                                    <p class="text-2xl font-bold" aria-labelledby="failed-label">
                                        {{ userStats.AllfailedTickets }}
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- My Performance Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-4">My Performance Today</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Closed Tickets</p>
                                <p class="text-2xl font-bold">{{ userStats.TodayclosedTickets }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Success Rate</p>
                                <p class="text-2xl font-bold">{{ userStats.TodaysuccessRate }}%</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Average Rating</p>
                                <p class="text-2xl font-bold">{{ userStats.TodayaverageRating }}</p>
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

                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="team in visibleTeams" 
                         :key="team.id" 
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

                        <div v-if="userPermissions && userPermissions.includes('view_teamtickets_menu')">
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
                <!-- <div class="mt-6 bg-white shadow-sm rounded-lg">
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
                </div> -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>