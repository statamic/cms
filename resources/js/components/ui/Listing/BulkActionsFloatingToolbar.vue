<script setup>
/**
 * Bulk Actions Floating Toolbar
 *
 * Renders the floating toolbar when items are selected in a listing, with keyboard shortcuts
 * for each action. Shortcuts are derived from the localized action title (first unused letter).
 * Delete uses the backspace icon and is triggered by Delete/Backspace only.
 */
import { Motion } from 'motion-v';
import { computed, onMounted, onUnmounted } from 'vue';
import { Button, ButtonGroup, Icon } from '@ui';

// ——— Keyboard shortcut constants ———
const DESELECT_SHORTCUT_KEY = 'Escape';
const DESELECT_SHORTCUT_LABEL = 'Esc';
const DELETE_SHORTCUT_KEY = 'Delete';

const shortcutKeyClasses =
    'ms-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded bg-gray-200/75 px-1 font-semibold uppercase text-[0.625rem] text-gray-600 dark:bg-gray-800 dark:text-gray-400';

const props = defineProps({
    actions: { type: Array, default: () => [] },
    visible: { type: Boolean, default: false },
    selections: { type: Array, default: () => [] },
    clearSelections: { type: Function, default: null },
});

const hasSelections = computed(() => (props.selections?.length ?? 0) > 0);

/** True if this action is the built-in delete (by handle or trash icon). */
function isDeleteAction(action) {
    return action?.handle?.toLowerCase() === 'delete' || action?.icon === 'trash';
}

/**
 * First unused a–z letter from the action's (localized) title.
 * e.g. "Unpublish" → u, "Veröffentlichung aufheben" → v.
 */
function findShortcutKey(action, used) {
    const title = (action.title || '').toLowerCase();
    for (const char of title) {
        if (/[a-z]/.test(char) && !used.has(char)) return char;
    }

    return null;
}

const actionsWithShortcuts = computed(() => {
    const used = new Set();
    return (props.actions || []).map((action) => {
        // Delete always shows the backspace icon and is triggered by Delete/Backspace only; no letter.
        if (isDeleteAction(action)) {
            return { ...action, shortcutKey: null, shortcutLabel: null };
        }
        const key = findShortcutKey(action, used);
        if (key && key.length === 1) used.add(key);
        return { ...action, shortcutKey: key, shortcutLabel: key };
    });
});

// ——— Keyboard handler: skip when overlays or form controls have focus ———
function hasOpenOverlay() {
    return !!document.querySelector(
        '[data-ui-modal-content], .stack-content, [role="dialog"]'
    );
}

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
        const deleteAction = actionsWithShortcuts.value.find(isDeleteAction);
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

// Capture phase so Escape is handled before other listeners (e.g. command palette).
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
                    {{ __n(`Deselect :count item|Deselect all :count items`, selections.length) }}
                    <span :class="[shortcutKeyClasses, 'text-blue-600! bg-blue-100/80! dark:text-blue-400! dark:bg-blue-950!']">
                        {{ DESELECT_SHORTCUT_LABEL }}
                    </span>
                </Button>
                <Button
                    v-for="action in actionsWithShortcuts"
                    :key="action.handle"
                    :variant="action.dangerous ? 'danger' : 'default'"
                    @click="action.run"
                >
                    {{ __(action.title) }}
                    <!-- Delete always shows backspace icon; other actions show their shortcut letter. -->
                    <span
                        :class="[
                            shortcutKeyClasses,
                            'inline-flex items-center',
                            isDeleteAction(action) && 'ms-0.25!',
                            action.dangerous && '[&_svg]:text-red-600! dark:[&_svg]:text-red-500! [&_svg]:size-3.75! [&_svg]:opacity-70 dark:[&_svg]:opacity-80 bg-transparent dark:bg-transparent',
                        ]"
                    >
                        <Icon v-if="isDeleteAction(action)" name="backspace" class="size-3" />
                        <template v-else>{{ action.shortcutLabel }}</template>
                    </span>
                </Button>
            </ButtonGroup>
        </div>
    </Motion>
</template>

