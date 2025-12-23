<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
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
    <GuestLayout>
        <template #title>
            <div class="text-center">
                <h2 class="text-3xl font-bold text-white">Login</h2>
                <p class="mt-2 text-sm text-[#C9D3E8]">Sign in to your account to access the helpdesk dashboard.</p>
            </div>
        </template>

        <Head title="Innovato — Login" />

        <div>
            <div v-if="status" class="mb-4 rounded-md bg-emerald-900/30 p-3 text-sm font-medium text-emerald-200">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email" class="text-white/80" />

                    <TextInput id="email" type="email" class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-slate-900 placeholder-slate-400
                        focus:border-[#3BA3FF] focus:ring-[#3BA3FF]" v-model="form.email" required
                        autofocus autocomplete="username" placeholder="admin@helpdesk.com" />

                    <InputError class="mt-2 text-rose-300" :message="form.errors.email" />
                </div>

                <div class="mt-6">
                    <InputLabel for="password" value="Password" class="text-white/80" />
                    <TextInput id="password" type="password" class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-slate-900 placeholder-slate-400
                        focus:border-[#3BA3FF] focus:ring-[#3BA3FF]" v-model="form.password"
                        required autocomplete="current-password" placeholder="password" />

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
                    <PrimaryButton class="w-full justify-center rounded-full bg-[#0B66B3] py-3 text-white hover:bg-[#0E71C2] shadow-[0_12px_30px_-6px_rgba(0,0,0,0.6)] hover:shadow-[0_18px_44px_-12px_rgba(0,0,0,0.6)]" :class="{ 'opacity-75': form.processing }" :disabled="form.processing">
                        Sign In
                    </PrimaryButton>
                </div>

                <div class="mt-6 text-center">
                    <span class="text-sm text-white/80">Don't have an account?</span>
                    <Link :href="route('register')" class="ml-1 text-sm text-emerald-400 hover:text-emerald-300">
                    Create Account
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
