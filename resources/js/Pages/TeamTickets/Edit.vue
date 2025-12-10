<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    ticket: Object,
    customers: Array,
    teams: Array,
    employees: Array,
    priorities: Array,
    stages: Array,
    tags: { type: Array, default: () => [] },
});


const formattedDeadline = props.ticket.deadline ? props.ticket.deadline.split(' ')[0] : '';

const form = useForm({
    customer_id: props.ticket.customer_id,
    subject: props.ticket.subject,
    description: props.ticket.description,
    priority: props.ticket.priority,
    stage: props.ticket.stage,
    team_id: props.ticket.team_id,
    assigned_to_employee_id: props.ticket.assigned_to_employee_id,
    deadline: props.ticket.deadline,
    deadline: formattedDeadline,
    tag_ids: props.ticket.tags ? props.ticket.tags.map(t => t.id) : [],
});

const submit = () => {
    form.put(route('teamtickets.update', props.ticket.id), {
        onSuccess: () => {
        },
    });
};
</script>

<template>
    <Head :title="`Edit Ticket #${ticket.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Ticket #{{ ticket.id }} - {{ ticket.subject }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Left Column -->
                                <div>
                                    <div>
                                        <InputLabel for="customer" value="Customer" />
                                        <select id="customer" v-model="form.customer_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                            <option :value="null" disabled>Select a customer</option>
                                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.first_name }} {{ customer.last_name }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.customer_id" />
                                    </div>

                                    <div>
                                        <InputLabel for="subject" value="Subject" />
                                        <TextInput id="subject" type="text" class="mt-1 block w-full" v-model="form.subject" required autofocus />
                                        <InputError class="mt-2" :message="form.errors.subject" />
                                    </div>

                                    <div class="mt-4">
                                        <InputLabel for="description" value="Description" />
                                        <Textarea id="description" class="mt-1 block w-full" v-model="form.description" required rows="8" />
                                        <InputError class="mt-2" :message="form.errors.description" />
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div>
                                    <div>
                                        <InputLabel for="stage" value="Stage" />
                                        <select id="stage" v-model="form.stage" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option v-for="stage in stages" :key="stage" :value="stage">{{ stage }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.stage" />
                                    </div>

                                    <div class="mt-4">
                                        <InputLabel for="priority" value="Priority" />
                                        <select id="priority" v-model="form.priority" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option v-for="priority in priorities" :key="priority" :value="priority">{{ priority }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.priority" />
                                    </div>

                                    <div class="mt-4">
                                        <InputLabel for="team" value="Team" />
                                        <select id="team" v-model="form.team_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                            <option :value="null" disabled>Select a team</option>
                                            <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.team_name }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.team_id" />
                                    </div>

                                    <div class="mt-4">
                                        <InputLabel for="assigned_to" value="Assigned To" />
                                        <select id="assigned_to" v-model="form.assigned_to_employee_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option :value="null">Unassigned</option>
                                            <option v-for="employee in (employees || [])" :key="employee.id" :value="employee.id">{{ employee.first_name }} {{ employee.last_name }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.assigned_to_employee_id" />
                                    </div>

                                    <div class="mt-4">
                                        <InputLabel for="deadline" value="Deadline" />
                                        <TextInput id="deadline" type="date" class="mt-1 block w-full" v-model="form.deadline" />
                                        <InputError class="mt-2" :message="form.errors.deadline" />
                                    </div>

                                    <!-- Tags Field (checkboxes) -->
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

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('teamtickets.show', ticket.id)" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>

                                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Ticket
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>