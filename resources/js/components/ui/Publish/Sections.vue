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
    const showField = new ShowField(
        visibleValues.value,
        extraValues.value,
        visibleValues.value,
        revealerValues.value,
        hiddenFields.value,
        setHiddenField,
        { container },
    );

    return sections.filter((section) => {
        return section.fields.some((field) => showField.showField(field, field.handle));
    });
});

function renderInstructions(instructions) {
    return instructions ? markdown(__(instructions), { openLinksInNewTabs: true }) : '';
}

function toggleSection(section) {
    if (section.collapsible) {
        section.collapsibleInteracted = true;
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
                :class="[
                    section.collapsed ? 'publish-section-collapsible--collapsed' : 'publish-section-collapsible--expanded',
                    { 'publish-section-collapsible--interacted': section.collapsibleInteracted },
                ]"
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
.publish-section-collapsible {
    --timing: ease;
    /* No animation on load; enable once the user has toggled this section. */
    --speed: 0ms;

    /* Only setting the animation speed when the section is interacted with. Prevents the animation triggering on page load. */
    &.publish-section-collapsible--interacted {
        --speed: 250ms;
    }

    @media (prefers-reduced-motion: reduce) {
        --speed: 0ms;
    }
}

.publish-section-collapsible--expanded {
    /* We can animate collapse/expand using grid rows */
    animation: expand-rows var(--speed) var(--timing) forwards;

    .publish-section-collapsible__inner {
        animation: calc(var(--speed) * 2) var(--timing) section-fade-in both;
        overflow: clip;
        /* We need to increase the clip margin here vs regular collapsible sections because we have things appearing outside the section such as the logic indicator icon. */
        overflow-clip-margin: 2.5rem;
    }
}

.publish-section-collapsible--collapsed {
    animation: collapse-rows var(--speed) var(--timing) forwards;

    .publish-section-collapsible__inner {
        animation:
            clip-overflow 0ms var(--speed) forwards,
            make-invisible 0ms var(--speed) forwards;
        overflow: clip;
    }
}

@keyframes section-fade-in { from { opacity: 0%; } to { opacity: 100%; } }
@keyframes make-invisible { from { visibility: visible; } to { visibility: hidden; } }
@keyframes collapse-rows  { from { grid-template-rows: 1fr; } to { grid-template-rows: 0fr; } }
@keyframes expand-rows    { from { grid-template-rows: 0fr; } to { grid-template-rows: 1fr; } }
@keyframes clip-overflow  { to { overflow: clip; } }
</style>
