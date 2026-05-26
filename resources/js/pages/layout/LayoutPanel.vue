<script setup>
import { inject, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    side: {
        type: String,
        required: true,
        validator: (value) => ['left', 'right'].includes(value),
    },
    mobileOpen: {
        type: Boolean,
        default: false,
    },
});

const teleportTarget = props.side === 'left' ? '#left-panel' : '#right-panel';
const panelActive = inject(props.side === 'left' ? 'leftPanelActive' : 'rightPanelActive');

const navWasOriginallyOpen = ref(false);

onMounted(() => {
    panelActive.value = true;

    if (props.side === 'left') {
        navWasOriginallyOpen.value = localStorage.getItem('statamic.nav') !== 'closed';
        Statamic.$events.$emit('nav.close', { persist: false });
    }
});

onBeforeUnmount(() => {
    panelActive.value = false;

    if (props.side === 'left' && navWasOriginallyOpen.value) {
        Statamic.$events.$emit('nav.open', { persist: false });
    }
});
</script>

<template>
    <Teleport defer :to="teleportTarget">
        <div class="layout-panel-content" :data-mobile-open="mobileOpen">
            <slot />
        </div>
    </Teleport>
</template>
