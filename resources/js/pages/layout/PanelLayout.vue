<script setup>
import { computed, provide, ref, watch, onMounted, onUnmounted, useTemplateRef } from 'vue';
import useResizable from '@/composables/use-resizable.js';
import useAiChat from '@/composables/use-ai-chat.js';
import AiChat from '@/components/ai-chat/AiChat.vue';
import AiChatDockControl from '@/components/ai-chat/AiChatDockControl.vue';
import AiChatNewControl from '@/components/ai-chat/AiChatNewControl.vue';
import { Icon, Tabs, TabList, TabTrigger } from '@ui';

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
const { open: chatOpen, floating: chatFloating, messages: chatMessages, draft: chatDraft, focusRequests: chatFocusRequests } = useAiChat();

// Prototype: what the floating card does when you click away from it while it's empty. Flip through them with the toggle in the bottom left.
const compactOptions = [
    { key: 'off', label: 'Stays full size' },
    { key: 'pill', label: 'Shrinks to a narrower pill' },
    { key: 'circle', label: 'Shrinks to a circle' },
];
const compactOptionKey = 'statamic.ai-chat.compact-option';
const compactOption = ref((() => {
    try {
        return Math.min(Number(localStorage.getItem(compactOptionKey)) || 0, compactOptions.length - 1);
    } catch {
        return 0;
    }
})());
watch(compactOption, (value) => {
    try {
        localStorage.setItem(compactOptionKey, String(value));
    } catch {}
});
const compactStyle = computed(() => compactOptions[compactOption.value].key);

// Whether focus is somewhere in the floating card. Opening the chat moves focus into it, so count it as focused straight away, or the card would grow from small as it fades in.
const chatCardFocused = ref(false);
watch(chatOpen, (isOpen) => isOpen && (chatCardFocused.value = true), { flush: 'sync' });
const onChatCardFocusOut = (event) => (chatCardFocused.value = event.currentTarget.contains(event.relatedTarget));

// Empty means nothing sent and nothing typed, so a half-written message is never tucked away.
const chatCompact = computed(() => compactStyle.value !== 'off' && !chatCardFocused.value && !chatMessages.value.length && !chatDraft.value.trim());
const chatCardSizeClasses = computed(() => {
    if (!chatCompact.value) return 'w-84 h-auto';
    return compactStyle.value === 'pill' ? 'w-52 h-auto' : 'w-11 h-11 rounded-[22px]! overflow-clip';
});
// Shrinking to a circle, keep the input at its full width (the card clips it) so it doesn't wrap and stretch the card taller on the way.
const chatContentClasses = computed(() => (compactStyle.value === 'circle' && !chatMessages.value.length ? '[&>[data-ai-chat]]:min-w-[325px]' : ''));

// Switching between floating and docked swaps the floating card in or out of the page. That isn't opening or closing the chat, so skip its fade then, or it plays as a distraction while the sidebar appears instantly.
const switchingLayout = ref(false);
watch(chatFloating, () => {
    switchingLayout.value = true;
    setTimeout(() => (switchingLayout.value = false), 100);
}, { flush: 'sync' });

// A quiet card until the input is focused: a plain grey 1px edge. Focusing it brings in the purple-to-blue gradient edge, a 1px blue line just inside it (so 2px in all, like a focused field's outline), and a purple glow. The outline is drawn over the edge, so nothing moves.
const chatCardClasses = 'rounded-xl border border-transparent ps-2.25 focus-within:outline-1 focus-within:-outline-offset-2 focus-within:outline-blue-400 focus-within:shadow-[0_10px_40px_-10px_color-mix(in_oklab,var(--color-primary)_45%,transparent)] [background:linear-gradient(white,white)_padding-box,linear-gradient(var(--color-gray-200),var(--color-gray-200))_border-box] dark:[background:linear-gradient(var(--color-gray-900),var(--color-gray-900))_padding-box,linear-gradient(var(--color-gray-700),var(--color-gray-700))_border-box] focus-within:[background:linear-gradient(white,white)_padding-box,linear-gradient(135deg,var(--color-primary),#60a5fa)_border-box] dark:focus-within:[background:linear-gradient(var(--color-gray-900),var(--color-gray-900))_padding-box,linear-gradient(135deg,var(--color-primary),#60a5fa)_border-box]';// The chat only uses the right panel when it's docked there, rather than floating in the corner.
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
                    <AiChatNewControl />
                    <AiChatDockControl />
                </template>
            </div>
        </div>
    </Teleport>

    <!-- The floating chat fades up as it opens and fades back down as it closes, the same movement both ways. Reduced motion skips it. It stays mounted while closed (hidden), so opening it doesn't wait on building the chat and the animation starts straight away. -->
    <!-- The card always carries its transition (it also eases its size as it shrinks when empty and unfocused), so the fade's active classes are only markers. -->
    <Transition
        :css="!switchingLayout"
        enter-active-class="ai-chat-card-fading"
        enter-from-class="translate-y-3 opacity-0"
        leave-active-class="ai-chat-card-fading"
        leave-to-class="translate-y-3 opacity-0"
    >
        <div
            v-if="chatFloating"
            v-show="chatOpen"
            :class="[chatCardClasses, chatCardSizeClasses, chatContentClasses]"
            class="fixed end-4.75 bottom-5 max-h-[calc(100dvh-11rem)] z-(--z-index-modal) group/chat flex max-w-[calc(100vw-2rem)] flex-col [interpolate-size:allow-keywords] transition-[opacity,translate,width,height,border-radius] duration-200 ease-out motion-reduce:transition-none"
            @focusin="chatCardFocused = true"
            @focusout="onChatCardFocusOut"
        >
            <AiChat />
            <AiChatNewControl />
            <AiChatDockControl />
            <!-- Shrunk to a circle, the card is just the chat icon. Clicking it puts you back in the input, which grows the card again. -->
            <Transition
                enter-active-class="transition-opacity duration-150 motion-reduce:transition-none"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-100 motion-reduce:transition-none"
                leave-to-class="opacity-0"
            >
                <button
                    v-if="chatCompact && compactStyle === 'circle'"
                    type="button"
                    tabindex="-1"
                    class="absolute inset-0 z-30 flex cursor-pointer items-center justify-center rounded-full bg-white text-gray-500 hover:text-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                    :aria-label="__('Ask anything…')"
                    @mousedown.prevent
                    @click="chatFocusRequests++"
                >
                    <Icon name="ai-chat" class="size-4.5" />
                </button>
            </Transition>
        </div>
    </Transition>

    <!-- Prototype: flips through what the floating card does when it's empty and unfocused -->
    <button
        v-if="chatFloating"
        type="button"
        class="fixed start-4 bottom-4 z-(--z-index-modal) cursor-pointer rounded-md border border-gray-300 bg-white px-2 py-1 font-mono text-xs text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
        @mousedown.prevent
        @click="compactOption = (compactOption + 1) % compactOptions.length"
    >
        {{ compactOption + 1 }}/{{ compactOptions.length }} · {{ compactOptions[compactOption].label }}
    </button>

    <slot />
</template>
