<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';


const props = defineProps({
    department: Object,
});

const form = useForm({
    department_name: props.department.department_name,
});

const submit = () => {
    form.put(route('departments.update', { department: props.department.id }));
};
</script>

<template>
    <Head :title="'Edit Department: ' + department.department_name" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Department: <span class="font-bold">{{ department.department_name }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="max-w-2xl mx-auto"> <div>
                                <InputLabel for="department_name" value="Department Name" />
                                <TextInput
                                    id="department_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.department_name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.department_name" />
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('departments.show', { department: department.id })" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>
                                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Department
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>