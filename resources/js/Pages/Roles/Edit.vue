<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    role: Object, 
    all_permissions: Array,
});

const form = useForm({
    name: props.role.name,
    description: props.role.description,
    permissions: (props.role.permissions ?? []).map(p => p.id),
});

// Categorize permissions
const categorizedPermissions = computed(() => {
    const categories = {
        'General': [],
        'My Tickets': [],
        'All Tickets': [],
        'Team Tickets': [],
        'Users': [],
        'Roles': [],
        'Permissions': [],
        'Customers': [],
        'Employees': [],
        'Departments': [],
        'Positions': [],
        'Helpdesk Teams': [],
        'Tags': [],
        'Companies': [],
        'Reports': [],
        'Others': [],
    };

    props.all_permissions.forEach(permission => {
        const raw = (permission.name || '').toLowerCase();
        // Strip common action prefixes so we can classify by resource base
        const name = raw.replace(/^(view_|show_|create_|edit_|delete_|can_)/, '');

        if (name.includes('dashboard') || name.includes('profile') || raw.includes('settings')) {
            categories['General'].push(permission);
        } else if (name.includes('myticket')) {
            categories['My Tickets'].push(permission);
        } else if (name.includes('allticket')) {
            categories['All Tickets'].push(permission);
        } else if (name.includes('teamticket')) {
            categories['Team Tickets'].push(permission);
        } else if (name.includes('user')) {
            categories['Users'].push(permission);
        } else if (name.includes('role')) {
            categories['Roles'].push(permission);
        } else if (name.includes('permission')) {
            categories['Permissions'].push(permission);
        } else if (name.includes('customer')) {
            categories['Customers'].push(permission);
        } else if (name.includes('employee')) {
            categories['Employees'].push(permission);
        } else if (name.includes('department')) {
            categories['Departments'].push(permission);
        } else if (name.includes('position') || name.includes('positions')) {
            categories['Positions'].push(permission);
        } else if (name.includes('helpdesk')) {
            categories['Helpdesk Teams'].push(permission);
        } else if (name.includes('tag')) {
            categories['Tags'].push(permission);
        } else if (name.includes('compan') || name.includes('company')) {
            categories['Companies'].push(permission);
        } else if (name.includes('report') || raw.includes('ticket_analysis') || raw.includes('customer_ratings')) {
            categories['Reports'].push(permission);
        } else {
            categories['Others'].push(permission);
        }
    });
 
    return Object.entries(categories).filter(([_, perms]) => perms.length > 0);
});

const submit = () => {
    form.put(route('roles.update', props.role.id));
};
</script>

<template>
    <Head :title="'Edit Role: ' + role.name" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Role: <span class="font-bold">{{ role.name }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">

                            <div>
                                <InputLabel for="name" value="Role Name" />
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

                            <div class="mt-6">
                                <h3 class="font-semibold text-lg text-gray-800 mb-4">Permissions</h3>
                                <div class="space-y-6">
                                    <div v-for="[category, permissions] in categorizedPermissions" :key="category" class="border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-semibold text-md text-gray-700 mb-3">{{ category }}</h4>
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
                            
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('roles.index')" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>
                                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Role
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>