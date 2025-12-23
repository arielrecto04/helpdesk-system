<script setup>
import { computed, ref } from 'vue';
import CreateLayout from '@/Components/CreateLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    all_permissions: Array,
});

const form = useForm({
    name: '',
    description: '',
    permissions: [],
});

const showCategorySelectors = ref(true);

const isCategoryAllChecked = (permissions) => {
    if (!permissions || permissions.length === 0) return false;
    return permissions.every(p => form.permissions.includes(p.id));
};

const toggleCategory = (permissions) => {
    const ids = permissions.map(p => p.id);
    const allChecked = ids.every(id => form.permissions.includes(id));
    if (allChecked) {
        form.permissions = form.permissions.filter(id => !ids.includes(id));
    } else {
        const missing = ids.filter(id => !form.permissions.includes(id));
        form.permissions = [...form.permissions, ...missing];
    }
};

// Categorize permissions
const categorizedPermissions = computed(() => {
    const categories = {
        'My Tickets': [],
        'Ticket Chat': [],
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

    props.all_permissions.forEach(permission => {
        const raw = (permission.name || '').toLowerCase();

        if ((raw.includes('chat') || raw.includes('ticketmessage') || (raw.includes('message') && raw.includes('ticket'))) ) {
            categories['Ticket Chat'].push(permission);
            return;
        }
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

const submit = () => {
    form.post(route('roles.store'));
};
</script>

<template>
    <CreateLayout
        title="Create Role"
        subtitle="Define a new role with specific permissions"
        :breadcrumb-items="[
            { label: 'Home', href: route('dashboard') },
            { label: 'Roles', href: route('roles.index') },
            { label: 'Create' }
        ]"
        icon="plus"
        max-width="7xl"
    >
        <form @submit.prevent="submit">
            <!-- Role Name -->
            <div>
                <InputLabel for="name" value="Role Name" />
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autofocus
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                </div>
            </div>

            <!-- Role Description -->
            <div class="mt-4">
                <InputLabel for="description" value="Description" />
                <TextInput
                    id="description"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.description"
                />
                <InputError class="mt-2" :message="form.errors.description" />
            </div>

            <!-- Permissions Checkboxes -->
            <div class="mt-6">
                <h3 class="font-semibold text-lg text-gray-800 mb-4">Permissions</h3>
                <div class="space-y-6">
                    <div v-for="[category, permissions] in categorizedPermissions" :key="category" class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-md text-gray-700">{{ category }}</h4>
                            <div v-if="showCategorySelectors" class="flex items-center">
                                <input
                                    type="checkbox"
                                    :id="'cat_' + category.replace(/\s+/g, '_')"
                                    :checked="isCategoryAllChecked(permissions)"
                                    @change="toggleCategory(permissions)"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <label :for="'cat_' + category.replace(/\s+/g, '_')" class="ms-2 text-sm text-gray-600">Select all</label>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-3">
                            <div v-for="permission in permissions" :key="permission.id" class="flex items-start">
                                <input
                                    type="checkbox"
                                    :id="'perm_' + permission.id"
                                    :value="permission.id"
                                    v-model="form.permissions"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mt-1"
                                />
                                <label :for="'perm_' + permission.id" class="ms-2 text-sm text-gray-600">
                                    <span class="font-medium">{{ permission.name }}</span>
                                    <span class="block text-xs text-gray-500">{{ permission.description }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.permissions" />
            </div>
                            
            <!-- Buttons -->
            <div class="flex items-center justify-end mt-6">
                <Link :href="route('roles.index')" class="text-sm text-gray-600 hover:text-gray-900 underline">
                    Cancel
                </Link>
                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Create Role
                </PrimaryButton>
            </div>
        </form>
    </CreateLayout>
</template>