<script setup lang="ts">
import { inject } from 'vue';
import Button from '../Button/Button.vue';

const closeStack = inject<() => void>('closeStack');
</script>

<template>
	<div data-ui-modal-close @click="closeStack">
		<slot>
			<Button variant="ghost" size="sm" icon="x" class="absolute top-3 right-2" />
		</slot>
	</div>
</template>
