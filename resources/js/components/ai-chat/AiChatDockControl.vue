<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useTemplateRef, watch } from 'vue';
import { Icon } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { floating, focusRequests } = useAiChat();

// This is the last thing in the chat, so Tab from it loops back to the input rather than leaving the card.
function loopToInput(event) {
    if (event.shiftKey) return;

    event.preventDefault();
    focusRequests.value++;
}

// Docked, the button sits right on the corner where the sidebar's edge meets the input's divider. The sidebar clips anything that crosses its edge, so it's fixed to the viewport instead, and placed from where the sidebar and the input are. It follows them as the sidebar is resized or the input grows.
const button = useTemplateRef('button');
const docked = computed(() => !floating.value);
const position = ref(null);
let observer = null;

function measure() {
    const panel = button.value?.parentElement;
    const form = panel?.querySelector('[data-ai-chat] form');
    if (!panel || !form) return;

    const panelRect = panel.getBoundingClientRect();
    const formRect = form.getBoundingClientRect();
    // Centred on the corner: half the button's 28px either side of the line and the divider. The line is the edge of the page's card, 9px before the panel starts (its padding and the gap).
    position.value = { left: panelRect.left - 9 - 14, top: formRect.top - 14 };
}

function observe() {
    observer?.disconnect();
    if (!docked.value) return (position.value = null);

    nextTick(() => {
        const panel = button.value?.parentElement;
        observer = new ResizeObserver(measure);
        if (panel) observer.observe(panel);
        const form = panel?.querySelector('[data-ai-chat] form');
        if (form) observer.observe(form);
        measure();
    });
}

onMounted(() => {
    observe();
    window.addEventListener('resize', measure);
});
onBeforeUnmount(() => {
    observer?.disconnect();
    window.removeEventListener('resize', measure);
});
watch(docked, observe);

const label = computed(() => (floating.value ? __('Dock to sidebar') : __('Float in corner')));
const icon = computed(() => (floating.value ? 'bar-sidebar-left-panel-open' : 'bar-sidebar-right-panel-open'));
</script>

<template>
    <!-- Switches between the floating card and the sidebar. Floating, it straddles the card's corner so it never covers the input. -->
    <button
        ref="button"
        type="button"
        :aria-label="label"
        v-tooltip="label"
        :class="[
            // The same look in both layouts: a small button on the edge of the chat, level with the input's divider, so it stays put as messages make the chat grow
            'z-10 flex size-7 cursor-pointer items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 opacity-0 shadow-md transition-opacity hover:text-gray-900 focus-visible:opacity-100 focus-visible:focus-outline group-hover/chat:opacity-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:text-white',
            // Floating it straddles the card's right edge. Docked it's fixed in place (see above).
            floating ? 'absolute -end-3.5 bottom-[37.5px]' : 'fixed',
        ]"
        :style="docked && position ? { left: position.left + 'px', top: position.top + 'px' } : undefined"
        @click="floating = !floating"
        @keydown.tab="loopToInput"
    >
        <Icon :name="icon" class="size-3.5" />
    </button>
</template>
