<template>
    <Head :title="`Ticket #${ticket.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ticket #{{ ticket.id }} - {{ ticket.subject }}
                </h2>
                <Link :href="route('team.tickets', ticket.team_id)" class="text-blue-600 hover:text-blue-800">
                    Back to Team Tickets
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Ticket Details -->
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Ticket Information</h3>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1">
                                            <span :class="{
                                                'px-2 py-1 text-xs font-medium rounded-full': true,
                                                'bg-green-100 text-green-800': ticket.stage === 'Resolved',
                                                'bg-blue-100 text-blue-800': ticket.stage === 'Open',
                                                'bg-yellow-100 text-yellow-800': ticket.stage === 'In Progress'
                                            }">
                                                {{ ticket.stage }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                                        <dd class="mt-1">
                                            <span :class="{
                                                'px-2 py-1 text-xs font-medium rounded-full': true,
                                                'bg-red-100 text-red-800': ticket.priority === 'Urgent',
                                                'bg-orange-100 text-orange-800': ticket.priority === 'High',
                                                'bg-yellow-100 text-yellow-800': ticket.priority === 'Medium',
                                                'bg-green-100 text-green-800': ticket.priority === 'Low'
                                            }">
                                                {{ ticket.priority }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ new Date(ticket.created_at).toLocaleString() }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">People</h3>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Customer</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ ticket.customer.first_name }} {{ ticket.customer.last_name }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ ticket.assignedTo ? 
                                                `${ticket.assignedTo.first_name} ${ticket.assignedTo.last_name}` : 
                                                'Unassigned' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Team</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ ticket.team.team_name }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Ticket Description -->
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Description</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-700">{{ ticket.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    ticket: {
        type: Object,
        required: true
    }
});</script>