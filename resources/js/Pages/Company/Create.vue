<script setup>
import CreateLayout from '@/Components/CreateLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    address: '',
    phone: '',
    email: '',
});

const submit = () => {
    form.post(route('companies.store'));
};
</script>

<template>
    <CreateLayout
        title="Create Company"
        subtitle="Add a new company to the system"
        :breadcrumb-items="[
            { label: 'Home', href: route('dashboard') },
            { label: 'Companies', href: route('companies.index') },
            { label: 'Create' }
        ]"
        icon="plus"
        max-width="2xl"
    >
        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <InputLabel for="name" value="Company Name" />
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

                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
            </div>

            <!-- Address -->
            <div class="mt-4">
                <InputLabel for="address" value="Address" />
                <TextInput
                    id="address"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.address"
                    required
                />
                <InputError class="mt-2" :message="form.errors.address" />
            </div>

            <!-- Phone -->
            <div class="mt-4">
                <InputLabel for="phone" value="Phone" />
                <TextInput
                    id="phone"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    required
                />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="flex items-center justify-end mt-6">
                <Link :href="route('companies.index')" class="text-sm text-gray-600 hover:text-gray-900 underline">
                    Cancel
                </Link>
                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Create Company
                </PrimaryButton>
            </div>
        </form>
    </CreateLayout>
</template>