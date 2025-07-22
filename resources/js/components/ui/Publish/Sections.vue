<script setup>
import { injectTabContext } from './TabProvider.vue';
import { Panel, PanelHeader, Heading, Subheading, Card } from '@statamic/ui';
import FieldsProvider from './FieldsProvider.vue';
import Fields from './Fields.vue';
import ShowField from '@statamic/components/field-conditions/ShowField.js';
import { injectContainerContext } from './Container.vue';
import markdown from '@statamic/util/markdown.js';
import { computed } from 'vue';
import { Primitive } from 'reka-ui';

const { blueprint, container, visibleValues, extraValues, asConfig, hiddenFields, revealerFields, setHiddenField } = injectContainerContext();
const tab = injectTabContext();
const sections = tab.sections;
const visibleSections = computed(() => {
    return sections.filter((section) => {
        return section.fields.some((field) => {
            return new ShowField(
                visibleValues.value,
                extraValues.value,
                visibleValues.value,
                hiddenFields.value,
                revealerFields.value,
                setHiddenField
            ).showField(field, field.handle);
        });
    });
});

function renderInstructions(instructions) {
    return instructions ? markdown(__(instructions), { openLinksInNewTabs: true }) : '';
}
</script>

<template>
    <div>
        <Panel
            v-for="(section, i) in visibleSections"
            :key="i"
            :class="asConfig ? 'mb-12' : 'mb-6'"
        >
            <PanelHeader v-if="section.display">
                <Heading :text="__(section.display)" />
                <Subheading v-if="section.instructions" :text="renderInstructions(section.instructions)" />
            </PanelHeader>
            <Primitive :as="asConfig ? 'div' : Card">
                <FieldsProvider :fields="section.fields">
                    <slot :section="section">
                        <Fields />
                    </slot>
                </FieldsProvider>
            </Primitive>
        </Panel>
    </div>
</template>
