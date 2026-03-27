<script setup>
import { inject, onBeforeUnmount, onMounted, ref } from 'vue';

const rightPanelActive = inject('rightPanelActive');
rightPanelActive.value = true;

const navWasOriginallyOpen = ref(null);

onMounted(() => {
    rightPanelActive.value = true;

    navWasOriginallyOpen.value = localStorage.getItem('statamic.nav') === 'open';
    Statamic.$events.$emit('nav.close');
});

onBeforeUnmount(() => {
    rightPanelActive.value = false;
    if (navWasOriginallyOpen.value) Statamic.$events.$emit('nav.open');
});
</script>

<template>
    <Teleport defer to="#right-panel">
        <slot />
    </Teleport>
</template>
