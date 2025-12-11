<script setup>
import CreateLayout from '@/Components/CreateLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    department_id: { 
        type: [String, Number],
        default: ''
    },
    departments: { 
        type: Array,
        required: true
    }
});

const form = useForm({
    position_title: '',
    department_id: props.department_id,
});

const submit = () => {
    form.post(route('positions.store'), {
        onSuccess: () => form.reset('position_title'), 
    });
};
</script>

<template>
    <CreateLayout
        title="Create Position"
        subtitle="Add a new position within a department"
        :breadcrumb-items="[
            { label: 'Home', href: route('dashboard') },
            { label: 'Departments', href: route('departments.index') },
            { label: 'Create Position' }
        ]"
        icon="plus"
        max-width="2xl"
    >
        <form @submit.prevent="submit">
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
    </CreateLayout>
</template>