<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification — Innovato" />

        <div class="mb-4 text-sm text-[#C9D3E8]">
            Thanks for signing up! Before getting started, please verify your
            email address by clicking the link we emailed. If you didn't receive
            it, we will gladly send another.
        </div>

        <div class="mb-4 text-sm font-medium text-emerald-200" v-if="verificationLinkSent">
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <PrimaryButton class="w-full sm:w-auto justify-center rounded-full bg-[#0B66B3] px-4 sm:px-6 py-2.5 sm:py-3 text-white hover:bg-[#0E71C2] shadow-[0_12px_30px_-6px_rgba(0,0,0,0.6)] hover:shadow-[0_18px_44px_-12px_rgba(0,0,0,0.6)]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Resend Verification Email
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-[#C9D3E8] underline hover:text-white"
                    >Log Out</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
