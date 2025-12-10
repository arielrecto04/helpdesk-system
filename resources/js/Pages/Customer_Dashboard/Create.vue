<script setup>
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const attachments = ref(null);

const descriptionLength = computed(() => (form.description || '').length);

const isTagSelected = (id) => {
    return form.tag_ids && form.tag_ids.includes(id);
};

const toggleTag = (id) => {
    const idx = form.tag_ids.indexOf(id);
    if (idx === -1) form.tag_ids.push(id);
    else form.tag_ids.splice(idx, 1);
};

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
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-[0_18px_60px_-24px_rgba(2,6,23,0.5)] border border-slate-200 overflow-hidden">
            <div class="px-8 py-8">
                <div class="flex items-start justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center text-white">
                                <!-- ticket icon -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 0a2 2 0 100-4H7a2 2 0 100 4m0 0v6m6-6v6" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-800">Create Ticket</h3>
                                <p class="text-sm text-slate-500 mt-1">Tell us what's happening. Our support team will get back to you soon.</p>
                            </div>
                        </div>
                    </div>
                    <div class="hidden sm:block w-56 text-right">
                        <p class="text-xs text-slate-400">Quick tip</p>
                        <p class="text-sm text-slate-600">Provide a concise subject and clear description for faster response.</p>
                    </div>
                </div>

                <form @submit.prevent="submitTicket" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="subject" value="Subject" />
                        <TextInput id="subject" v-model="form.subject" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3" placeholder="Brief summary of the issue" required />
                        <InputError class="mt-2" :message="form.errors.subject" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Description" />
                        <Textarea id="description" v-model="form.description" class="mt-2 block w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3" rows="6" placeholder="Describe the problem, steps to reproduce, and expected outcome" required />
                        <div class="flex items-center justify-between mt-2">
                            <InputError :message="form.errors.description" />
                            <div class="text-xs text-slate-400">{{ descriptionLength }} chars</div>
                        </div>
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

                    <div class="mt-2">
                        <InputLabel value="Attachments (optional)" />
                        <input type="file" @change="e => attachments = e.target.files" class="mt-2 block w-full text-sm text-slate-600" />
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
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
