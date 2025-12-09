<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
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
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register — Innovato" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="first_name" value="First Name" class="text-white/80" />

                <TextInput
                    id="first_name"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-white placeholder-white/60 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
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
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-white placeholder-white/60 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
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
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-white placeholder-white/60 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
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
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-white placeholder-white/60 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" class="text-white/80" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-white placeholder-white/60 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
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
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-white placeholder-white/60 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2 text-rose-300"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link
                    :href="route('login')"
                    class="text-sm text-[#C9D3E8] underline hover:text-white"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4 rounded-full bg-[#3BA3FF] px-6 py-2 text-[#0A1733]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
