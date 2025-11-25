<script setup>
import { ref } from 'vue';
import { TabContent } from '@ui';

const emit = defineEmits(['revealed']);

const wrapper = ref();

Statamic.$reveal.use(wrapper, () => emit('revealed'));
</script>

<template>
    <TabContent>
        <div ref="wrapper">
            <slot />
        </div>
    </TabContent>
</template>
