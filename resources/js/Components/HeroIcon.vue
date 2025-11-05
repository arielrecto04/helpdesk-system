<script setup>
import { computed } from 'vue'
// Import the icon paths from a separate file
import { iconPaths } from './icons.js'

const props = defineProps({
    name: {
        type: String,
        required: true
    }
})

// Find the correct SVG path, or use a fallback
const path = computed(() => iconPaths[props.name] || iconPaths['fallback'])
</script>

<template>
    <svg
        xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="w-6 h-6"
    >
        <path stroke-linecap="round" stroke-linejoin="round" :d="path" />
    </svg>
</template>
