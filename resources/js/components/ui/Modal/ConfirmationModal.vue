<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Modal, ModalClose, Button, Icon } from '@/components/ui';

const emit = defineEmits([
    'update:open',
    'opened',
    'confirm',
    'cancel'
]);

const props = defineProps({
    /** The controlled open state of the modal. */
    open: { type: Boolean, default: false },
    /** Enables Cmd/Ctrl+Enter submit shortcut and keycap badge on submit button. */
    submitShortcut: { type: Boolean, default: false },
    title: {
        type: String,
    },
    bodyText: {
        type: String,
    },
    buttonText: {
        type: String,
        default: 'Confirm',
    },
    cancellable: {
        type: Boolean,
        default: true,
    },
    submittable: {
        type: Boolean,
        default: true,
    },
    cancelText: {
        type: String,
        default: () => __('Cancel'),
    },
    danger: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    busy: {
        type: [Boolean, undefined],
        default: undefined,
    },
    /** When `true`, the modal's backdrop will be blurred */
    blur: { type: Boolean, default: false },
});

const isSubmitModifierPressed = ref(false);

function updateModalOpen(open) {
    if (! open && props.busy) {
        return;
    }

    emit('update:open', open);

    if (! open) emit('cancel');
}

function submit() {
    if (props.busy) return;

    emit('confirm');

    if (shouldCloseOnSubmit.value) {
        updateModalOpen(false);
    }
}

function onKeydown(event) {
    isSubmitModifierPressed.value = event.metaKey || event.ctrlKey;

    if (!props.open || !props.submittable || !props.submitShortcut) return;
    if (props.disabled || props.busy) return;
    if (event.isComposing) return;

    const isSubmitShortcut = event.key === 'Enter' && (event.metaKey || event.ctrlKey);
    if (!isSubmitShortcut) return;

    submit();
    event.preventDefault();
    event.stopPropagation();
}

function onKeyup(event) {
    isSubmitModifierPressed.value = event.metaKey || event.ctrlKey;
}

function onWindowBlur() {
    isSubmitModifierPressed.value = false;
}

const submitShortcutLabel = computed(() => {
    if (typeof navigator === 'undefined') return '⌘↵';

    return /(Mac|iPhone|iPad|iPod)/i.test(navigator.platform) ? '⌘↵' : 'Ctrl↵';
});

const shouldCloseOnSubmit = computed(() => {
    // If the busy prop is provided, we will assume they will handle the open state externally.
    return props.busy === undefined;
});

onMounted(() => {
    document.addEventListener('keydown', onKeydown, true);
    document.addEventListener('keyup', onKeyup, true);
    window.addEventListener('blur', onWindowBlur);
});

onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown, true);
    document.removeEventListener('keyup', onKeyup, true);
    window.removeEventListener('blur', onWindowBlur);
});
</script>

<template>
    <Modal
        ref="modal"
        :title="__(title)"
        :open="open"
        :blur="blur"
        :dismissible="cancellable"
        @update:open="updateModalOpen"
        @opened="emit('opened')"
    >
        <div
            v-if="busy"
            class="pointer-events-none absolute inset-0 flex select-none items-center justify-center bg-white bg-opacity-75 dark:bg-gray-850"
        >
            <Icon name="loading" />
        </div>

        <p v-if="bodyText" v-text="bodyText" />
        <slot v-else>
            <p>{{ __('Are you sure?') }}</p>
        </slot>

        <template v-if="cancellable || submittable" #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <ModalClose asChild v-if="cancellable">
                    <Button
                        variant="ghost"
                        :disabled="busy"
                        :text="__(cancelText)"
                    />
                </ModalClose>
                <Button
                    v-if="submittable"
                    type="submit"
                    :variant="danger ? 'danger' : 'primary'"
                    :disabled="disabled || busy"
                    @click="submit"
                >
                    <span class="inline-flex items-center">
                        {{ __(buttonText) }}
                        <span
                            v-if="submitShortcut"
                            :class="[
                                'ms-2 inline-flex h-4 min-w-4 items-center justify-center rounded bg-white/25 px-1 font-semibold text-[0.625rem] text-white/90 ring-1 ring-white/20 transition-opacity opacity-60',
                                isSubmitModifierPressed && 'opacity-100',
                            ]"
                        >
                            {{ submitShortcutLabel }}
                        </span>
                    </span>
                </Button>
            </div>
        </template>
    </Modal>
</template>
