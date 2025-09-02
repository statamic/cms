<script setup>
import { injectTabContext } from './TabProvider.vue';
import { Panel, PanelHeader, Heading, Subheading, Card, Icon } from '@/components/ui';
import FieldsProvider from './FieldsProvider.vue';
import Fields from './Fields.vue';
import ShowField from '@/components/field-conditions/ShowField.js';
import { injectContainerContext } from './Container.vue';
import markdown from '@/util/markdown.js';
import { computed } from 'vue';
import { Primitive } from 'reka-ui';
import { Motion } from 'motion-v';

const { blueprint, container, visibleValues, extraValues, revealerValues, asConfig, hiddenFields, setHiddenField } = injectContainerContext();
const tab = injectTabContext();
const sections = tab.sections;
const visibleSections = computed(() => {
    return sections.filter((section) => {
        return section.fields.some((field) => {
            return new ShowField(
                visibleValues.value,
                extraValues.value,
                visibleValues.value,
                revealerValues.value,
                hiddenFields.value,
                setHiddenField
            ).showField(field, field.handle);
        });
    });
});

function renderInstructions(instructions) {
    return instructions ? markdown(__(instructions), { openLinksInNewTabs: true }) : '';
}

function toggleSection(id) {
    if (sections[id].collapsible) {
        sections[id].collapsed = !sections[id].collapsed;
    }
}
</script>

<template>
    <div>
        <Panel
            v-for="(section, i) in visibleSections"
            :key="i"
            :class="[
                asConfig ? 'mb-12' : 'mb-6',
                { 'pb-0': section.collapsed }
            ]"
        >
            <PanelHeader @click="toggleSection(i)" class="flex justify-between">
                <div>
                    <Heading :text="__(section.display)" />
                    <Subheading v-if="section.instructions" :text="renderInstructions(section.instructions)" />
                </div>
                <Icon
                    v-if="section.collapsible"
                    name="ui/chevron-down"
                    class="size-5 text-gray-400"
                    :class="section.collapsed ? 'rotate-180' : 'rotate-0'"
                />
            </PanelHeader>
            <Motion
                :class="{ 'overflow-hidden': section.collapsed }"
                :initial="{ height: section.collapsed ? '0px' : 'auto' }"
                :animate="{ height: section.collapsed ? '0px' : 'auto' }"
                :transition="{ duration: 0.25, type: 'tween' }"
            >
                <div class="p-px">
                    <Primitive :as="asConfig ? 'div' : Card">
                        <FieldsProvider :fields="section.fields">
                            <slot :section="section">
                                <Fields />
                            </slot>
                        </FieldsProvider>
                    </Primitive>
                </div>
            </Motion>
        </Panel>
    </div>
</template>
