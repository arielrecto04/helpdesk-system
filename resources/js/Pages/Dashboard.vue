<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

// Replace the hardcoded data with props
defineProps({
    stats: Object,
    recentActivities: Array
});
</script>

<!-- Rest of your template remains the same -->

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Total Tickets</h3>
                        <p class="text-2xl font-semibold text-gray-700">{{ stats.totalTickets }}</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Open Tickets</h3>
                        <p class="text-2xl font-semibold text-blue-600">{{ stats.openTickets }}</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Resolved Today</h3>
                        <p class="text-2xl font-semibold text-green-600">{{ stats.resolvedToday }}</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg shadow-sm">
                        <h3 class="text-sm font-medium text-gray-500">Avg. Response Time</h3>
                        <p class="text-2xl font-semibold text-purple-600">{{ stats.averageResponseTime }}</p>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-for="activity in recentActivities" :key="activity.id" 
                             class="px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                                    <p class="text-sm text-gray-500">{{ activity.time }}</p>
                                </div>
                                <span :class="{
                                    'px-2 py-1 text-xs font-medium rounded-full': true,
                                    'bg-green-100 text-green-800': activity.status === 'Resolved',
                                    'bg-blue-100 text-blue-800': activity.status === 'New',
                                    'bg-yellow-100 text-yellow-800': activity.status === 'In Progress'
                                }">
                                    {{ activity.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>