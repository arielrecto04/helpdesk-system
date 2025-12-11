<script setup>
import CreateLayout from '@/Components/CreateLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    companies: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    phone_number: '',
    company_id: null,
});

const submit = () => {
    form.post(route('customers.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <CreateLayout
        title="Create Customer"
        subtitle="Add a new customer to the system"
        :breadcrumb-items="[
            { label: 'Home', href: route('dashboard') },
            { label: 'Customers', href: route('customers.index') },
            { label: 'Create' }
        ]"
        icon="plus"
        max-width="2xl"
    >
        <form @submit.prevent="submit">

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

                            <div class="mt-4">
                                <InputLabel for="company_id" value="Company (Optional)" />
                                <select id="company_id" v-model="form.company_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                    <option :value="null">Select company</option>
                                    <option v-for="c in props.companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.company_id" />
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
    </CreateLayout>
</template>