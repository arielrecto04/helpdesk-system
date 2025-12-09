<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const brand = 'Innovato Information Technology Solutions';

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
    <div class="min-h-screen relative overflow-hidden bg-gradient-to-b from-[#0A1733] to-[#0E2255] py-16">

        <Head title="Innovato — Login" />

        <!-- Background shapes -->
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 -left-40 h-[600px] w-[600px] rounded-full bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-[#1A3B8E]/30 via-transparent to-transparent blur-2xl"></div>
            <div class="absolute top-1/3 -right-48 h-[700px] w-[700px] rounded-full bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-[#3BA3FF]/18 via-transparent to-transparent blur-2xl"></div>
        </div>

        <div class="mx-auto max-w-lg relative z-10">
            <!-- Logo/Brand Section -->
            <div class="mb-8 text-center">
                <div class="flex items-center justify-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-white/10 shadow-inner backdrop-blur-md ring-1 ring-white/20"></div>
                    <div class="text-2xl font-semibold tracking-tight text-white">{{ brand }}</div>
                </div>
                <p class="mt-3 text-[#C9D3E8]">Welcome back — sign in to continue to your dashboard.</p>
            </div>

            <!-- Login Card (glass) -->
            <div class="rounded-3xl bg-white/6 p-8 shadow-2xl ring-1 ring-white/10 backdrop-blur-md">
                <div v-if="status" class="mb-4 rounded-md bg-emerald-900/30 p-3 text-sm font-medium text-emerald-200">
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <div>
                        <InputLabel for="email" value="Email" class="text-white/80" />

                        <TextInput id="email" type="email" class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-white placeholder-white/60
                            focus:border-[#3BA3FF] focus:ring-[#3BA3FF]" v-model="form.email" required
                            autofocus autocomplete="username" placeholder="you@company.com" />

                        <InputError class="mt-2 text-rose-300" :message="form.errors.email" />
                    </div>

                    <div class="mt-6">
                        <InputLabel for="password" value="Password" class="text-white/80" />
                        <TextInput id="password" type="password" class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-white placeholder-white/60
                            focus:border-[#3BA3FF] focus:ring-[#3BA3FF]" v-model="form.password"
                            required autocomplete="current-password" placeholder="••••••••" />

                        <InputError class="mt-2 text-rose-300" :message="form.errors.password" />
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-white/20 text-[#3BA3FF]" />
                            <span class="ml-2 text-sm text-white/80">Remember me</span>
                        </label>

                        <Link v-if="canResetPassword" :href="route('password.request')"
                            class="text-sm text-[#3BA3FF] hover:text-[#49B2FF]">
                        Forgot password?
                        </Link>
                    </div>

                    <div class="mt-6">
                        <PrimaryButton class="w-full justify-center rounded-full bg-[#3BA3FF] py-3 text-[#0A1733] hover:bg-[#49B2FF]" :class="{ 'opacity-75': form.processing }" :disabled="form.processing">
                            Sign In
                        </PrimaryButton>
                    </div>

                    <div class="mt-6 text-center">
                        <span class="text-sm text-white/80">Don't have an account?</span>
                        <Link :href="route('register')" class="ml-1 text-sm text-[#C9D3E8] hover:text-white">
                        Create Account
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
