<script setup>
import { Motion } from 'motion-v';
import { computed, onMounted, onUnmounted } from 'vue';
import { Button, ButtonGroup, Icon } from '@ui';

// Deselect uses Escape; we show "Esc" in the UI.
const DESELECT_SHORTCUT_KEY = 'Escape';
const DESELECT_SHORTCUT_LABEL = 'Esc';

// Shared styles for the keyboard shortcut badges next to each action.
const shortcutKeyClasses =
    'ms-2 inline-flex h-4 min-w-4 items-center justify-center rounded bg-gray-200/75 px-1 font-semibold uppercase text-2xs text-gray-700 dark:bg-gray-700 dark:text-gray-300';

// Delete uses the Delete/Backspace key; we show an icon, but still need the key for the handler.
const DELETE_SHORTCUT_KEY = 'Delete';
const DELETE_SHORTCUT_LABEL = 'Del';

// Built-in actions get a fixed shortcut. Custom actions fall back to a letter from their title.
const handleToShortcutKey = {
    unpublish: 'u',
    publish: 'p',
    delete: DELETE_SHORTCUT_KEY,
};

const props = defineProps({
    actions: { type: Array, default: () => [] },
    visible: { type: Boolean, default: false },
    selections: { type: Array, default: () => [] },
    clearSelections: { type: Function, default: null },
});

const hasSelections = computed(() => (props.selections?.length ?? 0) > 0);

// Non-letter keys (e.g. "Delete") need a short label for display; letters use the key as-is.
const specialKeyLabels = {
    [DELETE_SHORTCUT_KEY]: DELETE_SHORTCUT_LABEL,
};

/**
 * Picks a shortcut key for an action. Built-in actions use handleToShortcutKey.
 * Custom actions use the first unused a–z letter from the action title (e.g. "Upload" → "u", or "p" if "u" is taken).
 */
function findShortcutKey(action, used) {
    const explicit = handleToShortcutKey[action.handle];
    if (explicit) return explicit;

    const title = (action.title || '').toLowerCase();
    for (const char of title) {
        if (/[a-z]/.test(char) && !used.has(char)) return char;
    }

    return null;
}

const actionsWithShortcuts = computed(() => {
    const used = new Set();
    return (props.actions || []).map((action) => {
        const key = findShortcutKey(action, used);
        // Only reserve single-letter keys; Delete doesn't consume a letter.
        if (key && key.length === 1) used.add(key);
        const label = specialKeyLabels[key] ?? key;
        return { ...action, shortcutKey: key, shortcutLabel: label };
    });
});

// Don't trigger toolbar shortcuts when a modal, stack, or dialog is open.
function hasOpenOverlay() {
    return !!document.querySelector(
        '[data-ui-modal-content], .stack-content, [role="dialog"]'
    );
}

// Don't trigger when the user is typing in an input, textarea, select, or contenteditable.
function isInsideFormControl(event) {
    const el = event.target;
    if (!el) return false;
    const tag = el.tagName;
    return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el.isContentEditable;
}

function onKeydown(event) {
    if (!props.visible || !hasSelections.value) return;
    if (hasOpenOverlay() || isInsideFormControl(event)) return;

    if (event.key === DESELECT_SHORTCUT_KEY) {
        props.clearSelections?.();
        event.preventDefault();
        event.stopPropagation();
        return;
    }
    if (event.key === DELETE_SHORTCUT_KEY || event.key === 'Backspace') {
        const deleteAction = actionsWithShortcuts.value.find((a) => a.handle === 'delete');
        if (deleteAction?.run) {
            deleteAction.run();
            event.preventDefault();
        }
        return;
    }

    // Single-letter shortcuts: ignore when a modifier is held.
    if (event.metaKey || event.ctrlKey || event.altKey) return;
    const key = event.key?.length === 1 ? event.key.toLowerCase() : null;
    if (!key) return;
    const action = actionsWithShortcuts.value.find((a) => a.shortcutKey === key);
    if (action?.run) {
        action.run();
        event.preventDefault();
    }
}

// Capture phase so we can handle Escape before other listeners (e.g. command palette).
onMounted(() => document.addEventListener('keydown', onKeydown, true));
onUnmounted(() => document.removeEventListener('keydown', onKeydown, true));
</script>

<template>
    <Motion
        v-if="visible"
        layout
        data-floating-toolbar
        class="pointer-events-none sticky inset-x-0 bottom-1 sm:bottom-6 z-(--z-index-above) flex w-full max-w-[95vw] mx-auto justify-center"
        :initial="{ y: 100, opacity: 0 }"
        :animate="{ y: 0, opacity: 1 }"
        :transition="{ duration: 0.2, ease: 'easeInOut' }"
    >
        <div class="pointer-events-auto space-y-3 rounded-xl border border-gray-300/60 dark:border-gray-700 p-1 bg-gray-200/55 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)] dark:bg-gray-800 dark:shadow-[0_10px_15px_rgba(0,0,0,.5)] dark:inset-shadow-2xs dark:inset-shadow-white/10">
            <ButtonGroup>
                <Button
                    class="text-blue-500!"
                    @click="clearSelections?.()"
                >
                    {{ __n(`Deselect :count item|Deselect all :count items`, selections.length) }} <span :class="[shortcutKeyClasses, 'text-blue-600! bg-blue-100/80! dark:text-blue-400 dark:bg-blue-900']">{{ DESELECT_SHORTCUT_LABEL }}</span>
                </Button>
                <Button
                    v-for="action in actionsWithShortcuts"
                    :key="action.handle"
                    :variant="action.dangerous ? 'danger' : 'default'"
                    @click="action.run"
                >
                    {{ __(action.title) }}
                    <!-- Delete shows backspace icon; other actions show their shortcut letter. -->
                    <span
                        :class="[
                            shortcutKeyClasses,
                            'inline-flex items-center',
                            action.handle === 'delete' && 'ms-0.25!',
                            action.dangerous && '[&_svg]:text-red-600! [&_svg]:size-4! bg-transparent dark:text-red-400 dark:bg-red-900',
                        ]"
                    >
                        <Icon v-if="action.handle === 'delete'" name="backspace" class="size-3" />
                        <template v-else>{{ action.shortcutLabel }}</template>
                    </span>
                </Button>
            </ButtonGroup>
        </div>
    </Motion>
</template>

