<script setup>
import { Button } from '@statamic/cms/ui';
function clicked() {
    alert('Button clicked!');
}
</script>
<template>
    this is the custom component.
    <Button text="click me" @click="clicked"/>
</template>
