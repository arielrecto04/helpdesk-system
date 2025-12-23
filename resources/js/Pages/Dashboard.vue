<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// 2. Props Definition: Dito tinatanggap ang data galing sa Backend (Laravel controller)
const props = defineProps({
    auth: {
        type: Object,
        required: true // Kailangan may laman ito (user info)
    },
    stats: {
        type: Object,
        required: true // General statistics data
    },
    recentActivities: {
        type: Array,
        required: true // Listahan ng mga nakaraang activities
    },
    teams: {
        type: Array,
        required: true // Listahan ng lahat ng teams
    },
    // Detalyadong stats ng user. May "default" values ito para hindi mag-error
    // kung sakaling null o walang naipasa mula sa backend.
    userStats: {
        type: Object,
        default: () => ({
            AllhighPriorityTickets: 0,
            AllurgentTickets: 0,
            AllfailedTickets: 0,
            // AllClosedTickets: 0,
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
    // Global stats na may default values din para safety
    globalStats: {
        type: Object,
        default: () => ({
            AllhighPriorityTickets: 0,
            AllurgentTickets: 0,
            AllfailedTickets: 0,
        }),
    },
});

// 3. Accessing Page Data: Kinukuha ang global shared data ng Inertia
const page = usePage();

// Safety Check: Sinisigurado na hindi mag-eerror kung sakaling walang 'user' o 'permissions'
// Kung may permissions, kunin ito; kung wala, gawing empty array []
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions 
    ? page.props.auth.user.permissions 
    : [];

// Safety Check: Kunin ang logged-in user object, kung wala, gawing null
const authUser = page.props.auth && page.props.auth.user ? page.props.auth.user : null;

// 4. Computed Property: Visible Teams
// Ito ay automatic na nag-a-update kapag nagbago ang 'teams' o 'authUser'
const visibleTeams = computed(() => {
    // Kunin ang listahan ng teams mula sa props, o empty array kung wala
    const allTeams = props.teams || [];
    
    // Filter Logic: Ibalik lang ang mga teams na may canView = true
    // Ang canView flag ay na-set na sa backend base sa user permissions at team membership
    return allTeams.filter(team => team.canView === true);
});

// 5. Computed Property: Permissions Check
// Tinitignan kung allowed ba ang user makita ang lahat ng tickets
const hasPermission = computed(() => {
    const perms = userPermissions || [];
    // Magre-return ng TRUE kung meron siya kahit isa sa mga permissions na ito
    return (
        perms.includes('view_alltickets_menu') ||
        perms.includes('show_alltickets')
    );
});

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-6 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- 2. Personalized Statistics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- All Tickets / My Assigned Tickets -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6 border border-gray-100" role="region">
                        <!-- When user has permission show both system-wide and personal stats -->
                        <div v-if="hasPermission">
                            <div class="flex items-center mb-6">
                                <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-3 rounded-xl mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 id="all-tickets-heading" class="text-xl font-bold text-gray-800">All System Tickets</h3>
                                    <p class="text-sm text-gray-500">System-wide overview</p>
                                </div>
                                <div class="ml-auto">
                                    <span class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">{{ stats.totalTickets }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4 mb-8">
                                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200 hover:shadow-md transition-all">
                                    <div class="flex items-center mb-2">
                                        <div class="bg-orange-500 p-2 rounded-lg mr-2">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-orange-700" id="global-high-priority-label">High Priority</p>
                                    </div>
                                    <p class="text-3xl font-bold text-orange-600" aria-labelledby="global-high-priority-label">{{ globalStats.AllhighPriorityTickets }}</p>
                                </div>
                                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200 hover:shadow-md transition-all">
                                    <div class="flex items-center mb-2">
                                        <div class="bg-red-500 p-2 rounded-lg mr-2">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-red-700" id="global-urgent-label">Urgent</p>
                                    </div>
                                    <p class="text-3xl font-bold text-red-600" aria-labelledby="global-urgent-label">{{ globalStats.AllurgentTickets }}</p>
                                </div>
                                <div class="bg-gradient-to-br from-rose-50 to-rose-100 rounded-xl p-4 border border-rose-200 hover:shadow-md transition-all">
                                    <div class="flex items-center mb-2">
                                        <div class="bg-rose-500 p-2 rounded-lg mr-2">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-rose-700" id="global-failed-label">Failed</p>
                                    </div>
                                    <p class="text-3xl font-bold text-rose-600" aria-labelledby="global-failed-label">{{ globalStats.AllfailedTickets }}</p>
                                </div>
                            </div>

                            <div class="border-t-2 border-gray-200 pt-6 mt-6">
                                <div class="flex items-center mb-6">
                                    <div class="bg-gradient-to-br from-blue-500 to-cyan-600 p-3 rounded-xl mr-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 id="my-tickets-heading" class="text-xl font-bold text-gray-800">My Assigned Tickets</h3>
                                        <p class="text-sm text-gray-500">Your personal workload</p>
                                    </div>
                                    <div class="ml-auto">
                                        <span class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ userStats.AllAssignedTickets }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 border border-amber-200 hover:shadow-md transition-all">
                                        <div class="flex items-center mb-2">
                                            <div class="bg-amber-500 p-2 rounded-lg mr-2">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-amber-700" id="user-high-priority-label">High Priority</p>
                                        </div>
                                        <p class="text-3xl font-bold text-amber-600" aria-labelledby="user-high-priority-label">{{ userStats.AllhighPriorityTickets }}</p>
                                    </div>
                                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200 hover:shadow-md transition-all">
                                        <div class="flex items-center mb-2">
                                            <div class="bg-red-500 p-2 rounded-lg mr-2">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-red-700" id="user-urgent-label">Urgent</p>
                                        </div>
                                        <p class="text-3xl font-bold text-red-600" aria-labelledby="user-urgent-label">{{ userStats.AllurgentTickets }}</p>
                                    </div>
                                    <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl p-4 border border-pink-200 hover:shadow-md transition-all">
                                        <div class="flex items-center mb-2">
                                            <div class="bg-pink-500 p-2 rounded-lg mr-2">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-pink-700" id="user-failed-label">Failed</p>
                                        </div>
                                        <p class="text-3xl font-bold text-pink-600" aria-labelledby="user-failed-label">{{ userStats.AllfailedTickets }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- When user does NOT have permission, show only personal stats -->
                        <div v-else>
                            <div class="flex items-center mb-6">
                                <div class="bg-gradient-to-br from-blue-500 to-cyan-600 p-3 rounded-xl mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 id="my-tickets-heading-fallback" class="text-xl font-bold text-gray-800">My Assigned Tickets</h3>
                                    <p class="text-sm text-gray-500">Your personal workload</p>
                                </div>
                                <div class="ml-auto">
                                    <span class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ userStats.AllAssignedTickets }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 border border-amber-200 hover:shadow-md transition-all">
                                    <div class="flex items-center mb-2">
                                        <div class="bg-amber-500 p-2 rounded-lg mr-2">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-amber-700" id="user-high-priority-label-fallback">High Priority</p>
                                    </div>
                                    <p class="text-3xl font-bold text-amber-600" aria-labelledby="user-high-priority-label-fallback">{{ userStats.AllhighPriorityTickets }}</p>
                                </div>
                                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200 hover:shadow-md transition-all">
                                    <div class="flex items-center mb-2">
                                        <div class="bg-red-500 p-2 rounded-lg mr-2">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-red-700" id="user-urgent-label-fallback">Urgent</p>
                                    </div>
                                    <p class="text-3xl font-bold text-red-600" aria-labelledby="user-urgent-label-fallback">{{ userStats.AllurgentTickets }}</p>
                                </div>
                                <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl p-4 border border-pink-200 hover:shadow-md transition-all">
                                    <div class="flex items-center mb-2">
                                        <div class="bg-pink-500 p-2 rounded-lg mr-2">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-pink-700" id="user-failed-label-fallback">Failed</p>
                                    </div>
                                    <p class="text-3xl font-bold text-pink-600" aria-labelledby="user-failed-label-fallback">{{ userStats.AllfailedTickets }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Performance Card -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 p-6 border border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="bg-gradient-to-br from-green-500 to-emerald-600 p-3 rounded-xl mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">My Performance Today</h3>
                                <p class="text-sm text-gray-500">Track your daily progress</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-3 border border-blue-200">
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <p class="text-xs font-medium text-blue-700">Assigned Today</p>
                                </div>
                                <p class="text-2xl font-bold text-blue-600">{{ userStats.TodayAssignedTickets }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-3 border border-purple-200">
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-xs font-medium text-purple-700">Deadline Today</p>
                                </div>
                                <p class="text-2xl font-bold text-purple-600">{{ userStats.TodayDeadlineTickets }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-4 border border-emerald-200 hover:shadow-md transition-all">
                                <div class="flex items-center mb-2">
                                    <div class="bg-emerald-500 p-2 rounded-lg mr-2">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-medium text-emerald-700">Closed</p>
                                </div>
                                <p class="text-2xl font-bold text-emerald-600">{{ userStats.TodayclosedTickets }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-teal-50 to-teal-100 rounded-xl p-4 border border-teal-200 hover:shadow-md transition-all">
                                <div class="flex items-center mb-2">
                                    <div class="bg-teal-500 p-2 rounded-lg mr-2">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-medium text-teal-700">Success</p>
                                </div>
                                <p class="text-2xl font-bold text-teal-600">{{ userStats.TodaysuccessRate }}%</p>
                            </div>
                            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4 border border-yellow-200 hover:shadow-md transition-all">
                                <div class="flex items-center mb-2">
                                    <div class="bg-yellow-500 p-2 rounded-lg mr-2">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-medium text-yellow-700">Rating</p>
                                </div>
                                <p class="text-2xl font-bold text-yellow-600">{{ userStats.TodayaverageRating }}</p>
                            </div>
                        </div>
                        <div class="mt-6 pt-6 border-t-2 border-gray-200 bg-gradient-to-r from-gray-50 to-slate-50 rounded-xl p-4">
                            <div class="flex items-center mb-4">
                                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm font-semibold text-gray-700">7-Day Average</p>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 mb-1">Closed</p>
                                    <p class="text-lg font-bold text-gray-700">{{ userStats.weeklyAvg.closed }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 mb-1">Success</p>
                                    <p class="text-lg font-bold text-gray-700">{{ userStats.weeklyAvg.success }}%</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 mb-1">Rating</p>
                                    <p class="text-lg font-bold text-gray-700">{{ userStats.weeklyAvg.rating }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <!-- Team Statistics Section -->
                <div class="mb-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-2 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Team Overview</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="team in visibleTeams" 
                         :key="team.id" 
                         class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 border border-gray-100 transform hover:-translate-y-1" 
                         role="region" 
                         :aria-label="`${team.name} Team Statistics`">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <div class="bg-gradient-to-br from-violet-500 to-purple-600 p-3 rounded-xl mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">{{ team.name }}</h3>
                                    <p v-if="team.ticketCount !== undefined" class="text-xs text-gray-500">{{ team.ticketCount }} tickets</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- High-level Stats -->
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl p-3 border border-green-200 text-center hover:shadow-md transition-all">
                                <div class="flex justify-center mb-2">
                                    <div class="bg-green-500 p-1.5 rounded-lg">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 mb-1">Closed</p>
                                <p class="text-xl font-bold text-green-600">{{ hasPermission ? team.stats.closed : (team.userStats?.closed ?? team.stats.closed) }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-100 rounded-xl p-3 border border-blue-200 text-center hover:shadow-md transition-all">
                                <div class="flex justify-center mb-2">
                                    <div class="bg-blue-500 p-1.5 rounded-lg">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 mb-1">Success</p>
                                <p class="text-xl font-bold text-blue-600">{{ hasPermission ? team.stats.success + '%' : ((team.userStats?.success ?? team.stats.success) + '%') }}</p>
                            </div>
                            <div class="bg-gradient-to-br from-amber-50 to-yellow-100 rounded-xl p-3 border border-amber-200 text-center hover:shadow-md transition-all">
                                <div class="flex justify-center mb-2">
                                    <div class="bg-amber-500 p-1.5 rounded-lg">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 mb-1">Rating</p>
                                <p class="text-xl font-bold text-amber-600">{{ hasPermission ? team.stats.rating : (team.userStats?.rating ?? team.stats.rating) }}</p>
                            </div>
                        </div>

                        <!-- Queue Status -->
                        <div class="bg-gradient-to-br from-gray-50 to-slate-100 rounded-xl p-4 mb-4 border border-gray-200">
                            <div class="flex items-center mb-3">
                                <svg class="w-4 h-4 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                                <p class="text-sm font-semibold text-gray-700">Queue Status</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white rounded-lg p-2 border border-gray-200">
                                    <p class="text-xs text-gray-500">Open</p>
                                    <p class="text-lg font-bold text-gray-700">{{ hasPermission ? team.queue.open : (team.queue.user?.open ?? team.queue.open) }}</p>
                                </div>
                                <div class="bg-white rounded-lg p-2 border border-gray-200">
                                    <p class="text-xs text-gray-500">Unassigned</p>
                                    <p class="text-lg font-bold text-gray-700">{{ hasPermission ? team.queue.unassigned : (team.queue.user?.unassigned ?? team.queue.unassigned) }}</p>
                                </div>
                                <div class="bg-white rounded-lg p-2 border border-red-200 bg-red-50">
                                    <p class="text-xs text-red-600">Urgent</p>
                                    <p class="text-lg font-bold text-red-600">{{ hasPermission ? team.queue.urgent : (team.queue.user?.urgent ?? team.queue.urgent) }}</p>
                                </div>
                                <div class="bg-white rounded-lg p-2 border border-rose-200 bg-rose-50">
                                    <p class="text-xs text-rose-600">Failed</p>
                                    <p class="text-lg font-bold text-rose-600">{{ hasPermission ? team.queue.failed : (team.queue.user?.failed ?? team.queue.failed) }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="userPermissions && userPermissions.includes('view_teamtickets_menu')">
                            <Link 
                                :href="route('teamtickets.index', { team: team.id })" 
                                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl py-3 hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 text-center block font-semibold shadow-md hover:shadow-lg transform hover:scale-105"
                            >
                                <span class="flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View Tickets
                                </span>
                            </Link>
                        </div>
                        <div v-else>
                            <button disabled class="w-full bg-gray-300 text-gray-500 rounded-xl py-3 text-center block font-semibold cursor-not-allowed">
                                <span class="flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    View Tickets
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>