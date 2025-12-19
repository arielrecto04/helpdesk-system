<template>
    <ShowLayout
        :title="`${customer.first_name} ${customer.last_name}`"
        subtitle="Customer Information"
        icon="user"
        :actions="userPermissions && (userPermissions.includes('edit_customers') || userPermissions.includes('delete_customers'))"
    >
        <template #actions>
            <Link v-if="userPermissions.includes('edit_customers')" :href="route('customers.edit', customer.id)" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-4M11 4l10 10M11 4v6h6"/>
                </svg>
                Edit
            </Link>
            <DangerButton v-if="userPermissions.includes('delete_customers')" @click="confirmCustomerDeletion" class="bg-white/20 text-white hover:bg-white/30 rounded-xl px-6 py-3">
                Delete
            </DangerButton>
        </template>

        <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-400">
            <div class="p-6 text-gray-900">
                <div class="flex items-center mb-6 pb-4 border-b">
                    <div class="bg-gradient-to-br from-indigo-500 to-blue-600 p-3 rounded-xl mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-xl text-gray-800">Customer Details</h2>
                        <p class="text-sm text-gray-500">ID: #{{ customer.id }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-center mb-4">
                            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-2 rounded-lg mr-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">General Information</h3>
                        </div>
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
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Company</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ customer.company_name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Has Account?</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span v-if="customer.has_account" class="px-3 py-1.5 inline-flex items-center text-xs leading-5 font-bold rounded-xl shadow-sm bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Account Exists
                                    </span>
                                    <Link
                                        v-else-if="userPermissions && userPermissions.includes('create_users')"
                                        :href="route('users.create', { customer: customer.id })"
                                        class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none"
                                    >
                                        Create Account
                                    </Link>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <div class="flex items-center mb-4">
                            <div class="bg-gradient-to-br from-teal-500 to-emerald-600 p-2 rounded-lg mr-2">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
                        </div>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ new Date(customer.created_at).toLocaleString() }}</dd>
                            </div>
                            <!-- <div>
                                <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ new Date(customer.updated_at).toLocaleString() }}</dd>
                            </div> -->
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
    </ShowLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ShowLayout from '@/Components/ShowLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
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
const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];
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