<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    team: Object,
});

const form = useForm({
    team_name: props.team.team_name,
});

const submit = () => {
    form.put(route('helpdeskteams.update', props.team.id));
};
</script>

<template>
    <Head :title="'Edit Team: ' + team.team_name" />
    <AuthenticatedLayout>
        <div class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-8">
            <!-- Header Banner -->
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-xl p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-3 rounded-xl mr-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 11-8 0 4 4 0 018 0m10 0a4 4 0 11-8 0 4 4 0 018 0" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="font-bold text-3xl text-white">Edit Team</h2>
                                <p class="text-blue-100 text-sm mt-1">{{ team.team_name }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <Link :href="route('helpdeskteams.index')" class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold text-sm shadow-lg hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
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
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="team_name" value="Team Name" />
                                <TextInput
                                    id="team_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.team_name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.team_name" />
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('helpdeskteams.index')" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>
                                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update Team
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>