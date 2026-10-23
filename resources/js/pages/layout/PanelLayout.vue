<script setup>
import { computed, provide, ref, watch, onMounted, onUnmounted, useTemplateRef } from 'vue';
import useResizable from '@/composables/use-resizable.js';
import useAiChat from '@/composables/use-ai-chat.js';
import AiChat from '@/components/ai-chat/AiChat.vue';
import AiChatCloseControl from '@/components/ai-chat/AiChatCloseControl.vue';
import AiChatDockControl from '@/components/ai-chat/AiChatDockControl.vue';
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
const { open: chatOpen, floating: chatFloating, messages: chatMessages } = useAiChat();

// Switching between floating and docked swaps the floating card in or out of the page. That isn't opening or closing the chat, so skip its fade then, or it plays as a distraction while the sidebar appears instantly.
const switchingLayout = ref(false);
watch(chatFloating, () => {
    switchingLayout.value = true;
    setTimeout(() => (switchingLayout.value = false), 100);
}, { flush: 'sync' });

// A quiet card until the input is focused: a plain grey 1px edge. Focusing it brings in the purple-to-blue gradient edge, a 1px blue line just inside it (so 2px in all, like a focused field's outline), and a purple glow. The outline is drawn over the edge, so nothing moves.
const chatCardClasses = 'rounded-xl border border-transparent ps-2.25 focus-within:outline-1 focus-within:-outline-offset-2 focus-within:outline-blue-400 focus-within:shadow-[0_10px_40px_-10px_color-mix(in_oklab,var(--color-primary)_45%,transparent)] [background:linear-gradient(white,white)_padding-box,linear-gradient(var(--color-gray-200),var(--color-gray-200))_border-box] dark:[background:linear-gradient(var(--color-gray-900),var(--color-gray-900))_padding-box,linear-gradient(var(--color-gray-700),var(--color-gray-700))_border-box] focus-within:[background:linear-gradient(white,white)_padding-box,linear-gradient(135deg,var(--color-primary),#60a5fa)_border-box] dark:focus-within:[background:linear-gradient(var(--color-gray-900),var(--color-gray-900))_padding-box,linear-gradient(135deg,var(--color-primary),#60a5fa)_border-box]';

// Once a chat has started, hovering the floating chat (while it isn't focused, which has its own stronger glow) brings in a softer version of the focus glow, at about half its strength. It eases in slowly, over 700ms, and out quicker. Before then it's just an input, so it stays quiet.
const chatCardHoverClasses = computed(() => (chatMessages.value.length ? '[&:hover:not(:focus-within)]:shadow-[0_10px_40px_-10px_color-mix(in_oklab,var(--color-primary)_22%,transparent)]' : ''));

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
    chat: { defaults: [230, 240, 260], storageKey: 'statamic.panels.ai-chat-width' },
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
            <div v-show="showChat" class="layout-panel-content group/chat relative flex flex-1 flex-col min-h-0" :data-mobile-open="chatOpen">
                <template v-if="!chatFloating">
                    <AiChat />
                    <AiChatCloseControl />
                    <AiChatDockControl />
                </template>
            </div>
        </div>
    </Teleport>

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
            :class="[chatCardClasses, chatCardHoverClasses]"
            class="transition-shadow duration-150 [&:hover:not(:focus-within)]:duration-700 fixed end-4.75 bottom-5 max-h-[calc(100dvh-11rem)] z-(--z-index-modal) group/chat flex w-84 max-w-[calc(100vw-2rem)] flex-col"
        >
            <AiChat />
            <AiChatCloseControl />
            <AiChatDockControl />
        </div>
    </Transition>

    <slot />
</template>
