<script setup>
import { ref } from 'vue'
import HeroIcon from './HeroIcon.vue' // Assumes HeroIcon.vue is in the same folder

// Props definition from your request
defineProps({
    links: {
        type: Array,
        required: true,
        default: () => [
            {
                url: "#",
                label: "Default Home", // Corrected 'lable' to 'label'
                icon: "home"
            }
        ]
    }
})

// State to manage the collapsed/expanded state
const isCollapsed = ref(false)

// Function to toggle the state
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value
}
</script>

<template>
    <aside
        class="flex flex-col h-screen bg-gray-900 text-gray-100 transition-all duration-300 ease-in-out"
        :class="[isCollapsed ? 'w-20' : 'w-64']"
    >
        <!-- Header with Logo/Title and Toggle Button -->
        <div class="flex items-center h-16 px-4 border-b border-gray-700" :class="[isCollapsed ? 'justify-center' : 'justify-between']">
            <h1 v-if="!isCollapsed" class="text-xl font-bold text-white whitespace-nowrap">My App</h1>
            <button
                @click="toggleSidebar"
                class="p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
            >
                <HeroIcon :name="isCollapsed ? 'chevron-double-right' : 'chevron-double-left'" />
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto">
            <ul class="py-4 space-y-1">
                <li v-for="link in links" :key="link.label">
                    <a
                        :href="link.url"
                        class="flex items-center p-4 mx-2 space-x-4 rounded-md hover:bg-gray-700 transition-colors"
                        :class="[isCollapsed ? 'justify-center' : '']"
                    >
                        <HeroIcon :name="link.icon" class="flex-shrink-0 w-6 h-6" />
                        <span v-if="!isCollapsed" class="font-medium whitespace-nowrap">{{ link.label }}</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Footer Area (e.g., User Profile) -->
        <div class="border-t border-gray-700">
                <a
                href="#"
                class="flex items-center p-4 mx-2 space-x-4 rounded-md hover:bg-gray-700 transition-colors"
                :class="[isCollapsed ? 'justify-center' : '']"
            >
                <img 
                    src="[https://placehold.co/40x40/667eea/white?text=U](https://placehold.co/40x40/667eea/white?text=U)" 
                    alt="User Avatar" 
                    class="w-10 h-10 rounded-full"
                    :class="[isCollapsed ? '' : 'w-10 h-10']"
                />
                    <div v-if="!isCollapsed" class="whitespace-nowrap">
                    <p class="font-semibold">John Doe</p>
                    <p class="text-sm text-gray-400">View Profile</p>
                    </div>
            </a>
        </div>
    </aside>
</template>