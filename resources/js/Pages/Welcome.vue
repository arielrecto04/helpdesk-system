<script setup>
import { Head, Link } from '@inertiajs/vue3';

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

    <Head title="IT Helpdesk - Your Technical Support Partner" />

    <!-- Hero Section -->
    <div class="min-h-screen bg-gradient-to-b from-blue-900 to-blue-700">
        <!-- Navigation -->
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="text-2xl font-bold text-white">IT Helpdesk</div>
                <div class="space-x-4">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="text-white hover:text-blue-200">
                    Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="text-white hover:text-blue-200">
                        Login
                        </Link>
                        <Link :href="route('register')"
                            class="ml-4 rounded-lg bg-white px-6 py-2 text-blue-900 hover:bg-blue-100">
                        Get Started
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Content -->
        <div class="container mx-auto px-6 py-20 text-center">
            <h1 class="mb-6 text-5xl font-bold text-white">
                Professional IT Support When You Need It
            </h1>
            <p class="mb-12 text-xl text-blue-100">
                Streamlined helpdesk solutions for businesses of all sizes.
                Get instant support, track issues, and resolve problems faster.
            </p>
            <button class="rounded-full bg-white px-8 py-4 text-lg font-semibold text-blue-900 hover:bg-blue-100">
                Submit a Ticket
            </button>
        </div>

        <!-- Stats Section -->
        <div class="bg-blue-800/50 py-16">
            <div class="container mx-auto grid grid-cols-1 gap-8 px-6 md:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="text-center">
                    <div class="text-4xl font-bold text-white">{{ stat.number }}</div>
                    <div class="text-blue-200">{{ stat.label }}</div>
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
                <div v-for="feature in features" :key="feature.title" class="rounded-lg bg-white p-6 shadow-lg">
                    <div class="mb-4 h-12 w-12 rounded-full bg-blue-100 p-3">
                        <!-- Icon placeholder - replace with actual icons -->
                        <div class="h-full w-full rounded-full bg-blue-500"></div>
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
                    <div class="mb-4 inline-block rounded-full bg-blue-500 p-4 text-white">
                        1
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Submit Ticket</h3>
                    <p class="text-gray-600">
                        Describe your issue and submit a support ticket in minutes
                    </p>
                </div>
                <div class="text-center">
                    <div class="mb-4 inline-block rounded-full bg-blue-500 p-4 text-white">
                        2
                    </div>
                    <h3 class="mb-2 text-xl font-semibold">Get Assigned</h3>
                    <p class="text-gray-600">
                        Our experts review and assign your ticket to the right team
                    </p>
                </div>
                <div class="text-center">
                    <div class="mb-4 inline-block rounded-full bg-blue-500 p-4 text-white">
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
    <div class="bg-blue-900 py-20 text-center">
        <div class="container mx-auto px-6">
            <h2 class="mb-6 text-3xl font-bold text-white">
                Ready to Get Started?
            </h2>
            <p class="mb-8 text-blue-100">
                Join thousands of satisfied customers who trust our helpdesk solution
            </p>
            <button class="rounded-full bg-white px-8 py-4 text-lg font-semibold text-blue-900 hover:bg-blue-100">
                Create Free Account
            </button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 py-12 text-gray-400">
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
                <p>&copy; 2025 IT Helpdesk. All rights reserved.</p>
            </div>
        </div>
    </footer>

</template>
