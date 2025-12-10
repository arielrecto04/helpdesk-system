<script setup>

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    companies: Array,
});
const viewCompany = (companyId) => {
    router.visit(route('companies.show', companyId));
};
const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

</script>

<template>
    <AuthenticatedLayout>
        <div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-8">
            <!-- Header Banner -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-3 rounded-xl mr-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4zm0 8l9 4 9-4m-9 4V11"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-3xl text-white">Company</h2>
                                <p class="text-blue-100 text-sm mt-1">Manage company records</p>
                            </div>
                        </div>
                                                <div class="flex items-center space-x-3">
                                                        <Link v-if="userPermissions && userPermissions.includes('create_companies')" :href="route('companies.create')" class="inline-flex items-center px-4 py-2 bg-white/20 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                                </svg>
                                                                Create Company
                                                        </Link>
                                                        <Breadcrumb :items="[
                                                            { label: 'Home', href: route('dashboard') },
                                                            { label: 'Company' }
                                                        ]" />
                                                </div>
                    </div>
                </div>
            </div>
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="companies.length === 0">
                                        <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No companies found.</td>
                                    </tr>
                                    <tr v-for="comapny in companies" :key="comapny.id"@click="viewCompany(comapny.id)" class="hover:bg-gray-100 cursor-pointer">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ comapny.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ comapny.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ comapny.address }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ comapny.phone }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ comapny.email }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>