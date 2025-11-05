<template>
    <Head title="Team Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ team.team_name }} - Team Tickets
                    </h2>
                    <p v-if="isAdmin" class="text-sm text-gray-600 mt-1">
                        Viewing as Administrator
                    </p>
                </div>
                <Link :href="route('dashboard')" class="text-blue-600 hover:text-blue-800">
                    Back to Dashboard
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Tickets Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stage</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="ticket in tickets.data" :key="ticket.id" @click="viewTicket(ticket.id)" class="hover:bg-gray-100 cursor-pointer">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ ticket.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ ticket.subject }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ ticket.customer ? `${ticket.customer.first_name} ${ticket.customer.last_name}` : 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ ticket.assigned_to_user_id === authUser.id 
                                                    ? 'You' 
                                                    : (ticket.assigned_user_name ? ticket.assigned_user_name : 'Unassigned') 
                                                }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-2 py-1 text-xs font-medium rounded-full': true,
                                                'bg-green-100 text-green-800': ticket.stage === 'Resolved',
                                                'bg-blue-100 text-blue-800': ticket.stage === 'Open',
                                                'bg-yellow-100 text-yellow-800': ticket.stage === 'In Progress'
                                            }">
                                                {{ ticket.stage }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ ticket.deadline }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-2 py-1 text-xs font-medium rounded-full': true,
                                                'bg-red-100 text-red-800': ticket.priority === 'Urgent',
                                                'bg-orange-100 text-orange-800': ticket.priority === 'High',
                                                'bg-yellow-100 text-yellow-800': ticket.priority === 'Medium',
                                                'bg-green-100 text-green-800': ticket.priority === 'Low'
                                            }">
                                                {{ ticket.priority }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            <Pagination :links="tickets.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

const authUser = usePage().props.auth.user;

const viewTicket = (ticketId) => {
    router.visit(route('tickets.show', ticketId));
};

defineProps({
    tickets: Object,
    team: Object,
    isAdmin: {
        type: Boolean,
        default: false
    }
});</script>