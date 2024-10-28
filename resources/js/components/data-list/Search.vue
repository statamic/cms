<script setup>
import { ref } from 'vue';
import _ from 'underscore';

const props = defineProps({
    placeholder: {
        type: String,
        default: 'Search...'
    },
})

const modelValue = defineModel();
const input = ref(null)

const emitEvent = _.debounce(function (e) {
    modelValue.value = e.target.value;
}, 300);

function reset() {
    modelValue.value = '';
}

function focus() {
    input.value?.focus();
}

defineExpose({
    focus,
})
</script>

<template>
    <input
        type="text"
        ref="input"
        :placeholder="__(placeholder)"
        :value="modelValue"
        @input="emitEvent"
        @keyup.esc="reset"
        autofocus
        class="input-text flex-1 bg-white dark:bg-dark-600 text-sm focus:border-blue-300 dark:focus:border-dark-blue-125 outline-0"
    >
</template>
