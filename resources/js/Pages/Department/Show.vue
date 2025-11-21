<template>
    <Head :title="`Department: ${department.department_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Department Name - {{ department.department_name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight border-b pb-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span>Department: {{ department.department_name }}</span>
                                <div class="flex space-x-2" v-if="userPermissions && (userPermissions.includes('edit_departments') || userPermissions.includes('delete_departments'))">
                                    <Link v-if="userPermissions.includes('edit_departments')" :href="route('departments.edit', department.id)" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Edit
                                    </Link>
                                    <DangerButton v-if="userPermissions.includes('delete_departments')" @click="confirmDepartmentDeletion">Delete</DangerButton>
                                </div>
                            </div>
                        </h2>
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

                <div v-if="userPermissions && (userPermissions.includes('view_positions_menu') || userPermissions.includes('show_positions'))" class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Positions in this Department
                            </h3>
                            <Link v-if="userPermissions && userPermissions.includes('create_positions')" :href="route('positions.create', { department: department.id })" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Create New Position
                            </Link>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position Title</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
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