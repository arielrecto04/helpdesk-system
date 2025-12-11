<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';


const props = defineProps({
    employee: Object,
    departments: Array,
    positions: Array,
    teams: Array,
    companies: Array,
});


const form = useForm({

    first_name: props.employee.first_name,
    middle_name: props.employee.middle_name ?? '',
    last_name: props.employee.last_name,
    email: props.employee.email,
    phone_number: props.employee.phone_number ?? '', 
    company_id: props.employee.company_id ?? '',
    department_id: props.employee.department_id,
    position_id: props.employee.position_id, 
    employee_code: props.employee.employee_code ?? '',
    hire_date: props.employee.hire_date ?? '',
    teams: props.employee.teams ?? [],
});


const submit = () => {
    form.put(route('employees.update', { employee: props.employee.id }));
};

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


const fullName = computed(() => 
    [props.employee.first_name, props.employee.middle_name, props.employee.last_name]
        .filter(Boolean) 
        .join(' ')
);
</script>

<template>
    <Head :title="'Edit Employee: ' + fullName" />

    <AuthenticatedLayout>
        <div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-8">
            <!-- Header Banner -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-3 rounded-xl mr-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A7 7 0 1118.879 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-3xl text-white">Edit Employee</h2>
                                <p class="text-blue-100 text-sm mt-1">{{ fullName }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <Link :href="route('employees.show', { employee: props.employee.id })" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
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
                                <InputLabel for="company_id" value="Company (Optional)" />
                                <select
                                    id="company_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.company_id"
                                >
                                    <option value="">Select a Company</option>
                                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.company_id" />
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
                                <InputLabel for="teams" value="Helpdesk Teams (Optional)" />
                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <label v-for="t in teams" :key="t.id" class="inline-flex items-center space-x-2">
                                        <input
                                            type="checkbox"
                                            :value="t.id"
                                            v-model="form.teams"
                                            class="rounded text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                        />
                                        <span class="text-sm text-gray-700">{{ t.team_name }}</span>
                                    </label>
                                </div>
                                <InputError class="mt-2" :message="form.errors.teams" />
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