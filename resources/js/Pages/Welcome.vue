<script setup>
import { Head, Link } from '@inertiajs/vue3';

const brand = 'Innovato Information Technology Solutions';

const features = [
    {
        title: "24/7 Support",
        description: "Round-the-clock technical assistance for all your needs",
        icon: "support"
    },
    {
        title: "Ticket Tracking",
        description: "Real-time monitoring of your support requests",
        icon: "ticket"
    },
    {
        title: "Knowledge Base",
        description: "Comprehensive documentation and self-help resources",
        icon: "knowledge"
    },
    {
        title: "Priority Response",
        description: "Fast response times based on issue severity",
        icon: "priority"
    }
];

const stats = [
    { number: "99.9%", label: "Uptime" },
    { number: "15min", label: "Avg. Response Time" },
    { number: "50K+", label: "Tickets Resolved" },
    { number: "98%", label: "Customer Satisfaction" }
];
</script>

<template>

    <Head title="Innovato Information Technology Solutions — Helpdesk" />

    <!-- Hero Section -->
    <div class="min-h-screen relative overflow-hidden bg-gradient-to-b from-[#0A1733] to-[#0E2255]">
        <!-- Abstract tech background shapes -->
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 -left-40 h-[600px] w-[600px] rounded-full bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-[#1A3B8E]/40 via-transparent to-transparent blur-2xl"></div>
            <div class="absolute top-1/3 -right-48 h-[700px] w-[700px] rounded-full bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-[#3BA3FF]/20 via-transparent to-transparent blur-2xl"></div>
            <svg class="absolute right-20 top-24 opacity-20" width="400" height="400" viewBox="0 0 400 400" fill="none">
                <defs>
                    <linearGradient id="grid" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#8693A6"/>
                        <stop offset="100%" stop-color="#3BA3FF"/>
                    </linearGradient>
                </defs>
                <g stroke="url(#grid)" stroke-width="0.5">
                    <path d="M0 50 H400"/>
                    <path d="M0 100 H400"/>
                    <path d="M0 150 H400"/>
                    <path d="M0 200 H400"/>
                    <path d="M0 250 H400"/>
                    <path d="M0 300 H400"/>
                    <path d="M0 350 H400"/>
                    <path d="M50 0 V400"/>
                    <path d="M100 0 V400"/>
                    <path d="M150 0 V400"/>
                    <path d="M200 0 V400"/>
                    <path d="M250 0 V400"/>
                    <path d="M300 0 V400"/>
                    <path d="M350 0 V400"/>
                </g>
            </svg>
        </div>
        <!-- Navigation -->
        <nav class="container mx-auto px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="/images/iits_logo.png" alt="Logo" class="h-10 w-10 rounded-xl object-cover shadow-inner backdrop-blur-md ring-1 ring-white/20" />
                    <div class="text-2xl font-semibold tracking-tight text-white">{{ brand }}</div>
                </div>
                <div class="space-x-3">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="text-white/90 hover:text-white">
                    Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="text-white/90 hover:text-white">Login</Link>
                        <Link :href="route('register')" class="ml-2 text-white/90 hover:text-white">Get Started</Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Central Hero Composition -->
        <div class="container mx-auto px-6 pt-10 pb-24">
            <div class="flex flex-col items-center text-center">
                <!-- 3D Isometric Avatar Placeholder (replace with real render) -->
                <div class="relative mb-10">
                    <div class="mx-auto h-[380px] w-[680px] rounded-3xl bg-gradient-to-br from-[#0F2A66] to-[#162B49] shadow-2xl ring-1 ring-white/10 backdrop-blur-xl" style="perspective:1000px">
                        <div class="absolute inset-6 rounded-2xl bg-white/8 backdrop-blur-xl ring-1 ring-white/10 shadow-lg" style="transform:rotateX(8deg) rotateY(-6deg); transform-style:preserve-3d"></div>
                        <!-- Floating UI widgets -->
                        <div class="absolute -top-6 left-10 h-14 w-44 rounded-full bg-white/10 backdrop-blur-md ring-1 ring-white/20 shadow-md"></div>
                        <div class="absolute top-8 -right-6 h-20 w-20 rounded-2xl bg-[#3BA3FF]/20 ring-1 ring-[#3BA3FF]/40 blur-[1px]"></div>
                        <div class="absolute bottom-10 left-6 h-24 w-56 rounded-2xl bg-white/10 backdrop-blur-md ring-1 ring-white/20"></div>
                        <!-- Soft glowing data particles -->
                        <div class="absolute inset-0">
                            <div class="absolute left-24 top-6 h-2 w-2 rounded-full bg-[#3BA3FF] blur-sm opacity-80"></div>
                            <div class="absolute left-44 top-20 h-1.5 w-1.5 rounded-full bg-[#3BA3FF] blur-sm opacity-60"></div>
                            <div class="absolute right-24 top-14 h-2 w-2 rounded-full bg-[#8693A6] blur-sm opacity-70"></div>
                            <div class="absolute right-36 bottom-20 h-1.5 w-1.5 rounded-full bg-white blur-sm opacity-50"></div>
                        </div>
                    </div>
                </div>

                <!-- Headline & Subcopy -->
                <h1 class="mb-4 text-5xl font-semibold leading-tight tracking-tight text-white">
                    Premium IT Helpdesk, Elevated by Design
                </h1>
                <p class="mb-10 max-w-3xl text-lg text-[#C9D3E8]">
                    Corporate-modern support platform with streamlined ticketing, intelligent routing,
                    and delightful UI. Built for speed, clarity, and confidence.
                </p>

                <!-- Floating Navigation Card (Glassmorphism) -->
                <div class="relative">
                    <div class="flex items-center gap-3 rounded-full bg-white/10 px-4 py-4 backdrop-blur-xl ring-1 ring-white/20 shadow-xl">
                        <!-- Submit a Ticket (primary, solid electric blue) -->
                        <Link :href="route('register')" class="inline-flex items-center gap-2 rounded-full bg-[#3BA3FF] px-6 py-2.5 text-sm font-semibold text-[#0A1733] shadow-[0_12px_24px_-6px_rgba(59,163,255,0.45)] hover:bg-[#49B2FF]">
                            <span>Submit a Ticket</span>
                        </Link>
                        <!-- Login (ghost outline) -->
                        <Link :href="route('login')" class="inline-flex items-center gap-2 rounded-full border border-white/40 px-6 py-2.5 text-sm font-semibold text-white/90 hover:border-white hover:text-white">
                            <span>Login</span>
                        </Link>
                        <!-- Get Started (text link with arrow) -->
                        <Link :href="route('register')" class="inline-flex items-center gap-2 px-2 text-sm font-medium text-white/80 hover:text-white">
                            <span>Get Started</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="opacity-80"><path d="M5 12h12M13 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="bg-[#0E2255]/40 py-16 backdrop-blur-sm">
            <div class="container mx-auto grid grid-cols-1 gap-8 px-6 md:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="text-center">
                    <div class="text-4xl font-semibold tracking-tight text-white">{{ stat.number }}</div>
                    <div class="text-[#C9D3E8]">{{ stat.label }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white py-20">
        <div class="container mx-auto px-6">
            <h2 class="mb-12 text-center text-3xl font-bold text-gray-900">
                Why Choose Our Helpdesk?
            </h2>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                <div v-for="feature in features" :key="feature.title" class="rounded-2xl bg-white p-6 shadow-lg ring-1 ring-slate-200/70">
                    <div class="mb-4 h-12 w-12 rounded-full bg-slate-100 p-3">
                        <!-- Icon placeholder - replace with actual icons -->
                        <div class="h-full w-full rounded-full bg-[#3BA3FF]"></div>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900">
                        {{ feature.title }}
                    </h3>
                    <p class="text-gray-600">
                        {{ feature.description }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Section -->
    <div class="bg-gray-50 py-20">
        <div class="container mx-auto px-6">
            <h2 class="mb-12 text-center text-3xl font-bold text-gray-900">
                How It Works
            </h2>
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="text-center">
                    <div class="mb-4 inline-block rounded-full bg-[#0E2255] p-4 text-white">
                        1
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Submit Ticket</h3>
                    <p class="text-gray-600">
                        Describe your issue and submit a support ticket in minutes
                    </p>
                </div>
                <div class="text-center">
                    <div class="mb-4 inline-block rounded-full bg-[#0E2255] p-4 text-white">
                        2
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Get Assigned</h3>
                    <p class="text-gray-600">
                        Our experts review and assign your ticket to the right team
                    </p>
                </div>
                <div class="text-center">
                    <div class="mb-4 inline-block rounded-full bg-[#0E2255] p-4 text-white">
                        3
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Problem Solved</h3>
                    <p class="text-gray-600">
                        Get quick resolution and follow-up support as needed
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-[#0A1733] py-20 text-center">
        <div class="container mx-auto px-6">
            <h2 class="mb-6 text-3xl font-bold text-white">
                Ready to Get Started?
            </h2>
            <p class="mb-8 text-[#C9D3E8]">
                Join thousands of satisfied customers who trust our helpdesk solution
            </p>
            <button class="rounded-full bg-[#3BA3FF] px-8 py-4 text-lg font-semibold text-[#0A1733] shadow-[0_12px_24px_-6px_rgba(59,163,255,0.45)] hover:bg-[#49B2FF]">
                Create Free Account
            </button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-[#0A1733] py-12 text-gray-400">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-white">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white">About</a></li>
                        <li><a href="#" class="hover:text-white">Careers</a></li>
                        <li><a href="#" class="hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-white">Services</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white">Help Desk</a></li>
                        <li><a href="#" class="hover:text-white">IT Support</a></li>
                        <li><a href="#" class="hover:text-white">Training</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-white">Resources</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white">Documentation</a></li>
                        <li><a href="#" class="hover:text-white">Blog</a></li>
                        <li><a href="#" class="hover:text-white">FAQs</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-lg font-semibold text-white">Legal</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 border-t border-gray-800 pt-8 text-center">
                <p>&copy; 2025 {{ brand }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

</template>
