<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';



const page = usePage();
const authUser = page.props.auth.user;
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];
const props = defineProps({

    tickets: {
        type: Object,
        required: true,
    },

    pageTitle: {
        type: String,
        required: true,
    }

});


const getPriorityClass = (priority) => {

    return {
        'bg-red-100 text-red-800': priority === 'Urgent',
        'bg-orange-100 text-orange-800': priority === 'High',
        'bg-blue-100 text-blue-800': priority === 'Medium',
        'bg-gray-100 text-gray-800': priority === 'Low',
    };
};
const getStageClass = (stage) => {

    return {
        'bg-green-100 text-green-800': ['Resolved', 'Closed'].includes(stage),
        'bg-blue-100 text-blue-800': stage === 'Open',
        'bg-yellow-100 text-yellow-800': stage === 'In Progress',
        'bg-purple-100 text-purple-800': stage === 'Pending Customer',
    };

};

const viewTicket = (ticketId) => {
    router.visit(route('mytickets.show', ticketId));
};

</script>

<template>

    <Head :title="pageTitle" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ pageTitle }}</h2>
                <Link v-if="userPermissions && userPermissions.includes('create_tickets')" :href="route('mytickets.create')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Create Ticket
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stage</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="tickets.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            You have no assigned tickets.
                                        </td>
                                    </tr>
                                    <tr v-for="ticket in tickets.data" :key="ticket.id" @click="viewTicket(ticket.id)" class="hover:bg-gray-100 cursor-pointer">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ ticket.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ ticket.subject }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ ticket.customer ? `${ticket.customer.first_name} ${ticket.customer.last_name}` : 'N/A' }}
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
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStageClass(ticket.stage)">
                                                {{ ticket.stage }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getPriorityClass(ticket.priority)">
                                                {{ ticket.priority }}
                                            </span>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ ticket.team ? ticket.team.team_name : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(ticket.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ ticket.deadline }}</div>
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
    </AuthenticatedLayout>
</template>