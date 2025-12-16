<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Number, default: 5 }
});
const emit = defineEmits(['update:modelValue']);

const hover = ref(0);
const setRating = (n) => emit('update:modelValue', n);
watch(() => props.modelValue, (v) => {
  if (v < 0 || v > 5) emit('update:modelValue', 5);
});
</script>

<template>
  <div class="flex items-center gap-2" role="radiogroup" aria-label="Rating">
    <button
      v-for="n in 5" :key="n"
      :aria-checked="modelValue === n ? 'true' : 'false'"
      role="radio"
      type="button"
      @click="setRating(n)"
      @mouseover="hover = n"
      @mouseleave="hover = 0"
      class="text-2xl leading-none focus:outline-none"
      :class="(n <= (hover || modelValue)) ? 'text-yellow-400' : 'text-slate-300'">
      ★
    </button>
  </div>
</template>
