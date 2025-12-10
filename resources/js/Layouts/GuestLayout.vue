<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen relative overflow-hidden bg-gradient-to-b from-[#123A66] to-[#1E4F8F] flex items-center py-12">
        <!-- Background accents -->
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <div class="absolute -top-40 -left-40 h-[560px] w-[560px] rounded-full bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-[#1A3B8E]/30 via-transparent to-transparent blur-2xl"></div>
            <div class="absolute top-1/4 -right-48 h-[700px] w-[700px] rounded-full bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-[#3BA3FF]/18 via-transparent to-transparent blur-2xl"></div>
        </div>

        <div class="w-full">
            <div class="flex flex-col items-center">
                <Link href="/" aria-label="Go to home" class="mb-6 flex items-center gap-3">
                    <ApplicationLogo class="h-12 w-14 fill-current text-white" />
                    <div class="text-white font-semibold text-lg">IITS Helpdesk Ticketing System</div>
                </Link>
            </div>

            <div class="mx-auto w-full max-w-xl px-4 sm:px-6">
                <div role="main" class="rounded-3xl bg-white/8 p-6 sm:p-8 shadow-2xl shadow-[0_30px_90px_-18px_rgba(0,0,0,0.75)] ring-1 ring-white/12 backdrop-blur-md border border-white/6">
                    <div class="mb-4 text-center">
                        <slot name="title" />
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
