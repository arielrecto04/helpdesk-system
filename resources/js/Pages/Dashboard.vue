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
            AllAssignedTickets: 0,
            TodayclosedTickets: 0,
            TodayAssignedTickets: 0,
            TodayDeadlineTickets: 0,
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
                    <!-- All Tickets / My Assigned Tickets -->
                    <div class="bg-white rounded-lg shadow p-6" role="region">
                        <!-- When user has permission show both system-wide and personal stats -->
                        <div v-if="hasPermission">
                            <div class="flex justify-between items-center mb-4">
                                <h3 id="all-tickets-heading" class="text-lg font-semibold flex items-baseline gap-3">
                                    <span>All System Tickets -</span>
                                    <span class="text-2xl font-bold text-gray-900">{{ stats.totalTickets }}</span>
                                    <span class="text-sm text-gray-500 font-normal">(Includes all tickets across teams)</span>
                                </h3>
                            </div>

                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div>
                                    <p class="text-sm text-gray-500" id="global-high-priority-label">High Priority</p>
                                    <p class="text-2xl font-bold" aria-labelledby="global-high-priority-label">{{ globalStats.AllhighPriorityTickets }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500" id="global-urgent-label">Urgent</p>
                                    <p class="text-2xl font-bold" aria-labelledby="global-urgent-label">{{ globalStats.AllurgentTickets }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500" id="global-failed-label">Failed</p>
                                    <p class="text-2xl font-bold" aria-labelledby="global-failed-label">{{ globalStats.AllfailedTickets }}</p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center mb-4">
                                <h3 id="my-tickets-heading" class="text-lg font-semibold flex items-baseline gap-3">
                                    <span>My Assigned Tickets -</span>
                                    <span class="text-2xl font-bold text-gray-900">{{ userStats.AllAssignedTickets }}</span>
                                    <span class="text-sm text-gray-500 font-normal">(Tickets assigned to you)</span>
                                </h3>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500" id="user-high-priority-label">High Priority</p>
                                    <p class="text-2xl font-bold" aria-labelledby="user-high-priority-label">{{ userStats.AllhighPriorityTickets }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500" id="user-urgent-label">Urgent</p>
                                    <p class="text-2xl font-bold" aria-labelledby="user-urgent-label">{{ userStats.AllurgentTickets }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500" id="user-failed-label">Failed</p>
                                    <p class="text-2xl font-bold" aria-labelledby="user-failed-label">{{ userStats.AllfailedTickets }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- When user does NOT have permission, show only personal stats -->
                        <div v-else>
                            <div class="flex justify-between items-center mb-4">
                                <h3 id="my-tickets-heading-fallback" class="text-lg font-semibold flex items-baseline gap-3">
                                    <span>My Assigned Tickets -</span>
                                    <span class="text-2xl font-bold text-gray-900">{{ userStats.AllAssignedTickets }}</span>
                                    <span class="text-sm text-gray-500 font-normal">(Tickets assigned to you)</span>
                                </h3>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500" id="user-high-priority-label-fallback">High Priority</p>
                                    <p class="text-2xl font-bold" aria-labelledby="user-high-priority-label-fallback">{{ userStats.AllhighPriorityTickets }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500" id="user-urgent-label-fallback">Urgent</p>
                                    <p class="text-2xl font-bold" aria-labelledby="user-urgent-label-fallback">{{ userStats.AllurgentTickets }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500" id="user-failed-label-fallback">Failed</p>
                                    <p class="text-2xl font-bold" aria-labelledby="user-failed-label-fallback">{{ userStats.AllfailedTickets }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Performance Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-baseline gap-3">
                            <span>My Performance Today -</span>
                            <span class="text-2xl font-bold text-gray-900">{{ userStats.TodayAssignedTickets }}</span>
                            <span class="text-sm text-gray-500 font-normal">(assigned today)</span>
                            <span class="text-2xl font-bold text-gray-900">{{ userStats.TodayDeadlineTickets }}</span>
                            <span class="text-sm text-gray-500 font-normal">(deadline today)</span>
                        </h3>
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
                        <h3 class="text-lg font-semibold mb-4">
                            {{ team.name }}
                            <span v-if="team.ticketCount !== undefined" class="ml-2 text-sm text-gray-500">({{ team.ticketCount }} tickets)</span>
                        </h3>
                        
                        <!-- High-level Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div>
                                <p class="text-sm text-gray-500">Tickets Closed</p>
                                <p class="text-xl font-bold">{{ hasPermission ? team.stats.closed : (team.userStats?.closed ?? team.stats.closed) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Success Rate</p>
                                <p class="text-xl font-bold">{{ hasPermission ? team.stats.success + '%' : ((team.userStats?.success ?? team.stats.success) + '%') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Average Rating</p>
                                <p class="text-xl font-bold">{{ hasPermission ? team.stats.rating : (team.userStats?.rating ?? team.stats.rating) }}</p>
                            </div>
                        </div>

                        <!-- Queue Status -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-sm text-gray-500">Open</p>
                                <p class="font-semibold">{{ hasPermission ? team.queue.open : (team.queue.user?.open ?? team.queue.open) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Unassigned</p>
                                <p class="font-semibold">{{ hasPermission ? team.queue.unassigned : (team.queue.user?.unassigned ?? team.queue.unassigned) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Urgent</p>
                                <p class="font-semibold">{{ hasPermission ? team.queue.urgent : (team.queue.user?.urgent ?? team.queue.urgent) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Failed</p>
                                <p class="font-semibold">{{ hasPermission ? team.queue.failed : (team.queue.user?.failed ?? team.queue.failed) }}</p>
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