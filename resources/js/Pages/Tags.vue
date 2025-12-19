<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    tags: {
        type: [Object, Array],
        required: true,
    },
    pageTitle: {
        type: String,
        default: 'Tags',
    }
});

const page = usePage();
const userPermissions = page.props.auth && page.props.auth.user && page.props.auth.user.permissions ? page.props.auth.user.permissions : [];

const dataTags = computed(() => {
    return props.tags && props.tags.data ? props.tags.data : (Array.isArray(props.tags) ? props.tags : []);
});

const viewTag = (TagId) => {
    router.visit(route('tags.show', { tag: TagId }));
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-3xl text-white">{{ pageTitle }}</h2>
                                <p class="text-blue-100 text-sm mt-1">Overview of all tags</p>
                            </div>
                        </div>
                        <Link v-if="userPermissions && userPermissions.includes('create_tags')" :href="route('tags.create')" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Tag
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
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Tag Name</th>
                                        <!-- <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Created</th> -->
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr v-if="dataTags.length === 0">
                                        <td colspan="3" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 p-6 rounded-full mb-4">
                                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                                    </svg>
                                                </div>
                                                <p class="text-lg font-semibold text-gray-600 mb-2">No Tags Found</p>
                                                <p class="text-sm text-gray-500">There are no tags to display.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-for="tag in dataTags" :key="tag.id" @click="viewTag(tag.id)" class="group hover:bg-transparent cursor-pointer transition-all duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap border-l-4 border-transparent group-hover:border-indigo-500 group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-indigo-100 to-blue-100 text-indigo-700 border border-indigo-200">#{{ tag.id }}</span>
                                        </td>
                                        <td class="px-6 py-4 group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50">
                                            <div class="flex items-center">
                                                <div class="bg-gradient-to-br from-purple-100 to-indigo-100 p-2 rounded-lg mr-3">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                    </svg>
                                                </div>
                                                <div class="text-sm font-semibold text-gray-900">{{ tag.name }}</div>
                                            </div>
                                        </td>
                                        <!-- <td class="px-6 py-4 whitespace-nowrap group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-indigo-50">
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ tag.created_at ? new Date(tag.created_at).toLocaleDateString() : 'N/A' }}
                                            </div>
                                        </td> -->
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="props.tags && props.tags.links && props.tags.links.length > 3" class="mt-6">
                            <Pagination :links="props.tags.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>