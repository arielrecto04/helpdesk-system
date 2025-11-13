<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// --- INAYOS: Inayos ang props ---
const props = defineProps({
    // Ito ang pre-selected ID galing sa URL (e.g., ?department=1)
    department_id: { 
        type: [String, Number], // Pwedeng string galing URL
        default: ''
    },
    // Ito ang listahan ng lahat ng departments para sa dropdown
    departments: { 
        type: Array,
        required: true
    }
});

// --- INAYOS: Inayos ang form fields ---
const form = useForm({
    position_title: '', // 1. Dapat 'position_title'
    department_id: props.department_id, // 2. Gagamitin ang prop bilang default value
});

const submit = () => {
    form.post(route('positions.store'), {
        // 3. Inayos ang field na ire-reset
        onSuccess: () => form.reset('position_title'), 
    });
};
</script>

<template>
    <Head title="Create New Position" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Position
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
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
                                    <option value="" :disabled="props.department_id">Select a Department</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.department_name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.department_id" />
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('departments.show', { department: props.department_id })" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>
                                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Create Position
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>