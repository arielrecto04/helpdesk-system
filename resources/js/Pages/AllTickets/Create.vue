<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    customers: Array,
    teams: Array,
    tags: { type: Array, default: () => [] },
    users: Array,
    employees: Array,
    priorities: Array,
    defaultTeamId: { type: [Number, String], default: null }, 
    stages: Array,
    currentEmployeeId: { type: Number, default: null },
});


const form = useForm({
    customer_id: null,
    subject: '',
    description: '',
    priority: 'Low',
    stage: 'Open',
    team_id: props.defaultTeamId, 
    assigned_to_employee_id: props.currentEmployeeId, 
    deadline: '',
    tag_ids: [],
});

const submit = () => {
    form.post(route('alltickets.store'), {
        onFinish: () => form.reset('subject', 'description'),
    });
};
</script>

<template>
    <Head title="Create New Ticket" />

    <AuthenticatedLayout>
        <div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-8">
            <!-- Header Banner -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-3 rounded-xl mr-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-3xl text-white">Create New Ticket</h2>
                                <p class="text-blue-100 text-sm mt-1">Fill in the details below</p>
                            </div>
                        </div>
                        <Breadcrumb :items="[
                          { label: 'Home', href: route('dashboard') },
                          { label: 'All Tickets', href: route('alltickets.index') },
                          { label: 'Create' }
                        ]" />
                    </div>
                </div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-200">
                    <div class="p-6 bg-white">
                        <form @submit.prevent="submit">
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <!-- Customer Field -->
                                    <div>
                                        <InputLabel for="customer" value="Customer" />
                                        <select id="customer" v-model="form.customer_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                            <option :value="null" disabled>Select a customer</option>
                                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.first_name }} {{ customer.last_name }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.customer_id" />
                                    </div>

                                    <!-- Subject Field -->
                                    <div class="mt-4">
                                        <InputLabel for="subject" value="Subject" />
                                        <TextInput id="subject" type="text" class="mt-1 block w-full" v-model="form.subject" required autofocus />
                                        <InputError class="mt-2" :message="form.errors.subject" />
                                    </div>

                                    <!-- Description Field -->
                                    <div class="mt-4">
                                        <InputLabel for="description" value="Description" />
                                        <Textarea id="description" class="mt-1 block w-full" v-model="form.description" required rows="8" />
                                        <InputError class="mt-2" :message="form.errors.description" />
                                    </div>
                                </div>

                                <div>
                                    <!-- Stage Field -->
                                    <div>
                                        <InputLabel for="stage" value="Stage" />
                                        <select id="stage" v-model="form.stage" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option v-for="stage in stages" :key="stage" :value="stage">{{ stage }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.stage" />
                                    </div>

                                    <!-- Priority Field -->
                                    <div class="mt-4">
                                        <InputLabel for="priority" value="Priority" />
                                        <select id="priority" v-model="form.priority" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option v-for="priority in priorities" :key="priority" :value="priority">{{ priority }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.priority" />
                                    </div>

                                    <!-- Team Field -->
                                    <div class="mt-4">
                                        <InputLabel for="team" value="Team" />
                                        <select id="team" v-model="form.team_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                            <option :value="null" disabled>Select a team</option>
                                            <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.team_name }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.team_id" />
                                    </div>

                                    <!-- Assigned To Field -->
                                    <div class="mt-4">
                                        <InputLabel for="assigned_to" value="Assigned To" />
                                        <select id="assigned_to" v-model="form.assigned_to_employee_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option :value="null">Unassigned</option>
                                            <option v-for="employee in (employees || [])" :key="employee.id" :value="employee.id">{{ employee.first_name }} {{ employee.last_name }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.assigned_to_employee_id" />
                                    </div>

                                    <!-- Deadline Field -->
                                    <div class="mt-4">
                                        <InputLabel for="deadline" value="Deadline" />
                                        <TextInput id="deadline" type="date" class="mt-1 block w-full" v-model="form.deadline" />
                                        <InputError class="mt-2" :message="form.errors.deadline" />
                                    </div>

                                    <!-- Tags Field (checkboxes for multi-select) -->
                                    <div class="mt-4">
                                        <InputLabel for="tags" value="Tags" />
                                        <div class="mt-2 grid grid-cols-2 gap-2">
                                            <label v-for="tag in tags" :key="tag.id" class="inline-flex items-center gap-2 text-sm">
                                                <input type="checkbox" :value="tag.id" v-model="form.tag_ids" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                                <span class="truncate">{{ tag.name }}</span>
                                            </label>
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.tag_ids" />
                                    </div>
                                </div>
                            </div>
                            <!-- ... Buttons ... -->
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('alltickets.index')" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>

                                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Create Ticket
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>