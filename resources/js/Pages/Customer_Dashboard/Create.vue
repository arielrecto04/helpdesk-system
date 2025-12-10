<script setup>
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    teams: { type: Array, default: () => [] },
    priorities: { type: Array, default: () => ['Low', 'Medium', 'High', 'Urgent'] },
    customer_id: { type: [Number, null], default: null },
    tags: { type: Array, default: () => [] },
});

const form = useForm({
    customer_id: props.customer_id,
    subject: '',
    description: '',
    priority: props.priorities && props.priorities.length ? props.priorities[1] : 'Medium',
    team_id: props.teams && props.teams.length ? props.teams[0].id : null,
    deadline: '',
    tag_ids: [],
});

const submitTicket = () => {
    if (!form.team_id) {
        form.setError('team_id', 'Please select a team.');
        return;
    }

    // Use the customer-specific store route so this action doesn't require admin permissions
    form.post(route('customer.tickets.store'), {
        onSuccess: () => {
            form.reset('subject', 'description', 'deadline');
            router.visit(route('customer.dashboard'));
        },
    });
};

const emit = defineEmits(['close']);

const cancel = () => {
    form.reset('subject', 'description', 'deadline');
    form.clearErrors && form.clearErrors();
    emit('close');
};
</script>

<template>
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800">Create Ticket</h3>
                        <p class="text-sm text-slate-500 mt-1">Tell us what's happening. Our support team will get back to you soon.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-400">Quick tip</p>
                        <p class="text-sm text-slate-600">Provide a concise subject and clear description for faster response.</p>
                    </div>
                </div>

                <form @submit.prevent="submitTicket" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="subject" value="Subject" />
                        <TextInput id="subject" v-model="form.subject" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50" required />
                        <InputError class="mt-2" :message="form.errors.subject" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Description" />
                        <Textarea id="description" v-model="form.description" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50" rows="5" required />
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <InputLabel value="Team" />
                            <select v-model.number="form.team_id" class="mt-2 block w-full rounded-xl border-slate-200 bg-white py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option :value="null">-- Select team --</option>
                                <option v-for="team in props.teams" :key="team.id" :value="team.id">{{ team.team_name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.team_id" />
                        </div>

                        <div>
                            <InputLabel value="Priority" />
                            <select v-model="form.priority" class="mt-2 block w-full rounded-xl border-slate-200 bg-white py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option v-for="p in props.priorities" :key="p">{{ p }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.priority" />
                        </div>

                        <div>
                            <InputLabel for="deadline" value="Deadline (optional)" />
                            <TextInput id="deadline" type="date" v-model="form.deadline" class="mt-2 block w-full rounded-xl border-slate-200 bg-white py-2 px-3" />
                            <InputError class="mt-2" :message="form.errors.deadline" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <InputLabel for="tags" value="Tags (optional)" />
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <label v-for="tag in props.tags" :key="tag.id" class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" :value="tag.id" v-model="form.tag_ids" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                <span class="truncate">{{ tag.name }}</span>
                            </label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.tag_ids" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-sm text-slate-500">
                            <p class="font-medium">Need help?</p>
                            <p class="text-xs">Check our <a href="#" class="text-blue-600 underline">Support FAQ</a> for common issues.</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <input type="hidden" v-model="form.customer_id" />
                            <button type="button" @click="cancel" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">Cancel</button>
                            <PrimaryButton type="submit" :disabled="form.processing" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700">
                                <span v-if="!form.processing">Send Ticket</span>
                                <span v-else>Sending...</span>
                            </PrimaryButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
