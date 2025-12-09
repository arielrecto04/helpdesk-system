<template>
    <Head :title="`Ticket #${ticket.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ticket #{{ ticket.id }} - {{ ticket.subject }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight border-b pb-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span>Ticket #{{ ticket.id }} - {{ ticket.subject }}</span>
                                <div class="flex space-x-2" v-if="userPermissions && (userPermissions.includes('edit_teamtickets') || userPermissions.includes('delete_teamtickets'))">
                                    <Link v-if="userPermissions.includes('edit_teamtickets')" :href="route('teamtickets.edit', ticket.id)" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Edit
                                    </Link>
                                    <DangerButton v-if="userPermissions.includes('delete_teamtickets')" @click="confirmTeamTicketDeletion">Delete</DangerButton>
                                </div>
                            </div>
                        </h2>

                        <!-- Ticket Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Ticket Information</h3>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ ticket.id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStageClass(ticket.stage)">
                                                {{ ticket.stage }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                                        <dd class="mt-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getPriorityClass(ticket.priority)">
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
                                <h3 class="text-lg font-medium text-gray-900 mb-4">People</h3>
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
                            <h3 class="text-lg font-medium text-gray-900">Description</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ ticket.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <Modal :show="confirmingTeamTicketDeletion" @close="closeModal">
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
                                @click="deleteTeamTicket"
                            >
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

const confirmingTeamTicketDeletion = ref(false);
const form = useForm({});

const page = usePage();
const authUser = page.props.auth.user;
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

const confirmTeamTicketDeletion = () => {
    confirmingTeamTicketDeletion.value = true;
};

const deleteTeamTicket = () => {
    form.delete(route('teamtickets.destroy', props.ticket.id), {
        onFinish: () => {
            confirmingTeamTicketDeletion.value = false;
        },
    });
};

const closeModal = () => {
    confirmingTeamTicketDeletion.value = false;
};

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

    };
};

</script>