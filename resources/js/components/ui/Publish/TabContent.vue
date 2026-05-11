<script setup>
import { ref } from 'vue';
import { TabContent } from '@ui';
import { reveal } from '@api';

const emit = defineEmits(['revealed']);

const wrapper = ref();

reveal.use(wrapper, () => emit('revealed'));
</script>

<template>
    <TabContent>
        <div ref="wrapper">
            <slot />
        </div>
    </TabContent>
</template>
