<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
// --- INAYOS: Idinagdag ang 'computed' ---
import { computed } from 'vue';

const props = defineProps({
    prefill: Object // Ito ay null kung galing sa generic 'create user'
});

const form = useForm({
    first_name: props.prefill?.first_name ?? '',
    middle_name: props.prefill?.middle_name ?? '',
    last_name: props.prefill?.last_name ?? '',
    email: props.prefill?.email ?? '',
    password: '',
    password_confirmation: '',
    // TANDAAN: Hindi na natin kailangan ang 'employee_id' sa form,
    // dahil ipapasa natin ito sa route parameter.
});

// --- INAYOS: Nag-check kung ito ay para sa employee ---
const isEmployeeAccount = computed(() => !!props.prefill);

// --- INAYOS: Inayos ang buong submit logic ---
const submit = () => {
    if (isEmployeeAccount.value) {
        // 1. Kung galing sa Employee page, sa 'employees.storeAccount' i-submit
        form.post(route('employees.storeAccount', { employee: props.prefill.employee_id }), {
            // I-reset lang ang password fields kapag may error
            onError: () => form.reset('password', 'password_confirmation'),
            // Hindi na kailangan ng onSuccess dahil magre-redirect ito
        });
    } else {
        // 2. Kung generic 'Create User', sa 'users.store' i-submit
        form.post(route('users.store'), {
            onSuccess: () => form.reset(), // I-reset lahat kapag successful
            onError: () => form.reset('password', 'password_confirmation'), // I-reset lang ang password kapag may error
        });
    }
};

// --- INAYOS: Dynamic na 'Cancel' link ---
const cancelRoute = computed(() => {
    if (isEmployeeAccount.value) {
        // Bumalik sa employee show page
        return route('employees.show', { employee: props.prefill.employee_id });
    }
    // Bumalik sa user index page
    return route('users.index');
});
</script>

<template>
    <Head title="Create User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ isEmployeeAccount ? `Create Account for: ${prefill.first_name} ${prefill.last_name}` : 'Create New User' }}
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
                                    :autofocus="!isEmployeeAccount" :disabled="isEmployeeAccount" :class="{ 'bg-gray-100': isEmployeeAccount }"
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
                                    :disabled="isEmployeeAccount" :class="{ 'bg-gray-100': isEmployeeAccount }"
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
                                    :disabled="isEmployeeAccount" :class="{ 'bg-gray-100': isEmployeeAccount }"
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
                                    :disabled="isEmployeeAccount" :class="{ 'bg-gray-100': isEmployeeAccount }"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="password" value="Password" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password"
                                    required
                                    autocomplete="new-password"
                                    :autofocus="isEmployeeAccount" />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="cancelRoute" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancel
                                </Link>

                                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Create User
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>