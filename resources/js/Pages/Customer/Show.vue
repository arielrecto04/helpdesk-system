<template>
    <Head :title="`Customer`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Customer Name - {{ customer.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight border-b pb-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span>Customer Name - {{ customer.name }}</span>
                                <!-- Add Edit Button & Delete Button-->
                                <div class="flex space-x-2">
                                    <Link :href="route('customers.edit', customer.id)" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Edit
                                    </Link>
                                    <DangerButton @click="confirmCustomerDeletion">Delete</DangerButton>
                                </div>
                            </div>
                        </h2>

                        <!-- Ticket Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">General Information</h3>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ customer.id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">First Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ customer.first_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Middle Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ customer.middle_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Last Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ customer.last_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ customer.email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ customer.phone_number }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ customer.created_at }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ customer.updated_at }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <Modal :show="confirmingCustomerDeletion" @close="closeModal">
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
                                @click="deleteCustomer"
                            >
                                Delete User
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
    customer: {
        type: Object,
        required: true
    }
});

const confirmingCustomerDeletion = ref(false);
const form = useForm({});
const confirmCustomerDeletion = () => {
    confirmingCustomerDeletion.value = true;
};

const deleteCustomer = () => {
    form.delete(route('customer.destroy', props.customer.id), {
        onFinish: () => {
            confirmingCustomerDeletion.value = false;
        },
    });
};

const closeModal = () => {
    confirmingCustomerDeletion.value = false;
};

</script>