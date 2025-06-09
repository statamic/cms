<script setup>
import { injectTabContext } from './TabProvider.vue';
import { CardPanel } from '@statamic/ui';
import FieldsProvider from './FieldsProvider.vue';
import Fields from './Fields.vue';
import ShowField from '@statamic/components/field-conditions/ShowField.js';
import { injectContainerContext } from './Container.vue';
import markdown from '@statamic/util/markdown.js';

const { blueprint, store } = injectContainerContext();
const tab = injectTabContext();
const sections = tab.sections;
const visibleSections = sections.filter(section => {
    return section.fields.some((field) => {
        return new ShowField(store, store.values, store.extraValues).showField(field, field.handle);
    });
});

function renderInstructions(instructions) {
    return instructions ? markdown(instructions, { openLinksInNewTabs: true }) : '';
}
</script>

<template>
    <div>
        <CardPanel
            v-for="(section, i) in visibleSections"
            :key="i"
            :heading="section.display"
            :subheading="renderInstructions(section.instructions)"
            class="mb-6"
        >
            <FieldsProvider :fields="section.fields">
                <slot :section="section">
                    <Fields />
                </slot>
            </FieldsProvider>
        </CardPanel>
    </div>
</template>
