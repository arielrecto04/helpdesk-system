<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    phone_number: '',
});

const submit = () => {
    form.post(route('customers.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Customer" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Customer
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="max-w-2xl mx-auto">

                            <div>
                                <InputLabel for="first_name" value="First Name" />
                                <TextInput
                                    id="first_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.first_name"
                                    required
                                    autofocus
                                    autocomplete="given-name"
                                />
                                <InputError class="mt-2" :message="form.errors.first_name" />
                            </div>
                            
                            <div class="mt-4"> <InputLabel for="middle_name" value="Middle Name (Optional)" />
                                <TextInput
                                    id="middle_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.middle_name"
                                    autocomplete="additional-name" 
                                    />
                                <InputError class="mt-2" :message="form.errors.middle_name" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="last_name" value="Last Name" />
                                <TextInput
                                    id="last_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.last_name"
                                    required
                                    autocomplete="family-name"
                                />
                                <InputError class="mt-2" :message="form.errors.last_name" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.email"
                                    required
                                    autocomplete="username"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="phone_number" value="Phone Number (Optional)" />
                                <TextInput
                                    id="phone_number"
                                    type="tel" class="mt-1 block w-full"
                                    v-model="form.phone_number"
                                    autocomplete="tel"
                                    />
                                <InputError class="mt-2" :message="form.errors.phone_number" />
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('customers.index')" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>

                                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Create Customer
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>