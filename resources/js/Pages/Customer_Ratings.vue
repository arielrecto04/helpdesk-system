<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    ratings: {
        type: Object,
        required: true
    },
    teams: {
        type: Array,
        required: true
    },
    stats: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const searchQuery = ref(props.filters.search || '');
const selectedRating = ref(props.filters.rating || 'all');
const selectedTeam = ref(props.filters.team_id || 'all');

// Watch for changes and update URL with filters
watch([searchQuery, selectedRating, selectedTeam], () => {
    router.get(route('customer-ratings.index'), {
        search: searchQuery.value,
        rating: selectedRating.value,
        team_id: selectedTeam.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, { debounce: 300 });

const getRatingColor = (rating) => {
    const colors = {
        5: 'text-green-600 bg-green-50',
        4: 'text-blue-600 bg-blue-50',
        3: 'text-yellow-600 bg-yellow-50',
        2: 'text-orange-600 bg-orange-50',
        1: 'text-red-600 bg-red-50'
    };
    return colors[rating] || 'text-gray-600 bg-gray-50';
};

const getStarColor = (rating) => {
    if (rating >= 4) return 'text-yellow-400';
    if (rating === 3) return 'text-yellow-500';
    return 'text-orange-500';
};

const viewRating = (ratingId) => {
    router.visit(route('customer-ratings.show', ratingId));
};
</script>

<template>
    <Head title="Customer Ratings" />

    <AuthenticatedLayout>
        <div class="py-8 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
            <div class="mx-auto max-w-screen-2xl sm:px-6 lg:px-8">

                <!-- Header Banner -->
                <div class="mb-8">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="bg-white/20 p-3 rounded-xl mr-4">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="font-bold text-3xl text-white">Customer Ratings</h2>
                                    <p class="text-yellow-100 text-sm mt-1">Monitor customer satisfaction and feedback</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                    <!-- Total Ratings -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500">Total Ratings</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total }}</p>
                            </div>
                            <div class="bg-purple-100 p-2 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Average Rating -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500">Average</p>
                                <div class="flex items-center mt-1">
                                    <p class="text-2xl font-bold text-yellow-500">{{ stats.average }}</p>
                                    <svg class="w-5 h-5 text-yellow-400 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5 Stars -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl shadow-lg p-6 border border-green-200">
                        <p class="text-xs font-medium text-green-700">5 ★</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ stats.five_star }}</p>
                    </div>

                    <!-- 4 Stars -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg p-6 border border-blue-200">
                        <p class="text-xs font-medium text-blue-700">4 ★</p>
                        <p class="text-2xl font-bold text-blue-600 mt-1">{{ stats.four_star }}</p>
                    </div>

                    <!-- 3 Stars -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-lg p-6 border border-yellow-200">
                        <p class="text-xs font-medium text-yellow-700">3 ★</p>
                        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ stats.three_star }}</p>
                    </div>

                    <!-- 1-2 Stars -->
                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-lg p-6 border border-red-200">
                        <p class="text-xs font-medium text-red-700">1-2 ★</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ stats.one_star + stats.two_star }}</p>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <div class="relative">
                                <input 
                                    v-model="searchQuery"
                                    type="text" 
                                    placeholder="Search by customer, ticket, or comment..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                >
                                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Rating</label>
                            <select v-model="selectedRating" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="all">All Ratings</option>
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>

                        <!-- Team Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Team</label>
                            <select v-model="selectedTeam" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="all">All Teams</option>
                                <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.team_name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Ratings Table -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="rating in ratings.data" :key="rating.id" class="hover:bg-gray-50 transition-colors cursor-pointer" @click="viewRating(rating.id)">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="bg-indigo-100 p-2 rounded-lg mr-2">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">#{{ rating.ticket_id }}</p>
                                                <p class="text-xs text-gray-500">{{ rating.ticket_subject }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ rating.customer_name }}</p>
                                            <p class="text-xs text-gray-500">{{ rating.customer_email }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ rating.assigned_to }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ rating.team_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span :class="getRatingColor(rating.rating)" class="px-3 py-1 rounded-full text-sm font-bold flex items-center">
                                                {{ rating.rating }}
                                                <svg :class="getStarColor(rating.rating)" class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-700 line-clamp-2">{{ rating.comment || 'No comment' }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ rating.submitted_on }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('customer-ratings.show', rating.id)" class="text-yellow-600 hover:text-yellow-900 inline-flex items-center">
                                            View
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <Pagination :links="ratings.links" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
