<template>
    <ShowLayout
        :title="department.department_name"
        subtitle="Department Information"
        icon="building"
        :actions="userPermissions && (userPermissions.includes('edit_departments') || userPermissions.includes('delete_departments'))"
    >
        <template #actions>
            <Link v-if="userPermissions.includes('edit_departments')" :href="route('departments.edit', department.id)" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-4M11 4l10 10M11 4v6h6"/>
                </svg>
                Edit
            </Link>
            <DangerButton v-if="userPermissions.includes('delete_departments')" @click="confirmDepartmentDeletion" class="bg-white/20 text-white hover:bg-white/30 rounded-xl px-6 py-3">
                Delete
            </DangerButton>
        </template>

        <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-400 mb-6">
            <div class="p-6 text-gray-900">
                <div class="flex items-center mb-6 pb-4 border-b">
                    <div class="bg-gradient-to-br from-indigo-500 to-blue-600 p-3 rounded-xl mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-xl text-gray-800">Department Details</h2>
                        <p class="text-sm text-gray-500">ID: #{{ department.id }}</p>
                    </div>
                </div>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ department.id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Department Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ department.department_name }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div v-if="userPermissions && (userPermissions.includes('view_positions_menu') || userPermissions.includes('show_positions'))" class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-400">
            <div class="p-6 text-gray-900">
                <div class="flex items-center justify-between mb-6 pb-4 border-b">
                    <div class="flex items-center">
                        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-3 rounded-xl mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Positions in this Department</h3>
                            <p class="text-sm text-gray-500">{{ positions.data.length }} position{{ positions.data.length !== 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                    <Link v-if="userPermissions && userPermissions.includes('create_positions')" :href="route('positions.create', { department: department.id })" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-500 hover:to-blue-500 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Position
                    </Link>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-blue-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position Title</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                <tr v-if="positions.data.length === 0">
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No positions found for this department.</td>
                                </tr>
                                <tr v-for="position in positions.data" :key="position.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ position.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <h1 class="text-indigo-600 hover:text-indigo-900">
                                            {{ position.position_title }}
                                        </h1>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right space-x-2">
                                        <Link v-if="userPermissions && userPermissions.includes('edit_positions')" :href="route('positions.edit', { position: position.id })" class="text-indigo-600 hover:text-indigo-900">
                                            Edit
                                        </Link>
                                        <button v-if="userPermissions && userPermissions.includes('delete_positions')" @click="confirmPositionDeletion(position)" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                </table>
                <div v-if="positions.data.length > 0" class="mt-6">
                    <Pagination :links="positions.links" />
                </div>
            </div>
        </div>

        <Modal :show="confirmingDepartmentDeletion" @close="closeDepartmentModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900"> 
                            Are you sure you want to delete this department?
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            This action cannot be undone. This will permanently delete the department and all its related positions.
                        </p>
                        <div class="mt-6 flex justify-end">
                            <SecondaryButton @click="closeDepartmentModal"> Cancel </SecondaryButton>
                            <DangerButton
                                class="ms-3"
                                :class="{ 'opacity-25': departmentDeleteForm.processing }"
                                :disabled="departmentDeleteForm.processing"
                                @click="deleteDepartment"
                            >
                                Delete Department
                            </DangerButton>
                        </div>
                    </div>
                </Modal>

                <Modal :show="confirmingPositionDeletion" @close="closePositionModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900"> 
                            Are you sure you want to delete this position?
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            You are about to delete the position: <span class="font-medium">{{ positionToDelete?.position_title }}</span>.
                            This action cannot be undone.
                        </p>
                        <div class="mt-6 flex justify-end">
                            <SecondaryButton @click="closePositionModal"> Cancel </SecondaryButton>
                            <DangerButton
                                class="ms-3"
                                :class="{ 'opacity-25': positionDeleteForm.processing }"
                                :disabled="positionDeleteForm.processing"
                                @click="deletePosition"
                            >
                                Delete Position
                            </DangerButton>
                        </div>
                    </div>
                </Modal>
    </ShowLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import ShowLayout from '@/Components/ShowLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue'; 

const props = defineProps({
    department: {
        type: Object,
        required: true
    },
    positions: {
        type: Object,
        required: true
    }
});


const confirmingDepartmentDeletion = ref(false);
const departmentDeleteForm = useForm({}); 
const confirmDepartmentDeletion = () => {
    confirmingDepartmentDeletion.value = true;
};

const deleteDepartment = () => {
    departmentDeleteForm.delete(route('departments.destroy', props.department.id), {
        onFinish: () => {
            confirmingDepartmentDeletion.value = false;
        },
    });
};

const closeDepartmentModal = () => {
    confirmingDepartmentDeletion.value = false;
};

const confirmingPositionDeletion = ref(false);
const positionToDelete = ref(null);
const positionDeleteForm = useForm({});

const confirmPositionDeletion = (position) => {
    positionToDelete.value = position;
    confirmingPositionDeletion.value = true;
};

const deletePosition = () => {
    positionDeleteForm.delete(route('positions.destroy', positionToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closePositionModal();
        },
        onFinish: () => {
        },
    });
};

const closePositionModal = () => {
    confirmingPositionDeletion.value = false;
    positionToDelete.value = null;
};

const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

</script>