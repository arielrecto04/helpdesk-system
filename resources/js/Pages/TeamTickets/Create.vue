<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    customers: Array,
    teams: Array,
    users: Array,
    priorities: Array,
    defaultTeamId: { type: [Number, String], default: null },
    stages: Array,
});

const authUser = usePage().props.auth.user;

const form = useForm({
    customer_id: null,
    subject: '',
    description: '',
    priority: 'Low',
    stage: 'Open',
    team_id: props.defaultTeamId,
    assigned_to_employee_id: authUser.employee_id ?? null,
    deadline: '',
});

const storePrefix = route().current('teamtickets.*') ? 'teamtickets' : route().current('alltickets.*') ? 'alltickets' : 'mytickets';

const submit = () => {
    form.post(route(`${storePrefix}.store`), {
        onFinish: () => form.reset('subject', 'description'),
    });
};
</script>

<template>
    <Head title="Create New Ticket" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Ticket
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
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
                                        <select id="assigned_to" v-model="form.assigned_to_employee_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option :value="null">Unassigned</option>
                                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.first_name }} {{ user.last_name }}</option>
                                        </select>
                                        <InputError class="mt-2" :message="form.errors.assigned_to_employee_id" />
                                    </div>

                                    <div class="mt-4">
                                        <InputLabel for="deadline" value="Deadline" />
                                        <TextInput id="deadline" type="date" class="mt-1 block w-full" v-model="form.deadline" />
                                        <InputError class="mt-2" :message="form.errors.deadline" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route(`${storePrefix}.index`)" class="text-sm text-gray-600 hover:text-gray-900 underline">Cancel</Link>

                                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Create Ticket</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

let form = reactive({ subject: '', description: '' });
const submit = () => console.log('create team ticket', form);
</script>

<template>
  <Head title="Create Team Ticket" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Team Ticket</h2>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <div class="space-y-4">
            <input v-model="form.subject" placeholder="Subject" class="w-full border rounded p-2" />
            <textarea v-model="form.description" placeholder="Description" class="w-full border rounded p-2"></textarea>
            <button @click.prevent="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Create</button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
