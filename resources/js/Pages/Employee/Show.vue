<template>
    <ShowLayout
        :title="`${employee.first_name} ${employee.last_name}`"
        subtitle="Employee Information"
        icon="user"
        :actions="userPermissions && (userPermissions.includes('edit_employees') || userPermissions.includes('delete_employees'))"
    >
        <template #actions>
            <Link v-if="userPermissions.includes('edit_employees')" :href="route('employees.edit', employee.id)" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-4M11 4l10 10M11 4v6h6"/>
                </svg>
                Edit
            </Link>
            <DangerButton v-if="userPermissions.includes('delete_employees')" @click="confirmEmployeeDeletion" class="bg-white/20 text-white hover:bg-white/30 rounded-xl px-6 py-3">
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
                        <h2 class="font-bold text-xl text-gray-800">Employee Details</h2>
                        <p class="text-sm text-gray-500">ID: #{{ employee.id }}</p>
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
                                        <dd class="mt-1 text-sm text-gray-900 font-mono">#{{ employee.id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">First Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ employee.first_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Middle Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ employee.middle_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Last Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ employee.last_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ employee.email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Company</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ employee.company_name ?? 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ employee.phone_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Department</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ employee.department_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Position</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ employee.position_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Has Account?</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <span v-if="employee.has_account" class="px-3 py-1.5 inline-flex items-center text-xs leading-5 font-bold rounded-xl shadow-sm bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Account Exists
                                            </span>
                                            <Link
                                                v-else-if="userPermissions && userPermissions.includes('create_users')"
                                                :href="route('employees.createAccount', { employee: employee.id })"
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
                                </div>
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Employee Code</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ employee.employee_code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Helpdesk Teams</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <template v-if="employee.teams && employee.teams.length">
                                                <div class="flex flex-wrap gap-2">
                                                    <span v-for="team in employee.teams" :key="team" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-xl bg-gradient-to-r from-indigo-100 to-blue-100 text-indigo-800 border border-indigo-200">
                                                        {{ team }}
                                                    </span>
                                                </div>
                                            </template>
                                            <template v-else>
                                                <span class="text-sm text-gray-500">No teams assigned.</span>
                                            </template>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Hire Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ employee.hire_date ? new Date(employee.hire_date).toLocaleDateString() : 'N/A' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ new Date(employee.created_at).toLocaleString() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ new Date(employee.updated_at).toLocaleString() }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

        <Modal :show="confirmingEmployeeDeletion" @close="closeModal">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900"> 
                            Are you sure you want to delete this employee?
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            This action cannot be undone. This will permanently delete the employee and all its related data.
                        </p>
                        <div class="mt-6 flex justify-end">
                            <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                            <DangerButton
                                class="ms-3"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                @click="deleteEmployee"
                            >
                                Delete Employee
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
    employee: {
        type: Object,
        required: true
    }
});

const confirmingEmployeeDeletion = ref(false);
const form = useForm({});
const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

const confirmEmployeeDeletion = () => {
    confirmingEmployeeDeletion.value = true;
};

const deleteEmployee = () => {
    form.delete(route('employees.destroy', props.employee.id), {
        onFinish: () => {
            confirmingEmployeeDeletion.value = false;
        },
    });
};

const closeModal = () => {
    confirmingEmployeeDeletion.value = false;
};


</script>