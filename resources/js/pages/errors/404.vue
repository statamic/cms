<script setup>
import { Card, Heading, Description } from '@ui';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center">
        <Card class="text-center flex flex-col items-center space-y-2">
            <Heading size="2xl" :text="__('404')" />
            <Description :text="__('The page you are looking for could not be found.')" />
        </Card>
    </div>
</template>
