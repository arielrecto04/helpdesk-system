<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    company: '',
    password: '',
    password_confirmation: '',
});

const companies = usePage().props.companies || [];

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register — IITS Helpdesk Ticketing System" />

        <div class="mb-8 text-center">
            <h2 class="text-3xl font-bold text-white">Register</h2>
            <p class="mt-2 text-sm text-[#C9D3E8]">Create an account to access your helpdesk dashboard.</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="first_name" value="First Name" class="text-white/80" />

                <TextInput
                    id="first_name"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-slate-900 placeholder-slate-400 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
                    v-model="form.first_name"
                    required
                    autofocus
                    autocomplete="given-name"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.first_name" />
            </div>

            <div class="mt-4">
                <InputLabel for="middle_name" value="Middle Name" class="text-white/80" />

                <TextInput
                    id="middle_name"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-slate-900 placeholder-slate-400 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
                    v-model="form.middle_name"
                    autocomplete="additional-name"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.middle_name" />
            </div>

            <div class="mt-4">
                <InputLabel for="last_name" value="Last Name" class="text-white/80" />

                <TextInput
                    id="last_name"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-slate-900 placeholder-slate-400 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
                    v-model="form.last_name"
                    required
                    autocomplete="family-name"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.last_name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" class="text-white/80" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-slate-900 placeholder-slate-400 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="company" value="Company (optional)" class="text-white/80" />

                <TextInput
                    id="company"
                    type="text"
                    list="companies-list"
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-slate-900 placeholder-slate-400 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
                    v-model="form.company"
                    autocomplete="organization"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.company" />
            </div>
            <datalist id="companies-list">
                <option v-for="c in companies" :key="c" :value="c"></option>
            </datalist>

            <div class="mt-4">
                <InputLabel for="password" value="Password" class="text-white/80" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-slate-900 placeholder-slate-400 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                    class="text-white/80"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-slate-900 placeholder-slate-400 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2 text-rose-300"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                <Link
                    :href="route('login')"
                    class="text-sm text-emerald-400 underline hover:text-emerald-300"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="w-full sm:w-auto justify-center rounded-full bg-[#0B66B3] px-6 py-3 text-white hover:bg-[#0E71C2] shadow-[0_12px_30px_-6px_rgba(0,0,0,0.6)] hover:shadow-[0_18px_44px_-12px_rgba(0,0,0,0.6)]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
