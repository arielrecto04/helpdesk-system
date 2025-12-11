<script setup>
import CreateLayout from '@/Components/CreateLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

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
    <CreateLayout
        title="Create Helpdesk Team"
        subtitle="Add a new team to handle support tickets"
        :breadcrumb-items="[
            { label: 'Home', href: route('dashboard') },
            { label: 'Helpdesk Teams', href: route('helpdeskteams.index') },
            { label: 'Create' }
        ]"
        icon="plus"
        max-width="2xl"
    >
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
    </CreateLayout>
</template>