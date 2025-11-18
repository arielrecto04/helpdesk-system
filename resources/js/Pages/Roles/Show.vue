<template>
    <Head :title="`Role - ${role.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Role: {{ role.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight border-b pb-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span>Role Details</span>
                                <div class="flex space-x-2">
                                    <Link :href="route('roles.edit', role.id)" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Edit
                                    </Link>
                                    <DangerButton @click="confirmRoleDeletion">Delete</DangerButton>
                                </div>
                            </div>
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">

                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Role Information</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ role.id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Role Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ role.name }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ role.description || 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions ({{ role.permissions.length }})</h3>
                                <div v-if="role.permissions.length > 0" class="space-y-2">
                                    <span v-for="permission in role.permissions" :key="permission.id" class="inline-block bg-indigo-100 text-indigo-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full">
                                        {{ permission.name }}
                                    </span>
                                </div>
                                <div v-else>
                                    <p class="text-sm text-gray-500">No permissions assigned to this role.</p>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <Modal :show="confirmingRoleDeletion" @close="closeModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900"> 
                            Are you sure you want to delete this role?
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            This action cannot be undone. This will permanently delete the role and all its related data.
                        </p>
                        <div class="mt-6 flex justify-end">
                            <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                            <DangerButton
                                class="ms-3"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                @click="deleteRole" 
                            >
                                Delete Role
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
    role: {
        type: Object,
        required: true
    }
});

const confirmingRoleDeletion = ref(false);
const form = useForm({});

const confirmRoleDeletion = () => {
    confirmingRoleDeletion.value = true;
};

const deleteRole = () => {
    form.delete(route('roles.destroy', props.role.id), {
        onFinish: () => {
            confirmingRoleDeletion.value = false;

        },
    });
};

const closeModal = () => {
    confirmingRoleDeletion.value = false;
};
</script>