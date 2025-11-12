<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

import { Head, Link, useForm } from '@inertiajs/vue3';


const props = defineProps({
    team_name: String,
});

const form = useForm({
    team_name: props.team_name || '',
});

const submit = () => {

    form.post(route('helpdeskteams.store'), {

        onFinish: () => form.reset('team_name'),
    });
};
</script>

<template>
    <Head title="Create New Team" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Team
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
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
                                    Create Team
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>