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
                    class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    :icon="section.collapsed ? 'expand' : 'collapse'"
                    size="sm"
                    variant="ghost"
                    :aria-label="__('Toggle section visibility')"
                />
            </PanelHeader>
            <div
                class="publish-section-collapsible grid"
                :class="section.collapsed ? 'publish-section-collapsible--collapsed' : 'publish-section-collapsible--expanded'"
            >
                <div class="publish-section-collapsible__inner min-h-0">
                    <Card :class="{ 'p-0!': asConfig }">
                        <FieldsProvider :fields="section.fields">
                            <slot :section="section">
                                <Fields />
                            </slot>
                        </FieldsProvider>
                    </Card>
                </div>
            </div>
        </Panel>
    </div>
</template>

<style scoped>
.publish-section-collapsible--expanded {
    animation: publish-section-expand 250ms ease forwards;
    grid-template-rows: 1fr;
}

.publish-section-collapsible--collapsed {
    animation: publish-section-collapse 250ms ease forwards;
    grid-template-rows: 0fr;
}

.publish-section-collapsible--expanded .publish-section-collapsible__inner {
    animation:
        publish-section-expand-visibility 0ms 0ms forwards,
        publish-section-expand-overflow 0ms 250ms forwards;
    overflow: clip;
}

.publish-section-collapsible--collapsed .publish-section-collapsible__inner {
    /* border: 3px solid blue!important; */
    animation:
        publish-section-collapse-overflow 0ms 250ms forwards,
        publish-section-collapse-visibility 0ms 250ms forwards;
    overflow: clip;
}

@keyframes publish-section-expand {
    from {
        grid-template-rows: 0fr;
    }

    to {
        grid-template-rows: 1fr;
    }
}

@keyframes publish-section-expand-visibility {
    to {
        visibility: visible;
    }
}

@keyframes publish-section-expand-overflow {
    to {
        overflow: visible;
    }
}

@keyframes publish-section-collapse {
    from {
        grid-template-rows: 1fr;
    }

    to {
        grid-template-rows: 0fr;
    }
}

@keyframes publish-section-collapse-overflow {
    to {
        overflow: clip;
    }
}

@keyframes publish-section-collapse-visibility {
    to {
        visibility: hidden;
    }
}

@media (prefers-reduced-motion: reduce) {
    .publish-section-collapsible--expanded,
    .publish-section-collapsible--collapsed {
        animation: none;
    }

    .publish-section-collapsible--expanded {
        grid-template-rows: 1fr;
    }

    .publish-section-collapsible--collapsed {
        grid-template-rows: 0fr;
    }

    .publish-section-collapsible--expanded .publish-section-collapsible__inner {
        visibility: visible;
        overflow: visible;
    }

    .publish-section-collapsible--collapsed .publish-section-collapsible__inner {
        animation: none;
        visibility: hidden;
        overflow: clip;
    }
}
</style>
