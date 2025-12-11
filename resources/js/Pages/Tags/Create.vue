<script setup>
import CreateLayout from '@/Components/CreateLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    name: String,
});

const form = useForm({
    name: props.name || '',
});

const submit = () => {
    form.post(route('tags.store'), {
        onFinish: () => form.reset('name'),
    });
};
</script>

<template>
    <CreateLayout
        title="Create Tag"
        subtitle="Add a new tag to categorize tickets"
        :breadcrumb-items="[
            { label: 'Home', href: route('dashboard') },
            { label: 'Tags', href: route('tags.index') },
            { label: 'Create' }
        ]"
        icon="plus"
        max-width="2xl"
    >
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Tag Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="flex items-center justify-end mt-6">
                <Link :href="route('tags.index')" class="text-sm text-gray-600 hover:text-gray-900 underline">
                    Cancel
                </Link>
                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Create Tag
                </PrimaryButton>
            </div>
        </form>
    </CreateLayout>
</template>