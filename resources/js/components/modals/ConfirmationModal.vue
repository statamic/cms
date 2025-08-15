<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Modal, ModalClose, Button, Icon } from '@statamic/ui';

const emit = defineEmits(['opened', 'confirm', 'cancel']);

const props = defineProps({
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
    cancelText: {
        type: String,
        default: 'Cancel',
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
        type: Boolean,
        default: false,
    },
});

onMounted(() => emit('opened'));

const modalOpen = ref(true);

function updateModalOpen(open) {
    if (! open && props.busy) {
        return;
    }

    modalOpen.value = open;

    if (! open) emit('cancel');
}

function submit() {
    if (props.busy) return;

    emit('confirm');
}
</script>

<template>
    <Modal ref="modal" :title="__(title)" :open="modalOpen" @update:open="updateModalOpen">
        <div
            v-if="busy"
            class="pointer-events-none absolute inset-0 flex select-none items-center justify-center bg-white bg-opacity-75 dark:bg-dark-700"
        >
            <Icon name="loading" />
        </div>

        <p v-if="bodyText" v-text="bodyText" />
        <slot v-else>
            <p>{{ __('Are you sure?') }}</p>
        </slot>

        <template #footer>
            <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                <ModalClose asChild>
                    <Button
                        v-if="cancellable"
                        variant="ghost"
                        :disabled="busy"
                        :text="__(cancelText)"
                    />
                </ModalClose>
                <Button
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
