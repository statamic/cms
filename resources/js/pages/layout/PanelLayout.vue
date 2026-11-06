<script setup>
import { computed, provide, ref, watch, onMounted, onUnmounted, useTemplateRef } from 'vue';
import useResizable from '@/composables/use-resizable.js';
import useAiChat from '@/composables/use-ai-chat.js';
import AiChat from '@/components/ai-chat/AiChat.vue';
import useAiChanges from '@/composables/use-ai-changes.js';
import AiChatCloseControl from '@/components/ai-chat/AiChatCloseControl.vue';
import AiChatDockControl from '@/components/ai-chat/AiChatDockControl.vue';
import AiChatMoveHint from '@/components/ai-chat/AiChatMoveHint.vue';
import { moving, writing } from '@/components/ai-chat/mock-runs.js';
import { Tabs, TabList, TabTrigger } from '@ui';

const minWidth = 175;
// The docked chat needs more room than a page panel: its input holds an icon, the placeholder, a hint or Clear, and send
const chatMinWidth = 280;
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
const { open: chatOpen, floating: chatFloating, messages: chatMessages } = useAiChat();
// Start keeping track of the open publish form now, before any page's form mounts, so the chat can fill it in
useAiChanges();

// Prototype: while the AI chat fills in the page's fields, the page (and its side panels, but not the chat) is inert, so it can't be
// clicked, typed in or tabbed to, and it fades back. The global header says why (see AiChatMoveHint.vue).
const locked = computed(() => writing.active || undefined);
const lockedClasses = computed(() => ['transition-opacity duration-300', writing.active ? 'opacity-60' : '']);

// A docked chat is lifted above the screen's dimming while the chat moves you (its messages and input dim themselves, see AiChat.vue). The dimming takes 300ms
// to fade away once the move is over, so the lift stays for that long too: dropping it at once puts the dimming over the chat, which then dims twice as the two
// fade together, and flickers.
const chatLifted = ref(false);
let liftTimer;
watch(
    () => moving.title,
    (title) => {
        clearTimeout(liftTimer);
        if (title) chatLifted.value = true;
        else liftTimer = setTimeout(() => (chatLifted.value = false), 350);
    },
);

// Switching between floating and docked swaps the floating card in or out of the page. That isn't opening or closing the chat, so skip its fade then, or it plays as a distraction while the sidebar appears instantly.
const switchingLayout = ref(false);
watch(chatFloating, () => {
    switchingLayout.value = true;
    setTimeout(() => (switchingLayout.value = false), 100);
}, { flush: 'sync' });

// While a run is working, or waiting for you, the floating chat keeps its focused look, even once focus has moved to the page
const chatWorking = computed(() => chatMessages.value.some((message) => ['working', 'waiting'].includes(message.run?.status)));

// Once it stops, the card is marked as released for a second, while the focused look fades away (see the style block)
const chatReleased = ref(false);
let releaseTimer;
watch(chatWorking, (working) => {
    clearTimeout(releaseTimer);
    chatReleased.value = !working;
    if (!working) releaseTimer = setTimeout(() => (chatReleased.value = false), 1200);
});

// The chat only uses the right panel when it's docked there, rather than floating in the corner.
const chatInPanel = computed(() => chatOpen.value && !chatFloating.value);
const rightTab = ref('chat');
const showRightTabs = computed(() => pagePanelActive.value && chatInPanel.value);
const showPagePanel = computed(() => pagePanelActive.value && (!chatInPanel.value || rightTab.value === 'page'));
const showChat = computed(() => chatInPanel.value && (!pagePanelActive.value || rightTab.value === 'chat'));

watch(chatInPanel, (inPanel) => (rightTab.value = inPanel ? 'chat' : 'page'));

// The chat has its own default and remembered width, separate from page panels.
const rightSizes = {
    page: { defaults: [300, 290, 340], storageKey: 'statamic.panels.right-width' },
    chat: { defaults: [280, 300, 320], storageKey: 'statamic.panels.ai-chat-width' },
};

const right = {
    ref: useTemplateRef('rightPanel'),
    active: computed(() => pagePanelActive.value || chatInPanel.value),
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
        minWidth: panel === right ? () => (showChat.value ? chatMinWidth : minWidth) : minWidth,
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
    el.style.width = Number.isFinite(stored) ? `${Math.max(stored, showChat.value ? chatMinWidth : minWidth)}px` : getDefaultWidth(right);
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
            class="-order-1 sticky top-0 overflow-y-scroll overscroll-y-contain overflow-x-clip h-full min-[1000px]:grid mx-auto focus:outline-none pe-2"
            :class="lockedClasses"
            :inert="locked"
        ></div>
    </Teleport>

    <Teleport defer to="#main-content">
        <div
            v-show="right.active.value"
            :data-right-panel="right.active.value ? '' : undefined"
            ref="rightPanel"
            id="right-panel"
            tabindex="-1"
            class="order-2 sticky top-0 flex flex-col overflow-y-scroll overscroll-y-contain h-full min-[1000px]:-me-2 mx-auto focus:outline-none ps-2"
            :class="{ 'z-(--z-index-max)': chatInPanel && chatLifted }"
        >
            <Tabs v-if="showRightTabs" v-model="rightTab">
                <TabList class="mx-0! px-3 pt-2">
                    <TabTrigger name="page" :text="pagePanelLabel || __('Settings')" />
                    <TabTrigger name="chat" :text="__('Ask')" />
                </TabList>
            </Tabs>
            <div v-show="showPagePanel" id="right-panel-page" class="min-[1000px]:grid flex-1" :class="lockedClasses" :inert="locked"></div>
            <div v-show="showChat" class="layout-panel-content group/chat relative flex flex-1 flex-col min-h-0" :data-mobile-open="chatOpen">
                <template v-if="!chatFloating">
                    <AiChat />
                    <AiChatCloseControl />
                    <AiChatDockControl />
                </template>
            </div>
        </div>
    </Teleport>

    <!-- Before the floating chat, so the dimming it brings over the page (and the nav) stays under the chat. A docked chat is lifted above it while it's there. -->
    <AiChatMoveHint />

    <!-- The floating chat fades up as it opens and fades back down as it closes, the same movement both ways. Reduced motion skips it. It stays mounted while closed (hidden), so opening it doesn't wait on building the chat and the animation starts straight away. -->
    <Transition
        :css="!switchingLayout"
        enter-active-class="transition duration-200 ease-out motion-reduce:transition-none"
        enter-from-class="translate-y-3 opacity-0"
        leave-active-class="transition duration-200 ease-out motion-reduce:transition-none"
        leave-to-class="translate-y-3 opacity-0"
    >
        <div
            v-if="chatFloating"
            v-show="chatOpen"
            :data-started="chatMessages.length ? '' : undefined"
            :data-working="chatWorking || undefined"
            :data-released="chatReleased || undefined"
            class="ai-chat-card rounded-xl border border-transparent ps-2.25 transition-shadow duration-150 [&:hover:not(:focus-within)]:duration-700 fixed end-4.75 bottom-5 max-h-[calc(100dvh-11rem)] z-(--z-index-modal) group/chat flex w-84 max-w-[calc(100vw-2rem)] flex-col"
        >
            <AiChat />
            <AiChatCloseControl />
            <AiChatDockControl />
        </div>
    </Transition>


    <!-- Prototype: locked while the AI chat fills in the page (see writing) -->
    <div :inert="locked" :class="lockedClasses">
        <slot />
    </div>
</template>
