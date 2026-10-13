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
    /** The tab label, shown when this panel shares the right-hand side with the AI chat */
    label: {
        type: String,
        default: null,
    },
});

const teleportTarget = props.side === 'left' ? '#left-panel' : '#right-panel-page';
const panelActive = inject(props.side === 'left' ? 'leftPanelActive' : 'rightPanelActive');
const panelLabel = props.side === 'right' ? inject('rightPanelLabel') : null;

const navWasOriginallyOpen = ref(false);

onMounted(() => {
    panelActive.value = true;
    if (panelLabel) panelLabel.value = props.label;

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
