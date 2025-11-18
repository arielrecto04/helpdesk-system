<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();
const auth = page.props.auth;

const tabs = [
    { name: 'Overview', type: 'tab', href: route('dashboard') },
    { name: 'Tickets', type: 'dropdown', active: route().current('tickets.*'), items: [{name: 'My Tickets', href: route('tickets.my')}, {name: 'All Tickets', href: route('tickets.index')}] },
    { name: 'Reporting', type: 'dropdown', items: [{name: 'Ticket Analysis', href: '#'}, {name: 'Customer Ratings', href: '#'}] },
    { name: 'Settings', type: 'dropdown', items: [{name: 'Helpdesk Team', href: route('helpdeskteams.index')}, {name: 'Canned Responses', href: '#'}, {name: 'Users', href: route('users.index')}, {name: 'Employee', href: route('employees.index')}, {name: 'Customer', href: route('customers.index')}, {name: 'Department', href: route('departments.index')}, {name: 'Tags',  href: route('tags.index')}, {name: 'Roles', href: '#'}, {name: 'Company', href: '#'}, {name: 'Logs', href: '#'}]}
];

const setActiveTab = (tabName) => {
    activeTab.value = tabName;
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav
                class="border-b border-gray-100 bg-white"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Main Tabs -->
                            <div class="hidden sm:flex sm:space-x-4 sm:ms-10">
                                <template v-for="tab in tabs" :key="tab.name">
                                    <div v-if="tab.type === 'dropdown'" class="relative">
                                        <Dropdown align="left" width="48">
                                            <template #trigger>
                                                <button
                                                    :class="[
                                                        'px-3 py-2 text-sm font-medium rounded-md inline-flex items-center focus:outline-none',
                                                        tab.active  // <-- Pinalitan ito
                                                            ? 'bg-blue-100 text-blue-700'
                                                            : 'text-gray-500 hover:text-gray-700'
                                                    ]"
                                                >
                                                    <span>{{ tab.name }}</span>
                                                    <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </template>
                                            <template #content>
                                                <DropdownLink v-for="item in tab.items" :key="item.name" :href="item.href">
                                                    {{ item.name }}
                                                </DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>
                                    <Link v-else :href="tab.href"
                                        
                                        :class="[
                                            'px-3 py-2 text-sm font-medium rounded-md',
                                            tab.active  // <-- Pinalitan din ito
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'text-gray-500 hover:text-gray-700'
                                        ]"
                                        :aria-current="tab.active ? 'page' : undefined" 
                                    >{{ tab.name }}</Link>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- User Profile -->
                            <span class="text-sm text-gray-700">{{ auth.user.first_name }} {{ auth.user.last_name }}</span>
                            <div class="relative ms-4">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="rounded-full bg-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <!-- Responsive Navigation Menu -->
                    <div class="space-y-1 pb-3 pt-2">
                        <template v-for="tab in tabs" :key="tab.name">

                            <!-- Kung 'tab' type, ipakita lang ito -->
                            <ResponsiveNavLink v-if="tab.type === 'tab'" :href="tab.href" :active="route().current(tab.href.split('/').pop())">
                                {{ tab.name }}
                            </ResponsiveNavLink>

                            <!-- Kung HINDI 'tab' type (ibig sabihin, dropdown), ipakita ito -->
                            <template v-else>
                                <div class="px-4 py-2 text-sm text-gray-500">{{ tab.name }}</div>
                                <ResponsiveNavLink v-for="item in tab.items" :key="item.name" :href="item.href" class="ps-8">
                                    {{ item.name }}
                                </ResponsiveNavLink>
                            </template>
                            
                        </template>
                    </div>
                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ auth.user.first_name }} {{ auth.user.last_name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
