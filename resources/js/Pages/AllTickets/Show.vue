<template>
    <Head :title="`Ticket #${ticket.id}`" />

    <AuthenticatedLayout>
        <div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-8">
            <!-- Header Banner -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-3 rounded-xl mr-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-3xl text-white">Ticket #{{ ticket.id }}</h2>
                                <p class="text-blue-100 text-sm mt-1">{{ ticket.subject }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2" v-if="userPermissions && (userPermissions.includes('edit_alltickets') || userPermissions.includes('delete_alltickets'))">
                            <Link v-if="userPermissions.includes('edit_alltickets')" :href="route('alltickets.edit', ticket.id)" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-4M11 4l10 10M11 4v6h6"/>
                                </svg>
                                Edit
                            </Link>
                            <DangerButton v-if="userPermissions.includes('delete_alltickets')" @click="confirmAllTicketDeletion" class="bg-white/20 text-white hover:bg-white/30 rounded-xl px-6 py-3">
                                Delete
                            </DangerButton>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-200">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center mb-6 pb-4 border-b">
                            <div class="bg-gradient-to-br from-indigo-500 to-blue-600 p-3 rounded-xl mr-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-xl text-gray-800">Ticket Details</h2>
                                <p class="text-sm text-gray-500">Reference: #{{ ticket.id }}</p>
                            </div>
                        </div>

                        <!-- Ticket Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="flex items-center mb-4">
                                    <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-2 rounded-lg mr-2">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Ticket Information</h3>
                                </div>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ ticket.id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1">
                                            <span class="px-3 py-1.5 inline-flex items-center text-xs leading-5 font-bold rounded-xl shadow-sm" :class="getStageClass(ticket.stage)">
                                                <svg v-if="['Resolved', 'Closed'].includes(ticket.stage)" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <svg v-else-if="ticket.stage === 'In Progress'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <svg v-else class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                {{ ticket.stage }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                                        <dd class="mt-1">
                                            <span class="px-3 py-1.5 inline-flex items-center text-xs leading-5 font-bold rounded-xl shadow-sm" :class="getPriorityClass(ticket.priority)">
                                                <svg v-if="ticket.priority === 'Urgent'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                <svg v-else-if="ticket.priority === 'High'" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                                </svg>
                                                <svg v-else class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                </svg>
                                                {{ ticket.priority }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ new Date(ticket.created_at).toLocaleString() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Deadline</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ new Date(ticket.deadline).toLocaleString() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ new Date(ticket.updated_at).toLocaleString() }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <div class="flex items-center mb-4">
                                    <div class="bg-gradient-to-br from-teal-500 to-emerald-600 p-2 rounded-lg mr-2">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">People</h3>
                                </div>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Customer</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ ticket.customer.first_name }} {{ ticket.customer.last_name }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Team</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ ticket.team.team_name }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ ticket.assigned_employee_is_current_user ? 'You' : (ticket.assigned_employee_name ? ticket.assigned_employee_name : 'Unassigned') }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Ticket Description -->
                        <div class="mt-8">
                            <div class="flex items-center mb-3">
                                <div class="bg-gradient-to-br from-yellow-500 to-amber-600 p-2 rounded-lg mr-2">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Description</h3>
                            </div>
                            <div class="bg-gradient-to-br from-gray-50 to-slate-100 rounded-xl p-4 border border-gray-200">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ ticket.description }}</p>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="mt-6">
                            <div class="flex items-center mb-3">
                                <div class="bg-gradient-to-br from-pink-500 to-rose-600 p-2 rounded-lg mr-2">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M4 4h16v16H4V4zm4 4l10 10"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Tags</h3>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span v-for="tag in ticket.tags || []" :key="tag.id" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gradient-to-r from-slate-100 to-slate-200 text-slate-800 border border-slate-300">
                                    {{ tag.name }}
                                </span>
                                <span v-if="!(ticket.tags && ticket.tags.length)" class="text-sm text-slate-500">No tags</span>
                            </div>
                        </div>
                    </div>
                </div>

                <Modal :show="confirmingAllTicketDeletion" @close="closeModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900">
                            Are you sure you want to delete this ticket?
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            This action cannot be undone. This will permanently delete the ticket and all its related data.
                        </p>
                        <div class="mt-6 flex justify-end">
                            <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                            <DangerButton
                                class="ms-3"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                @click="deleteAllTicket">
                                Delete Ticket
                            </DangerButton>
                        </div>
                    </div>
                </Modal>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    ticket: {
        type: Object,
        required: true
    }
});

const confirmingAllTicketDeletion = ref(false);
const form = useForm({});

const page = usePage();
const authUser = page.props.auth.user;
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

const confirmAllTicketDeletion = () => {
    confirmingAllTicketDeletion.value = true;
};

const deleteAllTicket = () => {
    form.delete(route('alltickets.destroy', props.ticket.id), {
        onFinish: () => {
            confirmingAllTicketDeletion.value = false;
        },
    });
};

const closeModal = () => {
    confirmingAllTicketDeletion.value = false;
};

const getPriorityClass = (priority) => {
    return {
        'bg-gradient-to-r from-red-100 to-rose-100 text-red-800 border border-red-200': priority === 'Urgent',
        'bg-gradient-to-r from-orange-100 to-amber-100 text-orange-800 border border-orange-200': priority === 'High',
        'bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-800 border border-blue-200': priority === 'Medium',
        'bg-gradient-to-r from-gray-100 to-slate-100 text-gray-800 border border-gray-200': priority === 'Low',
    };
};

const getStageClass = (stage) => {
    return {
        'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200': ['Resolved', 'Closed'].includes(stage),
        'bg-gradient-to-r from-blue-100 to-sky-100 text-blue-800 border border-blue-200': stage === 'Open',
        'bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-800 border border-yellow-200': stage === 'In Progress',
    };
};

</script>