<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    rating: {
        type: Object,
        required: true
    },
    otherRatings: {
        type: Array,
        default: () => []
    }
});

const getRatingColor = (rating) => {
    const colors = {
        5: 'text-green-600',
        4: 'text-blue-600',
        3: 'text-yellow-600',
        2: 'text-orange-600',
        1: 'text-red-600'
    };
    return colors[rating] || 'text-gray-600';
};

const getRatingBgColor = (rating) => {
    const colors = {
        5: 'bg-green-50 border-green-200',
        4: 'bg-blue-50 border-blue-200',
        3: 'bg-yellow-50 border-yellow-200',
        2: 'bg-orange-50 border-orange-200',
        1: 'bg-red-50 border-red-200'
    };
    return colors[rating] || 'bg-gray-50 border-gray-200';
};

const renderStars = (rating) => {
    return '★'.repeat(rating) + '☆'.repeat(5 - rating);
};
</script>

<template>
    <Head :title="`Rating #${rating.id}`" />

    <AuthenticatedLayout>
        <div class="py-8 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Header with Back Button -->
                <div class="mb-8">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <Link :href="route('customer-ratings.index')" class="bg-white/20 p-2 rounded-lg mr-4 hover:bg-white/30 transition-colors">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                </Link>
                                <div>
                                    <h2 class="font-bold text-3xl text-white">Customer Rating Details</h2>
                                    <p class="text-yellow-100 text-sm mt-1">View complete rating information</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Rating Card -->
                        <div :class="getRatingBgColor(rating.rating)" class="rounded-2xl shadow-xl p-8 border-2">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold text-gray-800">Rating Score</h3>
                                <div class="text-right">
                                    <div :class="getRatingColor(rating.rating)" class="text-6xl font-bold">
                                        {{ rating.rating }}
                                    </div>
                                    <div :class="getRatingColor(rating.rating)" class="text-3xl mt-2">
                                        {{ renderStars(rating.rating) }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white rounded-xl p-6 shadow-sm">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Customer Feedback</h4>
                                <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ rating.comment || 'No comment provided' }}</p>
                            </div>

                            <div class="mt-4 flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Submitted on {{ rating.submitted_on }}</span>
                            </div>
                        </div>

                        <!-- Ticket Information -->
                        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                            <div class="flex items-center mb-6">
                                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-3 rounded-xl mr-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800">Ticket Information</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Ticket ID</p>
                                    <p class="text-lg font-semibold text-gray-900 mt-1">#{{ rating.ticket.id }}</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">Status</p>
                                    <span class="inline-flex mt-1 px-3 py-1 text-sm font-semibold rounded-full"
                                          :class="{
                                              'bg-green-100 text-green-700': rating.ticket.stage === 'Resolved',
                                              'bg-blue-100 text-blue-700': rating.ticket.stage === 'Open',
                                              'bg-yellow-100 text-yellow-700': rating.ticket.stage === 'In Progress',
                                              'bg-gray-100 text-gray-700': rating.ticket.stage === 'Closed'
                                          }">
                                        {{ rating.ticket.stage }}
                                    </span>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">Priority</p>
                                    <span class="inline-flex mt-1 px-3 py-1 text-sm font-semibold rounded-full"
                                          :class="{
                                              'bg-red-100 text-red-700': rating.ticket.priority === 'Urgent',
                                              'bg-orange-100 text-orange-700': rating.ticket.priority === 'High',
                                              'bg-green-100 text-green-700': rating.ticket.priority === 'Normal',
                                              'bg-blue-100 text-blue-700': rating.ticket.priority === 'Low'
                                          }">
                                        {{ rating.ticket.priority }}
                                    </span>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">Created</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ rating.ticket.created_at }}</p>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <p class="text-sm font-medium text-gray-500 mb-2">Subject</p>
                                <p class="text-lg font-semibold text-gray-900">{{ rating.ticket.subject }}</p>
                            </div>

                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-500 mb-2">Description</p>
                                <p class="text-sm text-gray-700 leading-relaxed">{{ rating.ticket.description }}</p>
                            </div>

                            <div v-if="rating.ticket.tags && rating.ticket.tags.length > 0" class="mt-4">
                                <p class="text-sm font-medium text-gray-500 mb-2">Tags</p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="tag in rating.ticket.tags" :key="tag" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                        {{ tag }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Customer Information -->
                        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                            <div class="flex items-center mb-4">
                                <div class="bg-gradient-to-br from-blue-500 to-cyan-600 p-2 rounded-lg mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">Customer</h3>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Name</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ rating.customer.name }}</p>
                                </div>

                                <div>
                                    <p class="text-xs font-medium text-gray-500">Email</p>
                                    <p class="text-sm text-gray-700 mt-1">{{ rating.customer.email }}</p>
                                </div>

                                <div v-if="rating.customer.phone">
                                    <p class="text-xs font-medium text-gray-500">Phone</p>
                                    <p class="text-sm text-gray-700 mt-1">{{ rating.customer.phone }}</p>
                                </div>

                                <div>
                                    <p class="text-xs font-medium text-gray-500">Company</p>
                                    <p class="text-sm text-gray-700 mt-1">{{ rating.customer.company }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Information -->
                        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                            <div class="flex items-center mb-4">
                                <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-2 rounded-lg mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">Assignment</h3>
                            </div>

                            <div class="space-y-3">
                                <div v-if="rating.assigned_to">
                                    <p class="text-xs font-medium text-gray-500">Assigned Agent</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ rating.assigned_to.name }}</p>
                                    <p class="text-xs text-gray-600">{{ rating.assigned_to.email }}</p>
                                </div>
                                <div v-else>
                                    <p class="text-xs font-medium text-gray-500">Assigned Agent</p>
                                    <p class="text-sm text-gray-600 mt-1">Unassigned</p>
                                </div>

                                <div v-if="rating.team">
                                    <p class="text-xs font-medium text-gray-500">Team</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ rating.team.name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Other Ratings from Same Customer -->
                        <div v-if="otherRatings.length > 0" class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Other Ratings from Customer</h3>
                            
                            <div class="space-y-3">
                                <Link v-for="other in otherRatings" :key="other.id" 
                                      :href="route('customer-ratings.show', other.id)"
                                      class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">Ticket #{{ other.ticket_id }}</p>
                                            <p class="text-xs text-gray-600 mt-1">{{ other.team_name }}</p>
                                            <p class="text-xs text-gray-500">{{ other.submitted_on }}</p>
                                        </div>
                                        <div :class="getRatingColor(other.rating)" class="text-2xl font-bold ml-3">
                                            {{ other.rating }}★
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
