<script setup>
import { inject } from 'vue';
import Button from '../Button/Button.vue';

const closeModal = inject('closeModal');
</script>

<template>
    <div data-ui-modal-close @click="closeModal">
        <slot>
	        <Button variant="ghost" size="sm" icon="x" class="absolute top-3 right-2" />
        </slot>
    </div>
</template>
