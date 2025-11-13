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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Position: <span class="font-bold">{{ position.position_title }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="max-w-2xl mx-auto"> <div>
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