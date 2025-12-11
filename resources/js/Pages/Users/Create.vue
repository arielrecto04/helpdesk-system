<script setup>
import CreateLayout from '@/Components/CreateLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    prefill: Object,
    roles: { type: Array, default: () => [] },
    is_customer: { type: Boolean, default: false },
});

const form = useForm({
    first_name: props.prefill?.first_name ?? '',
    middle_name: props.prefill?.middle_name ?? '',
    last_name: props.prefill?.last_name ?? '',
    email: props.prefill?.email ?? '',
    password: '',
    password_confirmation: '',
    roles: [],
    customer_id: props.prefill?.customer_id ?? null,
});

const isEmployeeAccount = computed(() => !!props.prefill && !props.is_customer);
const isCustomerAccount = computed(() => !!props.is_customer);

const prefill = props.prefill ?? null;
const roles = props.roles ?? [];
const is_customer = props.is_customer ?? false;

const selectedRole = ref(form.roles && form.roles.length ? form.roles[0] : null);
watch(selectedRole, (val) => {
    form.roles = val ? [val] : [];
});

const submit = () => {
    if (isEmployeeAccount.value) {
        form.post(route('employees.storeAccount', { employee: props.prefill?.employee_id }), {
            onError: () => form.reset('password', 'password_confirmation'),
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => form.reset(),
            onError: () => form.reset('password', 'password_confirmation'),
        });
    }
};

const cancelRoute = computed(() => {
    if (isEmployeeAccount.value) {
        return route('employees.show', { employee: props.prefill?.employee_id });
    }
    if (isCustomerAccount.value) {
        return route('customers.show', { customer: props.prefill?.customer_id });
    }
    return route('users.index');
});

const pageTitle = computed(() => {
    if (isEmployeeAccount.value) {
        return `Create Account for: ${prefill?.first_name ?? ''} ${prefill?.last_name ?? ''}`;
    }
    return 'Create New User';
});

const breadcrumbItems = computed(() => {
    if (isEmployeeAccount.value) {
        return [
            { label: 'Home', href: route('dashboard') },
            { label: 'Employees', href: route('employees.index') },
            { label: 'Create Account' }
        ];
    }
    return [
        { label: 'Home', href: route('dashboard') },
        { label: 'Users', href: route('users.index') },
        { label: 'Create' }
    ];
});
</script>

<template>
    <CreateLayout
        :title="pageTitle"
        subtitle="Create a new user account with credentials"
        :breadcrumb-items="breadcrumbItems"
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

                            <!-- Roles selection for employee account creation -->
                            <div v-if="roles && roles.length > 0" class="mt-4">
                                <InputLabel value="Assign Role" />
                                <div class="mt-2">
                                    <select v-model.number="selectedRole" required class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option :value="null">-- Select role --</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                    </select>
                                </div>
                                <InputError class="mt-2" :message="form.errors.roles" />
                                <p v-if="roles && roles.length > 0 && !selectedRole" class="text-sm text-red-600 mt-2">Please select a role.</p>
                            </div>

            <!-- Hidden customer id for customer-account flow -->
            <input v-if="form.customer_id" type="hidden" v-model="form.customer_id" />

            <div class="flex items-center justify-end mt-6">
                <Link :href="cancelRoute" class="text-sm text-gray-600 hover:text-gray-900 underline">
                    Cancel
                </Link>
                <PrimaryButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing || (roles && roles.length > 0 && !selectedRole)">
                    Create User
                </PrimaryButton>
            </div>
        </form>
    </CreateLayout>
</template>