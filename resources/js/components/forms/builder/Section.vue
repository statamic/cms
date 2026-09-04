<script setup lang="ts">
import { Button, Card, ConfirmationModal, Heading, Panel, PanelHeader, PublishContainer } from '@ui';
import { computed, ref } from 'vue';
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

const { clearInspector, dirty, ensureUniqueDisplay, fieldtypes, fieldView, inspect, inspecting, inspectorType, pages } = injectBuilderContext();

const isOnlySection = computed(() => pages.value.flatMap((page) => page.sections).length === 1);

const editSection = () => inspect(InspectorType.Section, props.section);
const isInspecting = computed(() => inspectorType.value === InspectorType.Section && inspecting.value?._id === props.section._id);

const inspectLinkFields = (field: any) => inspect(InspectorType.LinkFields, field);
const isInspectingLinkFields = (field) => inspectorType.value === InspectorType.LinkFields && inspecting.value?._id === field._id;

const addLinkFieldsPlaceholder = () => {
    const placeholder = { _id: uniqid(), type: 'link_fields' };
    props.section.fields.push(placeholder);
    inspectLinkFields(placeholder);
    dirty();
};

const toggleCollapsed = () => {
    props.section.collapsibleInteracted = true;
    props.section.collapsed = !props.section.collapsed;
};

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

const containerMeta = computed(() => {
    const meta = {};

    props.section.fields.forEach((field) => {
        if (field.preview?.meta) meta[field.preview.config.handle] = field.preview.meta;
    });

    return meta;
});

const containerValues = computed(() => {
    const values = {};

    props.section.fields.forEach((field) => {
        if (field.preview?.value) values[field.preview.config.handle] = field.preview.value;
    });

    return values;
});

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
    const _id = uniqid();

    const newField = {
        ...field,
        _id,
        handle: null,
        isNew: true,
        config: { ...field.config, display: ensureUniqueDisplay(`${field.config.display} (${__('Duplicate')})`) },
        preview: {
            config: { ...field.preview.config, handle: _id },
            value: field.preview.value,
            meta: field.preview.meta,
        },
    };

    section.fields.splice(index + 1, 0, newField);

    dirty();
    inspectField(newField);

    setTimeout(() => document.getElementById('field_display')?.select(), 250);
};

const fieldPendingDeletion = ref(null);
const confirmingFieldDeletion = ref(false);

const confirmDeleteField = (field) => {
    fieldPendingDeletion.value = field;
    confirmingFieldDeletion.value = true;
};

const deleteField = () => {
    dirty();
    clearInspector();
    props.section.fields.splice(props.section.fields.indexOf(fieldPendingDeletion.value), 1);
    fieldPendingDeletion.value = null;
    confirmingFieldDeletion.value = false;
}

const confirmingDelete = ref(false);
const confirmDelete = () => confirmingDelete.value = true;
const deleteSection = () => emit('deleted', props.section._id);

const getFieldWidth = (field): number => {
    const w = Number(field?.config?.width);
    return (w > 0 && w <= 100) ? w : 100;
};

const rowBoundaries = computed(() => {
    const fields = props.section.fields;
    let width = 0;
    let firstRowEnd = fields.length - 1;
    let lastRowStart = 0;
    let wrapped = false;

    for (let i = 0; i < fields.length; i++) {
        const fieldWidth = getFieldWidth(fields[i]);
        if (width > 0 && width + fieldWidth > 100) {
            if (!wrapped) firstRowEnd = i - 1;
            lastRowStart = i;
            width = 0;
            wrapped = true;
        }
        width += fieldWidth;
    }

    return { firstRowEnd, lastRowStart };
});

const isFieldInFirstRow = (index): boolean => index <= rowBoundaries.value.firstRowEnd;
const isFieldInLastRow = (index): boolean => index >= rowBoundaries.value.lastRowStart;
</script>

