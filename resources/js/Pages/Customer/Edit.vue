<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3'; 
import { computed, watch } from 'vue'; 

const props = defineProps({
    customer: Object,
    companies: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    first_name: props.customer.first_name,
    middle_name: props.customer.middle_name ?? '',
    last_name: props.customer.last_name,
    email: props.customer.email,
    phone_number: props.customer.phone_number ?? '',
    company_id: props.customer.company_id ?? null,
});

const submit = () => {
    form.put(route('customers.update', { customer: props.customer.id }));
};

const fullName = computed(() =>
    [props.customer.first_name, props.customer.middle_name, props.customer.last_name]
        .filter(Boolean)
        .join(' ')
);

</script>

<template>
    <Head :title="'Edit Customer: ' + fullName" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Customer: <span class="font-bold">{{ fullName }}</span>
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

                            <div class="mt-4">
                                <InputLabel for="middle_name" value="Middle Name (Optional)" />
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
                                <InputLabel for="phone_number" value="Phone (Optional)" />
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
                                    Update Customer </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>