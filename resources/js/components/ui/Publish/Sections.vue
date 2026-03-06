<script setup>
import { injectTabContext } from './TabProvider.vue';
import {
    Button,
    Panel,
    PanelHeader,
    Heading,
    Subheading,
    Card,
    Icon,
} from '@ui';
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
                setHiddenField,
                { container }
            ).showField(field, field.handle);
        });
    });
});

function renderInstructions(instructions) {
    return instructions ? markdown(__(instructions), { openLinksInNewTabs: true }) : '';
}

function toggleSection(section) {
    if (section.collapsible) {
        section.collapsed = !section.collapsed;
    }
}
</script>

<template>
    <div>
        <Panel
            v-for="(section, i) in visibleSections"
            :key="i"
            :class="[
                'mb-6',
                { 'pb-0': section.collapsed }
            ]"
        >
            <PanelHeader v-if="section.display || section.collapsible" class="relative flex items-center justify-between">
                <div class="[&_a]:relative [&_a]:z-(--z-index-above)">
                    <Heading :text="__(section.display)" />
                    <Subheading v-if="section.instructions" :text="renderInstructions(section.instructions)" />
                </div>
                <Button
                    @click="toggleSection(section)"
                    v-if="section.collapsible"
                    class="static! [&_svg]:size-4.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    :icon="section.collapsed ? 'expand' : 'collapse'"
                    size="sm"
                    variant="ghost"
                    :aria-label="__('Toggle section visibility')"
                />
            </PanelHeader>
            <div
                style="--tw-ease: ease;"
                class="h-auto visible transition-[height,visibility] duration-[250ms,2s]"
                :class="{ 'h-0! invisible! overflow-clip': section.collapsed }"
            >
                <Card :class="{ 'p-0!': asConfig }">
                    <FieldsProvider :fields="section.fields">
                        <slot :section="section">
                            <Fields />
                        </slot>
                    </FieldsProvider>
                </Card>
            </div>
        </Panel>
    </div>
</template>
