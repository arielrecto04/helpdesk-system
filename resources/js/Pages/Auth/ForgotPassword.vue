<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password — Innovato" />

        <div class="mb-4 text-sm text-[#C9D3E8]">
            Forgot your password? No problem. Enter your email and we'll send a
            secure reset link to get you back in.
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-200">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" class="text-white/80" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-lg border border-white/10 bg-white/4 text-slate-900 placeholder-slate-400 focus:border-[#3BA3FF] focus:ring-[#3BA3FF]"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2 text-rose-300" :message="form.errors.email" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton class="rounded-full bg-[#3BA3FF] px-5 py-2 text-[#0A1733]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Email Password Reset Link
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
