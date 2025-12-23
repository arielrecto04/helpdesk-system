<script setup>
import CreateLayout from '@/Components/CreateLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    customers: Array,
    teams: Array,
    employees: Array,
    tags: { type: Array, default: () => [] },
    priorities: Array,
    defaultTeamId: { type: [Number, String], default: null },
    stages: Array,
    currentEmployeeId: { type: Number, default: null },
});

const page = usePage();
const authUser = page.props.auth ? page.props.auth.user : null;

const form = useForm({
    customer_id: null,
    subject: '',
    description: '',
    priority: 'Low',
    stage: 'Open',
    team_id: (props.defaultTeamId !== null && props.defaultTeamId !== undefined) ? Number(props.defaultTeamId) : null,
    employee_id: props.currentEmployeeId ?? (authUser && authUser.employee_id ? authUser.employee_id : null),
    deadline: '',
    tag_ids: [],
});

const storePrefix = route().current('teamtickets.*') ? 'teamtickets' : route().current('alltickets.*') ? 'alltickets' : 'mytickets';

const submit = () => {
    form.post(route(`${storePrefix}.store`), {
        onFinish: () => form.reset('subject', 'description'),
    });
};
</script>

<template>
    <CreateLayout
        title="Create New Ticket"
        subtitle="Submit a support ticket for your team"
        :breadcrumb-items="[
            { label: 'Home', href: route('dashboard') },
            { label: 'Team Tickets', href: route('teamtickets.index') },
            { label: 'Create' }
        ]"
        icon="ticket"
        max-width="full"
    >
        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                        <select id="assigned_to" v-model="form.employee_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option :value="null">Unassigned</option>
                                            <option v-for="employee in (employees || [])" :key="employee.id" :value="employee.id">{{ employee.first_name }} {{ employee.last_name }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.employee_id" />
                                    </div>

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

            <div class="flex items-center justify-end mt-6">
                <Link :href="route(`${storePrefix}.index`)" class="text-sm text-gray-600 hover:text-gray-900 underline">Cancel</Link>
                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Create Ticket</PrimaryButton>
            </div>
        </form>
    </CreateLayout>
</template>
