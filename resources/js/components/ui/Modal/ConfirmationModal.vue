<script setup>
import { computed, onBeforeUnmount, onMounted, ref, useSlots } from 'vue';
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
});

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

const shouldCloseOnSubmit = computed(() => {
    // If the busy prop is provided, we will assume they will handle the open state externally.
    return props.busy === undefined;
});
</script>

<template>
    <Modal
        ref="modal"
        :title="__(title)"
        :open="open"
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
                    :text="__(buttonText)"
                    @click="submit"
                />
            </div>
        </template>
    </Modal>
</template>
