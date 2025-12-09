<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen relative overflow-hidden bg-gradient-to-b from-[#0A1733] to-[#0E2255] flex items-center py-12">
        <!-- Background accents -->
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <div class="absolute -top-40 -left-40 h-[560px] w-[560px] rounded-full bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-[#1A3B8E]/30 via-transparent to-transparent blur-2xl"></div>
            <div class="absolute top-1/4 -right-48 h-[700px] w-[700px] rounded-full bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-[#3BA3FF]/18 via-transparent to-transparent blur-2xl"></div>
        </div>

        <div class="w-full">
            <div class="flex flex-col items-center">
                <Link href="/" class="mb-6 flex items-center gap-3">
                    <ApplicationLogo class="h-12 w-12 fill-current text-white/90" />
                    <div class="text-white/90 font-semibold text-lg">Innovato</div>
                </Link>
            </div>

            <div class="mx-auto w-full max-w-md px-6">
                <div class="rounded-3xl bg-white/6 p-8 shadow-2xl ring-1 ring-white/10 backdrop-blur-md">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
