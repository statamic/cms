<script setup>
import { Motion } from 'motion-v';
import { computed, onMounted, onUnmounted } from 'vue';
import { Button, ButtonGroup } from '@ui';

const DESELECT_SHORTCUT_KEY = 'x';

const shortcutKeyClasses =
    'ml-2 inline-flex h-4 min-w-4 items-center justify-center rounded bg-gray-200/75 px-1 font-semibold uppercase text-2xs text-gray-700 dark:bg-gray-700 dark:text-gray-300';

const handleToShortcutKey = {
    unpublish: 'u',
    publish: 'p',
    delete: 'e',
};

const props = defineProps({
    actions: { type: Array, default: () => [] },
    visible: { type: Boolean, default: false },
    selections: { type: Array, default: () => [] },
    clearSelections: { type: Function, default: null },
});

const hasSelections = computed(() => (props.selections?.length ?? 0) > 0);

const actionsWithShortcuts = computed(() => {
    const used = new Set([DESELECT_SHORTCUT_KEY]);
    return (props.actions || []).map((action) => {
        let key = handleToShortcutKey[action.handle] ?? (action.title?.[0]?.toLowerCase() || '').replace(/[^a-z]/, '');
        if (!key || used.has(key)) {
            for (const c of 'abcdefghijklmnopqrstuvwxyz') {
                if (!used.has(c)) {
                    key = c;
                    break;
                }
            }
        }
        if (key) used.add(key);
        return { ...action, shortcutKey: key };
    });
});

function onKeydown(event) {
    if (!props.visible || !hasSelections.value) return;
    if (event.metaKey || event.ctrlKey || event.altKey) return;
    const key = event.key?.length === 1 ? event.key.toLowerCase() : null;
    if (!key) return;
    if (key === DESELECT_SHORTCUT_KEY) {
        props.clearSelections?.();
        event.preventDefault();
        return;
    }
    const action = actionsWithShortcuts.value.find((a) => a.shortcutKey === key);
    if (action?.run) {
        action.run();
        event.preventDefault();
    }
}

onMounted(() => document.addEventListener('keydown', onKeydown));
onUnmounted(() => document.removeEventListener('keydown', onKeydown));
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
                    {{ __n(`Deselect :count item|Deselect all :count items`, selections.length) }} <span :class="[shortcutKeyClasses, 'text-blue-600! bg-blue-100/80! dark:text-blue-400 dark:bg-blue-900']">{{ DESELECT_SHORTCUT_KEY }}</span>
                </Button>
                <Button
                    v-for="action in actionsWithShortcuts"
                    :key="action.handle"
                    :variant="action.dangerous ? 'danger' : 'default'"
                    @click="action.run"
                >
                    {{ __(action.title) }} <span :class="[shortcutKeyClasses, action.dangerous && 'text-red-600 bg-red-100/80! dark:text-red-400 dark:bg-red-900']">{{ action.shortcutKey }}</span>
                </Button>
            </ButtonGroup>
        </div>
    </Motion>
</template>

