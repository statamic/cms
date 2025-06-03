<script setup>
import { DialogTitle } from 'reka-ui';

defineOptions({
    name: 'ModalTitle',
});
</script>

<template>
    <DialogTitle data-ui-modal-title>
        <slot />
    </DialogTitle>
</template>
