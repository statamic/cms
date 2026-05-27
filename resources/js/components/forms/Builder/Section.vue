<script setup lang="ts">
import { Button, Card, Heading, Panel, PanelHeader, PublishContainer } from '@ui';
import { computed } from 'vue';
import { FieldView, injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import ImportField from './ImportField.vue';
import RegularFormField from './RegularFormField.vue';
import { __, uniqid } from '@/bootstrap/globals';

const emit = defineEmits<{
    (e: 'deleted', value: null): void;
}>();

const props = defineProps<{
    section: object,
    canDeleteSection: boolean,
}>();

const { clearInspector, dirty, fieldtypes, fieldView, inspect, inspecting, inspectorType, pages } = injectBuilderContext();

const isOnlySection = computed(() => pages.value.flatMap((page) => page.sections).length === 1);

const inspectLinkFields = (field: any) => inspect(InspectorType.LinkFields, field);
const isInspectingLinkFields = (field) => inspectorType.value === InspectorType.LinkFields && inspecting.value?._id === field._id;

const addLinkFieldsPlaceholder = () => {
    const placeholder = { _id: uniqid(), type: 'link_fields' };
    props.section.fields.push(placeholder);
    inspectLinkFields(placeholder);
    dirty();
};

const toggleCollapsed = () => props.section.collapsed = !props.section.collapsed;

const inspectField = (field: any) => inspect(InspectorType.Field, field);

const blueprint = computed(() => ({
    tabs: [{
        handle: 'main',
        sections: [{
            fields: props.section.fields
                .filter((field) => field.preview?.config)
                .map((field) => field.preview.config),
        }],
    }],
}));

const updateFieldWidth = (field, width) => {
    field.config.width = width;

    if (field.type === 'reference' && !field.config_overrides.includes('width')) {
        field.config_overrides.push('width');
    }

    dirty();
};

const duplicateField = (field) => {
    const { section } = props;
    const index = section.fields.indexOf(field);
    const handle = uniqid();

    const newField = {
        ...field,
        _id: `${section._id}-${section.fields.length}`,
        handle,
        config: { ...field.config, display: `${field.config.display} (${__('Duplicate')})` },
        preview: {
            config: { ...field.preview.config, handle },
            value: field.preview.value,
            meta: field.preview.meta,
        },
    };

    section.fields.splice(index + 1, 0, newField);

    dirty();
    inspectField(newField);

    setTimeout(() => document.getElementById('field_display')?.select(), 250);
};

const removeField = (field) => {
    dirty();
    clearInspector();
    props.section.fields.splice(props.section.fields.indexOf(field), 1);
};

const editSection = () => inspect(InspectorType.Section, props.section);
const deleteSection = () => emit('deleted', props.section._id);
</script>

<template>
    <Panel
        :id="`section-${section._id}`"
        class="mx-auto max-w-5xl"
        :class="{ 'pb-0': section.collapsed }"
        :data-panel-collapsed="section.collapsed ? 'true' : 'false'"
    >
        <PanelHeader class="flex items-center justify-between" @click="toggleCollapsed">
            <Heading class="cursor-pointer flex-1" :text="__(section.display)" />
            <div>
                <Button
                    class="[&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    icon="pencil-line"
                    size="sm"
                    variant="ghost"
                    @click.stop="editSection"
                />
                <Button
                    v-if="canDeleteSection"
                    class="[&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    icon="trash"
                    size="sm"
                    variant="ghost"
                    @click.stop="deleteSection"
                />
                <Button
                    class="[&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    :icon="section.collapsed ? 'expand' : 'collapse'"
                    size="sm"
                    variant="ghost"
                    :aria-label="__('Toggle section visibility')"
                    @click.stop="toggleCollapsed"
                />
            </div>
        </PanelHeader>

        <div
            class="publish-section-collapsible grid"
            :class="section.collapsed ? 'publish-section-collapsible--collapsed' : 'publish-section-collapsible--expanded'"
        >
            <div class="publish-section-collapsible__inner min-h-0">
            <Card class="section-drop-zone pb-[calc(var(--spacing)_*_5_-_2px)]!" :data-section-drop-zone="section._id">
                <div
                    v-if="section.fields.length === 0"
                    class="field-sort-container"
                    :data-sort-section="section._id"
                >
                    <div
                        data-empty-section
                        class="flex items-center justify-center rounded-lg border border-dashed border-zinc-300"
                        :class="{ 'h-[670px]': isOnlySection, 'h-[200px]': !isOnlySection }"
                    >
                        <div>
                            <span class="text-zinc-500 mr-2">{{ __('Drag fields here to build your form or') }}</span>
                            <Button size="xs" pill icon="link" :text="__('Link Existing')" @click="addLinkFieldsPlaceholder" />
                        </div>
                    </div>
                </div>

                <PublishContainer
                    v-else
                    :name="'form-builder-' + section._id"
                    :blueprint
                    :track-dirty-state="false"
                >
                    <div
                        class="field-sort-container field-grid"
                        :data-sort-section="section._id"
                        :data-fields-collapsed="fieldView === FieldView.Collapsed ? 'true' : null"
                    >
                        <template v-for="field in section.fields" :key="field._id">
                            <div
                                v-if="field.type === 'link_fields'"
                                :id="`fieldset-${field._id}`"
                                data-field-item
                                :data-editing-field="isInspectingLinkFields(field) ? '' : undefined"
                                :data-editing-item="isInspectingLinkFields(field) ? '' : undefined"
                                :class="[{ 'cursor-pointer': !isInspectingLinkFields(field) }]"
                                class="border border-dashed rounded-lg p-4 flex items-center justify-center"
                                @click.stop="isInspectingLinkFields(field) || inspectLinkFields(field)"
                            >
                                <span class="text-zinc-500 mr-2">{{ __('Select a field or fieldset to import') }}</span>
                            </div>

                            <ImportField
                                v-else-if="field.type === 'import'"
                                :field
                                @remove="removeField(field)"
                            />

                            <RegularFormField
                                v-else
                                :field
                                :fieldtypes
                                @duplicate="duplicateField(field)"
                                @width-changed="updateFieldWidth(field, $event)"
                                @remove="removeField(field)"
                            />
                        </template>
                    </div>
                </PublishContainer>

                <slot name="footer" />
            </Card>
            </div>
        </div>
    </Panel>
</template>

<style scoped>
[class*="publish-section-collapsible"] {
    --speed: 0ms;
    /* Only setting the animation speed when the panel is hovered prevents the animation triggering on page load. */
    [data-ui-panel]:hover & {
        --speed: 250ms;
    }
    --timing: ease;

    @media (prefers-reduced-motion: reduce) {
        --speed: 0ms;
    }
}

.publish-section-collapsible--expanded {
    /* We can animate collapse/expand using grid rows */
    animation: expand-rows var(--speed) var(--timing) forwards;
}

.publish-section-collapsible--collapsed {
    animation: collapse-rows var(--speed) var(--timing) forwards;
}

.publish-section-collapsible--expanded .publish-section-collapsible__inner {
    animation: calc(var(--speed) * 2) var(--timing) section-fade-in both;
    overflow: clip;
    overflow-clip-margin: 4px;
}

.publish-section-collapsible--collapsed .publish-section-collapsible__inner {
    animation:
        clip-overflow 0ms var(--speed) forwards,
        make-invisible 0ms var(--speed) forwards;
    overflow: clip;
}

@keyframes section-fade-in { from { opacity: 0%; } to { opacity: 100%; } }
@keyframes make-invisible { from { visibility: visible; } to { visibility: hidden; } }
@keyframes collapse-rows  { from { grid-template-rows: 1fr; } to { grid-template-rows: 0fr; } }
@keyframes expand-rows    { from { grid-template-rows: 0fr; } to { grid-template-rows: 1fr; } }
@keyframes clip-overflow  { to { overflow: clip; } }
</style>

<style>
[data-drop-indicator]:has(~ [data-empty-section]) {
    margin-bottom: 1rem;
}
</style>
