<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    position: {
        type: Object,
        required: true
    },
    departments: { 
        type: Array,
        required: true
    }
});

const form = useForm({
    position_title: props.position.position_title,
    department_id: props.position.department_id,
});

const submit = () => {
    form.put(route('positions.update', { position: props.position.id }));
};
</script>

<template>
    <Head :title="'Edit Position: ' + position.position_title" />
    <AuthenticatedLayout>
        <div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-8">
            <!-- Header Banner -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-3 rounded-xl mr-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-3xl text-white">Edit Position</h2>
                                <p class="text-blue-100 text-sm mt-1">{{ position.position_title }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <Link :href="route('departments.show', { department: position.department_id })" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-400">
                    <div class="p-6 bg-white">
                        <form @submit.prevent="submit" class="max-w-2xl mx-auto">
                            <div>
                                <InputLabel for="position_title" value="Position Name" />
                                <TextInput
                                    id="position_title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.position_title"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.position_title" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="department_id" value="Department" />
                                <select
                                    id="department_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.department_id"
                                    required
                                >
                                    <option value="">Select a Department</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.department_name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.department_id" />
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('departments.show', { department: position.department_id })" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>
                                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Position
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>