<script setup>
import { ref } from 'vue';
// import { Button, Modal, Badge } from '@statamic/ui';
import { Button } from '@statamic/ui';
// import Section from './Section.vue';

const modalOpen = ref(false);
</script>

<template>
    <div id="app" class="min-h-screen bg-gray-50 p-8">
        <h1 class="text-2xl font-bold mb-6">Statamic UI Test</h1>

<!--        <div class="space-y-6">-->
<!--            <Section title="Badges">-->
<!--                <Badge text="Im a badge" color="green" />-->
<!--            </Section>-->
<!--            <Section title="Buttons">-->
<!--                <Button variant="primary" @click="modalOpen = true">Open Modal</Button>-->
<!--            </Section>-->
<!--        </div>-->

<!--        <Modal title="Test Modal" v-model:open="modalOpen">-->
<!--            <p class="text-gray-700">This is a test modal using Statamic UI components.</p>-->
<!--        </Modal>-->
    </div>
</template>
