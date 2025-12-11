Narito ang isang **modern at professional design** para sa iyong `AuthenticatedLayout.vue`.

Binago ko ang design para magmukhang **"Admin Dashboard"** talaga:

1.  **Dark Sidebar:** Mas mataas ang contrast at mas madaling basahin ang menu.
2.  **Modern Icons:** Naglagay ako ng *generic icons* (SVG) para sa bawat section para hindi plain text lang.
3.  **Active State:** May "glow" effect at border sa kaliwa kapag active ang menu item.
4.  **Transitions:** Smooth ang pag-slide ng sidebar.

### Kopyahin at i-paste ito sa `AuthenticatedLayout.vue`

```vue
<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

// STATE: Control sidebar toggle
const isSidebarOpen = ref(true);

const page = usePage();
const auth = page.props.auth;
const userPermissions = auth && auth.user && auth.user.permissions ? auth.user.permissions : [];

// --- LOGIC: Building Menu Items (Walang binago sa logic mo) ---
const ticketsItems = [];
if (userPermissions.includes('view_mytickets_menu') || userPermissions.includes('show_mytickets')) {
    ticketsItems.push({ name: 'My Tickets', href: route('mytickets.index'), routeName: 'mytickets.*' });
}
if (userPermissions.includes('view_alltickets_menu') || userPermissions.includes('show_alltickets')) {
    ticketsItems.push({ name: 'All Tickets', href: route('alltickets.index'), routeName: 'alltickets.*' });
}

const reportingItems = [];
if (userPermissions.includes('view_ticket_analysis_menu') || userPermissions.includes('view_reports')) {
    reportingItems.push({ name: 'Ticket Analysis', href: '#', routeName: null });
}
if (userPermissions.includes('view_customer_ratings_menu') || userPermissions.includes('view_reports')) {
    reportingItems.push({ name: 'Customer Ratings', href: '#', routeName: null });
}

const settingsItems = [];
if (userPermissions.includes('view_helpdeskteams_menu') || userPermissions.includes('show_helpdeskteams')) {
    settingsItems.push({ name: 'Helpdesk Team', href: route('helpdeskteams.index'), routeName: 'helpdeskteams.*' });
}
if (userPermissions.includes('view_users_menu') || userPermissions.includes('show_users')) {
    settingsItems.push({ name: 'Users', href: route('users.index'), routeName: 'users.*' });
}
if (userPermissions.includes('view_employees_menu') || userPermissions.includes('show_employees')) {
    settingsItems.push({ name: 'Employee', href: route('employees.index'), routeName: 'employees.*' });
}
if (userPermissions.includes('view_customers_menu') || userPermissions.includes('show_customers')) {
    settingsItems.push({ name: 'Customer', href: route('customers.index'), routeName: 'customers.*' });
}
if (userPermissions.includes('view_departments_menu') || userPermissions.includes('show_departments')) {
    settingsItems.push({ name: 'Department', href: route('departments.index'), routeName: 'departments.*' });
}
if (userPermissions.includes('view_tags_menu') || userPermissions.includes('show_tags')) {
    settingsItems.push({ name: 'Tags', href: route('tags.index'), routeName: 'tags.*' });
}
if (userPermissions.includes('view_roles_menu') || userPermissions.includes('show_roles')) {
    settingsItems.push({ name: 'Roles', href: route('roles.index'), routeName: 'roles.*' });
}
if (userPermissions.includes('view_companies_menu') || userPermissions.includes('show_companies')) {
    settingsItems.push({ name: 'Company', href: route('companies.index'), routeName: 'companies.*' });
}
if (userPermissions.includes('view_canned_responses_menu') || userPermissions.includes('show_canned_responses')) {
    settingsItems.unshift({ name: 'Canned Responses', href: '#', routeName: null });
}
if (userPermissions.includes('view_logs_menu') || userPermissions.includes('show_logs')) {
    settingsItems.push({ name: 'Logs', href: '#', routeName: null });
}


const getIcon = (type) => {
    if (type === 'dashboard') return 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'; // Home
    if (type === 'tickets') return 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'; // Ticket
    if (type === 'reporting') return 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z'; // Chart
    if (type === 'settings') return 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'; // Cog
    return 'M4 6h16M4 12h16M4 18h16';
}

const tabs = [
    { name: 'Overview', type: 'tab', icon: 'dashboard', href: route('dashboard') },
    ...(ticketsItems.length ? [{ name: 'Tickets', type: 'dropdown', icon: 'tickets', active: (route().current('mytickets.*') || route().current('alltickets.*') || route().current('teamtickets.*')), items: ticketsItems }] : []),
    ...(reportingItems.length ? [{ name: 'Reporting', type: 'dropdown', icon: 'reporting', items: reportingItems }] : []),
    ...(settingsItems.length ? [{ name: 'Settings', type: 'dropdown', icon: 'settings', items: settingsItems }] : []),
];
</script>

<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden font-sans">
        
        <aside 
            class="flex-shrink-0 bg-slate-900 text-slate-300 flex flex-col transition-all duration-300 ease-in-out z-30"
            :class="[ isSidebarOpen ? 'w-64 translate-x-0' : 'w-0 -translate-x-full opacity-0 overflow-hidden' ]"
        >
            <div class="flex items-center gap-3 h-16 px-6 bg-slate-950 border-b border-slate-800 shrink-0">
                <ApplicationLogo class="w-9 h-8 text-blue-500 fill-current" />
                <span class="text-white font-bold text-lg tracking-wide whitespace-nowrap">IITS Helpdesk</span>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-6 scrollbar-thin scrollbar-thumb-slate-700">
                <template v-for="tab in tabs" :key="tab.name">
                    
                    <div v-if="tab.type === 'tab'">
                         <Link :href="tab.href"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group transform"
                            :class="route().current('dashboard') 
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 translate-x-2' 
                                : 'hover:bg-slate-800 hover:text-white hover:translate-x-2'"
                        >
                            <svg class="w-5 h-5 transition-colors" :class="route().current('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(tab.icon)" />
                            </svg>
                            <span class="font-medium text-sm whitespace-nowrap">{{ tab.name }}</span>
                        </Link>
                    </div>

                    <div v-else class="space-y-1">
                        <div class="px-3 mb-2 flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIcon(tab.icon)" />
                            </svg>
                            <span class="whitespace-nowrap">{{ tab.name }}</span>
                        </div>

                        <div class="space-y-1 ml-2 pl-2 border-l border-slate-700/50">
                            <Link v-for="item in tab.items" :key="item.name" :href="item.href"
                                class="block px-3 py-2 rounded-md text-sm transition-all duration-500 group transform"
                                :class="[
                                    (item.routeName && route().current(item.routeName)) 
                                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 translate-x-4' 
                                        : 'hover:bg-slate-800 hover:text-white hover:translate-x-4'
                                ]"
                            >
                                {{ item.name }}
                            </Link>
                        </div>
                    </div>

                </template>
            </nav>

            <div class="p-4 bg-slate-950 border-t border-slate-800 text-xs text-slate-500 whitespace-nowrap overflow-hidden">
                <p>&copy; 2025 Helpdesk Ticketing System</p>
                <p>v1.0.0</p>
            </div>
        </aside>


        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            
            <header class="h-16 bg-white border-b border-gray-200 shadow-sm flex items-center justify-between px-4 sm:px-6 z-20 sticky top-0">
                
                <div class="flex items-center gap-4">
                    <button 
                        @click="isSidebarOpen = !isSidebarOpen"
                        class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors"
                        title="Toggle Menu"
                    >
                        <svg v-if="isSidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <h2 v-if="$slots.header" class="font-semibold text-lg text-gray-800 leading-tight hidden sm:block">
                        <slot name="header" />
                    </h2>
                </div>

                <div class="flex items-center gap-4">
                    <button class="p-2 text-gray-400 hover:text-gray-600 transition relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                    </button>

                    <div class="h-6 w-px bg-gray-200 hidden sm:block"></div>

                    <div class="relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-3 focus:outline-none group">
                                    <div class="text-right hidden sm:block">
                                        <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition">{{ auth.user.first_name }}</p>
                                        <p class="text-xs text-gray-500">{{ auth.user.email }}</p>
                                    </div>
                                    <div class="h-9 w-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md ring-2 ring-transparent group-hover:ring-blue-100 transition">
                                        {{ auth.user.first_name[0] }}
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-3 border-b border-gray-100 sm:hidden">
                                     <p class="text-sm font-medium text-gray-800">{{ auth.user.first_name }} {{ auth.user.last_name }}</p>
                                     <p class="text-xs text-gray-500 truncate">{{ auth.user.email }}</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600 hover:bg-red-50"> Log Out </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-8 bg-gray-50/50">
                <slot />
            </main>
            
        </div>
    </div>
</template>

<style scoped>

.scrollbar-thin::-webkit-scrollbar {
  width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #475569;
  border-radius: 20px;
}
</style>
```