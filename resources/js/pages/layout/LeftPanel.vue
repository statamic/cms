<script setup>
import { inject, onBeforeUnmount, onMounted, ref } from 'vue';

const leftPanelActive = inject('leftPanelActive');
leftPanelActive.value = true;

const navWasOriginallyOpen = ref(null);

onMounted(() => {
    leftPanelActive.value = true;

    navWasOriginallyOpen.value = localStorage.getItem('statamic.nav') === 'open';
    Statamic.$events.$emit('nav.close');
});

onBeforeUnmount(() => {
    leftPanelActive.value = false;
    if (navWasOriginallyOpen.value) Statamic.$events.$emit('nav.open');
});
</script>

<template>
    <Teleport defer to="#left-panel">
        <slot />
    </Teleport>
</template>
