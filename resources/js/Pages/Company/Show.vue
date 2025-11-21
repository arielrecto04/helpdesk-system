<template>
    <Head :title="`Company Name #${company.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Company Name - {{ company.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight border-b pb-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span>Company Name - {{ company.name }}</span>
                                <div class="flex space-x-2" v-if="userPermissions && (userPermissions.includes('edit_companies') || userPermissions.includes('delete_companies'))">
                                    <Link v-if="userPermissions.includes('edit_companies')" :href="route('companies.edit', company.id)" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Edit
                                    </Link>
                                    <DangerButton v-if="userPermissions.includes('delete_companies')" @click="confirmCompanyDeletion">Delete</DangerButton>
                                </div>
                            </div>
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Company Information</h3>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ company.id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Company Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ company.name }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ company.address }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ company.phone }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ company.email }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <Modal :show="confirmingCompanyDeletion" @close="closeModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900"> 
                            Are you sure you want to delete this company?
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            This action cannot be undone. This will permanently delete the company and all its related data.
                        </p>
                        <div class="mt-6 flex justify-end">
                            <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                            <DangerButton
                                class="ms-3"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                @click="deleteCompany"
                            >
                                Delete Company
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
    company: {
        type: Object,
        required: true
    }
});

const confirmingCompanyDeletion = ref(false);
const form = useForm({});
const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

const confirmCompanyDeletion = () => {
    confirmingCompanyDeletion.value = true;
};

const deleteCompany = () => {
    form.delete(route('companies.destroy', props.company.id), {
        onFinish: () => {
            confirmingCompanyDeletion.value = false;
        },
    });
};

const closeModal = () => {
    confirmingCompanyDeletion.value = false;
};

</script>