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
                                <div class="flex space-x-2" v-if="userPermissions && (userPermissions.includes('edit_roles') || userPermissions.includes('delete_roles'))">
                                    <Link v-if="userPermissions.includes('edit_roles')" :href="route('roles.edit', role.id)" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Edit
                                    </Link>
                                    <DangerButton v-if="userPermissions.includes('delete_roles')" @click="confirmRoleDeletion">Delete</DangerButton>
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
                                <div v-if="role.permissions.length > 0" class="space-y-4">
                                    <div v-for="[category, permissions] in categorizedPermissions" :key="category" class="border border-gray-200 rounded-lg p-3">
                                        <h4 class="font-semibold text-sm text-gray-700 mb-2">{{ category }}</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="permission in permissions" :key="permission.id" class="inline-block bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                {{ permission.name }}
                                            </span>
                                        </div>
                                    </div>
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
import { ref, computed } from 'vue';
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
const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

const categorizedPermissions = computed(() => {
    const categories = {
        
        'My Tickets': [],
        'All Tickets': [],
        'Team Tickets': [],
        'Users': [],
        'Roles': [],
        'Permissions': [],
        'Customers': [],
        'Canned Responses': [],
        'Logs': [],
        'Customers Rating': [],
        'Ticket Analysis': [],
        'Employees': [],
        'Departments': [],
        'Positions': [],
        'Helpdesk Teams': [],
        'Tags': [],
        'Companies': [],
        'Can View Others': [],
        'Customer Dashboard': [],
        'General': [],
    };

    props.role.permissions.forEach(permission => {
        const raw = (permission.name || '').toLowerCase();

        if (raw.includes('customer_dashboard') || raw.includes('customer-dashboard') || raw.includes('customer dashboard') || raw.includes('cust_dashboard') || raw.includes('cust-dashboard')) {
            categories['Customer Dashboard'].push(permission);
        } else if (raw.includes('dashboard') || raw.includes('profile') || raw.includes('settings')) {
            categories['General'].push(permission);
        } else if (raw.includes('mytickets')) {
            categories['My Tickets'].push(permission);
        } else if (raw.includes('allticket') || raw.includes('all_ticket') || raw.includes('all_tickets')) {
            categories['All Tickets'].push(permission);
        } else if (raw.includes('helpdeskteams')) {
            categories['Helpdesk Teams'].push(permission);
        } else if (raw.includes('teamticket') || raw.includes('team_ticket') || raw.includes('team_ticket') ) {
            categories['Team Tickets'].push(permission);
        } else if (raw.includes('can_view') || raw.includes('view_other') || raw.includes('view_others')) {
            categories['Can View Others'].push(permission);
        } else if (raw.includes('user') || raw.includes('users')) {
            categories['Users'].push(permission);
        } else if (raw.includes('role')) {
            categories['Roles'].push(permission);
        } else if (raw.includes('permission')) {
            categories['Permissions'].push(permission);
        } else if (raw.includes('customer_rating') || raw.includes('customerrating') || raw.includes('customer_rating')) {
            categories['Customers Rating'].push(permission);
        } else if (raw.includes('canned') || raw.includes('response') || raw.includes('canned_response') || raw.includes('canned-responses')) {
            categories['Canned Responses'].push(permission);
        } else if (raw.includes('customer_dashboard')) {
            categories['Customer Dashboard'].push(permission);
        } else if (raw.includes('log') || raw.includes('logs') || raw.includes('activity') || raw.includes('audit') || raw.includes('history')) {
            categories['Logs'].push(permission);
        } else if (raw.includes('customer') || raw.includes('customers')) {
            categories['Customers'].push(permission);
        } else if (raw.includes('ticket_analysis') || raw.includes('ticketanalysis')) {
            categories['Ticket Analysis'].push(permission);
        } else if (raw.includes('employee') || raw.includes('employees')) {
            categories['Employees'].push(permission);
        } else if (raw.includes('department') || raw.includes('departments')) {
            categories['Departments'].push(permission);
        } else if (raw.includes('position') || raw.includes('positions')) {
            categories['Positions'].push(permission);
        } else if (raw.includes('helpdesk')) {
            categories['Helpdesk Teams'].push(permission);
        } else if (raw.includes('tag')) {
            categories['Tags'].push(permission);
        } else if (raw.includes('company') || raw.includes('companies') || raw.includes('compan')) {
            categories['Companies'].push(permission);
        } else {
            categories['General'].push(permission);
        }
    });
    return Object.entries(categories).filter(([_, perms]) => perms.length > 0);
});

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