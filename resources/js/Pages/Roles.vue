<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    roles: {
        type: [Object, Array],
        required: true,
    },
    pageTitle: {
        type: String,
        default: 'User Roles',
    }
});

const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

const dataRoles = computed(() => {
    return props.roles && props.roles.data ? props.roles.data : (Array.isArray(props.roles) ? props.roles : []);
});

const viewRole = (roleId) => {
    router.visit(route('roles.show', { role: roleId }));
};
</script>

<template>
    <Head :title="pageTitle" />
    <AuthenticatedLayout>
        <div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-8">
            <!-- Header Section -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-3 rounded-xl mr-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-3xl text-white">{{ pageTitle }}</h2>
                                <p class="text-blue-100 text-sm mt-1">Overview of all user roles</p>
                            </div>
                        </div>
                        <Link v-if="userPermissions && userPermissions.includes('create_roles')" :href="route('roles.create')" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Role
                        </Link>
                    </div>
                </div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-200">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-gray-100 to-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Description</th>
                                        <!-- <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Created</th> -->
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr v-if="dataRoles.length === 0">
                                        <td colspan="4" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 p-6 rounded-full mb-4">
                                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                    </svg>
                                                </div>
                                                <p class="text-lg font-semibold text-gray-600 mb-2">No Roles Found</p>
                                                <p class="text-sm text-gray-500">There are no roles to display.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-for="role in dataRoles" :key="role.id" @click="viewRole(role.id)" class="group hover:bg-transparent cursor-pointer transition-all duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap border-l-4 border-transparent group-hover:border-indigo-500 group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-indigo-100 to-blue-100 text-indigo-700 border border-indigo-200">#{{ role.id }}</span>
                                        </td>
                                        <td class="px-6 py-4 group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50">
                                            <div class="flex items-center">
                                                <div class="bg-gradient-to-br from-purple-100 to-indigo-100 p-2 rounded-lg mr-3">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                </div>
                                                <div class="text-sm font-semibold text-gray-900">{{ role.name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50">
                                            <div class="text-sm text-gray-700">{{ role.description ?? 'N/A' }}</div>
                                        </td>
                                        <!-- <td class="px-6 py-4 whitespace-nowrap group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50">
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ role.created_at ? new Date(role.created_at).toLocaleDateString() : 'N/A' }}
                                            </div>
                                        </td> -->
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="props.roles && props.roles.links && props.roles.links.length > 3" class="mt-6">
                            <Pagination :links="props.roles.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>