<template>
    <Panel
        :id="`section-${section._id}`"
        class="mx-auto max-w-5xl"
        :class="{ 'pb-0': section.collapsed }"
        :data-panel-collapsed="section.collapsed ? 'true' : 'false'"
        :data-editing-item="isInspecting ? '' : undefined"
    >
        <PanelHeader class="flex items-center justify-between" @click="toggleCollapsed">
            <Heading class="cursor-pointer flex-1" :text="__(section.display)" />
            <div>
                <Button
                    class="[&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    icon="pencil-line"
                    size="sm"
                    variant="ghost"
                    :aria-label="__('Edit section')"
                    @click.stop="editSection"
                />
                <Button
                    v-if="canDeleteSection"
                    class="[&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    icon="trash"
                    size="sm"
                    variant="ghost"
                    :aria-label="__('Delete section')"
                    @click.stop="confirmDelete"
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
            :class="[
                section.collapsed ? 'publish-section-collapsible--collapsed' : 'publish-section-collapsible--expanded',
                { 'publish-section-collapsible--interacted': section.collapsibleInteracted },
            ]"
        >
            <div class="publish-section-collapsible__inner min-h-0">
                <Card class="section-drop-zone" :data-section-drop-zone="section._id">
                    <div
                        v-if="section.fields.length === 0"
                        class="field-sort-container"
                        :data-sort-section="section._id"
                    >
                        <div
                            data-empty-section
                            class="flex items-center justify-center rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700"
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
                        :meta="containerMeta"
                        :model-value="containerValues"
                        :track-dirty-state="false"
                    >
                        <div
                            class="field-sort-container field-grid"
                            :data-sort-section="section._id"
                            :data-fields-collapsed="fieldView === FieldView.Collapsed ? 'true' : null"
                        >
                            <template v-for="(field, fieldIndex) in section.fields" :key="field._id">
                                <div
                                    v-if="field.type === 'link_fields'"
                                    :id="`fieldset-${field._id}`"
                                    data-field-item
                                    :data-editing-field="isInspectingLinkFields(field) ? '' : undefined"
                                    :data-editing-item="isInspectingLinkFields(field) ? '' : undefined"
                                    :data-first-row="isFieldInFirstRow(fieldIndex) ? '' : undefined"
                                    :data-last-row="isFieldInLastRow(fieldIndex) ? '' : undefined"
                                    :class="[
                                        {
                                            'cursor-pointer': !isInspectingLinkFields(field),
                                        },
                                    ]"
                                    class="border border-dashed rounded-lg p-4 flex items-center justify-center"
                                    @click.stop="isInspectingLinkFields(field) || inspectLinkFields(field)"
                                >
                                    <span class="text-zinc-500 mr-2">{{ __('Select a field or fieldset to import') }}</span>
                                </div>

                                <ImportField
                                    v-else-if="field.type === 'import'"
                                    :field
                                    :is-first-row="isFieldInFirstRow(fieldIndex)"
                                    :is-last-row="isFieldInLastRow(fieldIndex)"
                                    @remove="confirmRemoveField(field)"
                                />

                                <RegularFormField
                                    v-else
                                    :field
                                    :fieldtypes
                                    :is-first-row="isFieldInFirstRow(fieldIndex)"
                                    :is-last-row="isFieldInLastRow(fieldIndex)"
                                    @duplicate="duplicateField(field)"
                                    @width-changed="updateFieldWidth(field, $event)"
                                    @remove="confirmDeleteField(field)"
                                />
                            </template>
                        </div>
                    </PublishContainer>

                    <slot name="footer" />
                </Card>
            </div>
        </div>

        <ConfirmationModal
            v-model:open="confirmingDelete"
            :title="__('Delete Section')"
            :body-text="__('Are you sure you want to delete this section? All fields in this section will also be deleted.')"
            :button-text="__('Delete')"
            danger
            @confirm="deleteSection"
        />

        <ConfirmationModal
            v-model:open="confirmingFieldDeletion"
            :title="__('Delete Field')"
            :body-text="__('Are you sure you want to delete this field? This action cannot be undone.')"
            :button-text="__('Delete')"
            danger
            @confirm="deleteField"
        />
    </Panel>
</template>

<style>
[data-drop-indicator]:has(~ [data-empty-section]) {
    margin-bottom: 1rem;
}
</style>
