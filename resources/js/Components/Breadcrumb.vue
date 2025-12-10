<script setup>
const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  }
});
</script>

<template>
  <nav class="flex items-center text-sm" aria-label="Breadcrumb">
    <ol class="inline-flex items-center">
      <li v-for="(item, idx) in items" :key="idx" class="inline-flex items-center">
        <div v-if="idx === 0" class="inline-flex items-center text-gray-200 hover:text-white">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2 2v6a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-6m0 0L9 6m6 6l-6-6" />
          </svg>
          <a :href="item.href || '#'" class="font-medium">{{ item.label }}</a>
        </div>
        <div v-else class="flex items-center">
          <svg class="w-4 h-4 text-white/60 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
          <component :is="(idx === items.length - 1 || !item.href) ? 'span' : 'a'"
                     :href="item.href || undefined"
                     :class="[idx === items.length - 1 ? 'text-white font-semibold' : 'text-blue-100 hover:text-white']">
            {{ item.label }}
          </component>
        </div>
      </li>
    </ol>
  </nav>
</template>
