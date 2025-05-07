<script setup>
import { injectTabContext } from './TabProvider.vue';
import { CardPanel } from '@statamic/ui';
import SectionProvider from './SectionProvider.vue';
import Fields from './Fields.vue';

const tab = injectTabContext();
const sections = tab.sections;
</script>

<template>
    <div>
        <CardPanel
            v-for="(section, i) in sections"
            :key="i"
            :heading="section.display"
            :subheading="section.instructions"
            class="mb-6"
        >
            <SectionProvider :section="section">
                <slot :section="section">
                    <Fields />
                </slot>
            </SectionProvider>
        </CardPanel>
    </div>
</template>
