<script setup>
import { computed, provide, ref, watch, onMounted, onUnmounted, useTemplateRef } from 'vue';
import useResizable from '@/composables/use-resizable.js';
import useAiChat from '@/composables/use-ai-chat.js';
import AiChat from '@/components/ai-chat/AiChat.vue';
import { Tabs, TabList, TabTrigger } from '@ui';

const minWidth = 175;
const maxWidth = 450;
const paddingOffset = '0.5rem';
const breakpoints = { narrow: 1250, compact: 1400 };

const left = {
    ref: useTemplateRef('leftPanel'),
    active: ref(false),
    edge: 'right',
    defaults: [minWidth, 270, 300],
    storageKey: 'statamic.panels.left-width',
};

// The right panel is shared by the page (e.g. an inspector) and the AI chat. When both want it, they become tabs.
const pagePanelActive = ref(false);
const pagePanelLabel = ref(null);
const { open: chatOpen } = useAiChat();
const rightTab = ref('chat');
const showRightTabs = computed(() => pagePanelActive.value && chatOpen.value);
const showPagePanel = computed(() => !chatOpen.value || !pagePanelActive.value || rightTab.value === 'page');
const showChat = computed(() => chatOpen.value && (!pagePanelActive.value || rightTab.value === 'chat'));

watch(chatOpen, (open) => (rightTab.value = open ? 'chat' : 'page'));

// The chat has its own default and remembered width, separate from page panels.
const rightSizes = {
    page: { defaults: [300, 290, 340], storageKey: 'statamic.panels.right-width' },
    chat: { defaults: [230, 240, 260], storageKey: 'statamic.panels.ai-chat-width' },
};

const right = {
    ref: useTemplateRef('rightPanel'),
    active: computed(() => pagePanelActive.value || chatOpen.value),
    edge: 'left',
    get defaults() {
        return rightSizes[showChat.value ? 'chat' : 'page'].defaults;
    },
    get storageKey() {
        return rightSizes[showChat.value ? 'chat' : 'page'].storageKey;
    },
};

provide('leftPanelActive', left.active);
provide('rightPanelActive', pagePanelActive);
provide('rightPanelLabel', pagePanelLabel);

function formatWidth(panel, width) {
    return panel === left ? `calc(${width}px - ${paddingOffset})` : `${width}px`;
}

function getDefaultWidth(panel) {
    const w = window.innerWidth;
    if (w < breakpoints.narrow) return formatWidth(panel, panel.defaults[0]);
    if (w < breakpoints.compact) return formatWidth(panel, panel.defaults[1]);
    return formatWidth(panel, panel.defaults[2]);
}

const { makeResizable } = useResizable();

[left, right].forEach((panel) => {
    makeResizable(panel.ref, panel.active, {
        edge: panel.edge,
        minWidth,
        maxWidth,
        defaultWidth: () => getDefaultWidth(panel),
        storageKey: () => panel.storageKey,
    });
});

// Switching between the chat and a page panel swaps to that one's width.
watch(showChat, () => {
    const el = right.ref.value;
    if (!el || !right.active.value) return;

    const stored = parseInt(localStorage.getItem(right.storageKey), 10);
    el.style.width = Number.isFinite(stored) ? `${stored}px` : getDefaultWidth(right);
});

function applyBreakpointDefaults() {
    [left, right].forEach((panel) => {
        if (!panel.active.value) return;
        if (localStorage.getItem(panel.storageKey)) return;

        const el = panel.ref.value;
        if (!el) return;

        // Widths we treat as "unmodified by the user"
        const priorDefaults = new Set(panel.defaults.map((d) => formatWidth(panel, d)));
        const current = el.style.width;
        if (!current || priorDefaults.has(current)) {
            el.style.width = getDefaultWidth(panel);
        }
    });
}

watch(
    [left.active, right.active],
    () => {
        const el = document.getElementById('main-content');
        if (!el) return;

        if (left.active.value || right.active.value) {
            el.setAttribute('data-panels-showing', '');
        } else {
            el.removeAttribute('data-panels-showing');
        }
    },
);

onMounted(() => {
    applyBreakpointDefaults();
    window.addEventListener('resize', applyBreakpointDefaults);
});

onUnmounted(() => {
    window.removeEventListener('resize', applyBreakpointDefaults);
    document.getElementById('main-content')?.removeAttribute('data-panels-showing');
});
</script>

<template>
    <Teleport defer to="#main-content">
        <div
            v-show="left.active.value"
            :data-left-panel="left.active.value ? '' : undefined"
            ref="leftPanel"
            id="left-panel"
            tabindex="-1"
            style="order: -1"
            class="sticky top-0 overflow-y-scroll overscroll-y-contain overflow-x-clip h-full min-[1000px]:grid mx-auto focus:outline-none pe-2"
        ></div>
    </Teleport>

    <Teleport defer to="#main-content">
        <div
            v-show="right.active.value"
            :data-right-panel="right.active.value ? '' : undefined"
            ref="rightPanel"
            id="right-panel"
            tabindex="-1"
            style="order: 2"
            class="sticky top-0 flex flex-col overflow-y-scroll overscroll-y-contain h-full min-[1000px]:-mr-2 mx-auto focus:outline-none ps-2"
        >
            <Tabs v-if="showRightTabs" v-model="rightTab">
                <TabList class="mx-0! px-3 pt-2">
                    <TabTrigger name="page" :text="pagePanelLabel || __('Settings')" />
                    <TabTrigger name="chat" :text="__('Ask')" />
                </TabList>
            </Tabs>
            <div v-show="showPagePanel" id="right-panel-page" class="min-[1000px]:grid flex-1"></div>
            <div v-show="showChat" class="layout-panel-content flex-1 min-h-0" :data-mobile-open="chatOpen">
                <AiChat />
            </div>
        </div>
    </Teleport>

    <slot />
</template>
