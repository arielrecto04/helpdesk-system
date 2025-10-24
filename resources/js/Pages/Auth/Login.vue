<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-blue-900 to-blue-700 py-16">

        <Head title="Log in" />

        <div class="mx-auto max-w-md">
            <!-- Logo/Brand Section -->
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-white">IT Helpdesk</h1>
                <p class="mt-2 text-blue-200">Welcome back! Please login to your account.</p>
            </div>

            <!-- Login Card -->
            <div class="rounded-lg bg-white p-8 shadow-xl">
                <div v-if="status" class="mb-4 rounded-md bg-green-50 p-4 text-sm font-medium text-green-600">
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-700" />

                        <TextInput id="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                            focus:border-blue-500 focus:ring-blue-500" v-model="form.email" required autofocus
                            autocomplete="username" />

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="mt-6">
                        <InputLabel for="password" value="Password" class="text-gray-700" />

                        <TextInput id="password" type="password" class="mt-1 block w-full rounded-md border-gray-300
                            shadow-sm focus:border-blue-500 focus:ring-blue-500" v-model="form.password" required
                            autocomplete="current-password" />

                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-gray-300
                                text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>

                        <Link v-if="canResetPassword" :href="route('password.request')"
                            class="text-sm text-blue-600 hover:text-blue-800">
                        Forgot password?
                        </Link>
                    </div>

                    <div class="mt-6">
                        <PrimaryButton class="w-full justify-center rounded-full bg-blue-900 py-3 text-center
                            hover:bg-blue-800" :class="{ 'opacity-75': form.processing }" :disabled="form.processing">
                            Sign In
                        </PrimaryButton>
                    </div>

                    <div class="mt-6 text-center">
                        <span class="text-sm text-gray-600">Don't have an account?</span>
                        <Link :href="route('register')" class="ml-1 text-sm text-blue-600 hover:text-blue-800">
                        Create Account
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
