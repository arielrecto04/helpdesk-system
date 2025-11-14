<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
// --- INAYOS: Idinagdag ang computed at watch ---
import { computed, watch } from 'vue';

// --- INAYOS: Idinagdag ang kailangang props ---
const props = defineProps({
    employee: Object,
    departments: Array,
    positions: Array,
});

// --- INAYOS: Inayos ang form fields ---
const form = useForm({
    // Tinanggal ang _method: 'PUT'
    first_name: props.employee.first_name,
    middle_name: props.employee.middle_name ?? '',
    last_name: props.employee.last_name,
    email: props.employee.email,
    phone_number: props.employee.phone_number ?? '', // Inayos
    department_id: props.employee.department_id, // Inayos
    position_id: props.employee.position_id,     // Inayos
    employee_code: props.employee.employee_code ?? '', // Inayos
    hire_date: props.employee.hire_date ?? '',         // Inayos
});

// --- INAYOS: Inayos ang submit ---
const submit = () => {
    form.put(route('employees.update', { employee: props.employee.id }));
};

// --- INAYOS: Idinagdag ang cascading dropdown logic ---
const filteredPositions = computed(() => {
    if (!form.department_id) {
        return [];
    }
    return props.positions.filter(pos => pos.department_id == form.department_id);
});

watch(() => form.department_id, (newDeptId) => {
    if (newDeptId !== props.employee.department_id) {
        form.position_id = '';
    } else {
        form.position_id = props.employee.position_id;
    }
});
// --- End ng ayos ---

const fullName = computed(() => // INAYOS: Ginamit ang computed
    [props.employee.first_name, props.employee.middle_name, props.employee.last_name]
        .filter(Boolean) 
        .join(' ')
);
</script>

<template>
    <Head :title="'Edit Employee: ' + fullName" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Employee: <span class="font-bold">{{ fullName }}</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="max-w-2xl mx-auto">

                            <div>
                                <InputLabel for="first_name" value="First Name" />
                                <TextInput
                                    id="first_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.first_name"
                                    required
                                    autofocus
                                    autocomplete="given-name"
                                />
                                <InputError class="mt-2" :message="form.errors.first_name" />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="middle_name" value="Middle Name (Optional)" />
                                <TextInput
                                    id="middle_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.middle_name"
                                    autocomplete="additional-name"
                                />
                                <InputError class="mt-2" :message="form.errors.middle_name" />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="last_name" value="Last Name" />
                                <TextInput
                                    id="last_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.last_name"
                                    required
                                    autocomplete="family-name"
                                />
                                <InputError class="mt-2" :message="form.errors.last_name" />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.email"
                                    required
                                    autocomplete="username"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="phone_number" value="Phone (Optional)" />
                                <TextInput
                                    id="phone_number"
                                    type="tel" class="mt-1 block w-full"
                                    v-model="form.phone_number" autocomplete="tel"
                                    />
                                <InputError class="mt-2" :message="form.errors.phone_number" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="department_id" value="Department (Optional)" />
                                <select
                                    id="department_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.department_id"
                                >
                                    <option value="">Select a Department</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.department_name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.department_id" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="position_id" value="Position (Optional)" />
                                <select
                                    id="position_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.position_id"
                                    :disabled="!form.department_id"
                                >
                                    <option value="">{{ form.department_id ? 'Select a Position' : 'Please select a department first' }}</option>
                                    <option v-for="pos in filteredPositions" :key="pos.id" :value="pos.id">
                                        {{ pos.position_title }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.position_id" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="employee_code" value="Employee Code (Optional)" />
                                <TextInput
                                    id="employee_code"
                                    type="text" class="mt-1 block w-full"
                                    v-model="form.employee_code"
                                    autocomplete="off"
                                    />
                                <InputError class="mt-2" :message="form.errors.employee_code" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="hire_date" value="Hire Date (Optional)" />
                                <TextInput
                                    id="hire_date"
                                    type="date" class="mt-1 block w-full"
                                    v-model="form.hire_date"
                                    autocomplete="off"
                                    />
                                <InputError class="mt-2" :message="form.errors.hire_date" />
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('employees.index')" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>
                                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Employee
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>