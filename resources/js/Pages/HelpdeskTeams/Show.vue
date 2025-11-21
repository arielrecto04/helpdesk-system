<template>
    <Head :title="`Team Name #${helpdesk_team.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Team Name - {{ helpdesk_team.team_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight border-b pb-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span>Team Name {{ helpdesk_team.team_name }}</span>
                                <!-- Add Edit Button & Delete Button (only when permissions enabled) -->
                                <div class="flex space-x-2" v-if="userPermissions && (userPermissions.includes('edit_helpdeskteams') || userPermissions.includes('delete_helpdeskteams'))">
                                    <Link v-if="userPermissions.includes('edit_helpdeskteams')" :href="route('helpdeskteams.edit', helpdesk_team.id)" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Edit
                                    </Link>
                                    <DangerButton v-if="userPermissions.includes('delete_helpdeskteams')" @click="confirmTeamDeletion">Delete</DangerButton>
                                </div>
                            </div>
                        </h2>

                        <!-- Ticket Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Helpdesk Team Information</h3>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ helpdesk_team.id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Team Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ helpdesk_team.team_name }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <Modal :show="confirmingTeamDeletion" @close="closeModal">
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
                                @click="deleteTeam"
                            >
                                Delete Team
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
    helpdesk_team: {
        type: Object,
        required: true
    }
});

const confirmingTeamDeletion = ref(false);
const form = useForm({});
const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

const confirmTeamDeletion = () => {
    confirmingTeamDeletion.value = true;
};

const deleteTeam = () => {
    form.delete(route('helpdeskteam.destroy', props.helpdesk_team.id), {
        onFinish: () => {
            confirmingTeamDeletion.value = false;
        },
    });
};

const closeModal = () => {
    confirmingTeamDeletion.value = false;
};


</script>