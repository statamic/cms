<script setup>
import { injectContainerContext } from './containerContext.js';

const { components } = injectContainerContext();
</script>

<template>
    <component v-for="component in components" :key="component.name" :is="component.name" v-bind="component.props" />
</template>
