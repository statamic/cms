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

const { clearInspector, dirty, fieldtypes, fieldView, inspect, inspecting, inspectorType, pages } = injectBuilderContext();

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

/* ROW DETECTION
=================================================== */
// Work out which fields land in the first and final visual rows so we can add helper classes (something CSS alone can't reliably detect). `field--first-row` and `field--last-row` is added.
const normalizedWidth = (field) => {
    const raw = Number(field?.config?.width ?? 100);

    if (!Number.isFinite(raw) || raw <= 0) {
        return 100;
    }

    return Math.min(raw, 100);
};

const fieldRows = computed(() => {
    const rows = [];
    let currentRow = [];
    let currentRowWidth = 0;

    props.section.fields.forEach((field, index) => {
        const width = normalizedWidth(field);

        if (currentRowWidth > 0 && currentRowWidth + width > 100) {
            rows.push(currentRow);
            currentRow = [];
            currentRowWidth = 0;
        }

        currentRow.push(index);
        currentRowWidth += width;
    });

    if (currentRow.length > 0) {
        rows.push(currentRow);
    }

    return rows;
});

const firstRowIndexes = computed(() => fieldRows.value.at(0) ?? []);
const lastRowIndexes = computed(() => fieldRows.value.at(-1) ?? []);

const isFieldInFirstRow = (index) => firstRowIndexes.value.includes(index);
const isFieldInLastRow = (index) => lastRowIndexes.value.includes(index);
/* END ROW DETECTION */

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

const confirmingDelete = ref(false);
const confirmDelete = () => confirmingDelete.value = true;
const deleteSection = () => emit('deleted', props.section._id);
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
                    @click.stop="editSection"
                />
                <Button
                    v-if="canDeleteSection"
                    class="[&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                    icon="trash"
                    size="sm"
                    variant="ghost"
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
                        <template v-for="(field, fieldIndex) in section.fields" :key="field._id">
                            <div
                                v-if="field.type === 'link_fields'"
                                :id="`fieldset-${field._id}`"
                                data-field-item
                                :data-editing-field="isInspectingLinkFields(field) ? '' : undefined"
                                :data-editing-item="isInspectingLinkFields(field) ? '' : undefined"
                                :class="[
                                    {
                                        'cursor-pointer': !isInspectingLinkFields(field),
                                        'field--first-row': isFieldInFirstRow(fieldIndex),
                                        'field--last-row': isFieldInLastRow(fieldIndex),
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
                                @remove="removeField(field)"
                            />

                            <RegularFormField
                                v-else
                                :field
                                :fieldtypes
                                :is-first-row="isFieldInFirstRow(fieldIndex)"
                                :is-last-row="isFieldInLastRow(fieldIndex)"
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

        <ConfirmationModal
            v-model:open="confirmingDelete"
            :title="__('Delete Section')"
            :body-text="__('Are you sure you want to delete this section? All fields in this section will also be deleted.')"
            :button-text="__('Delete')"
            danger
            @confirm="deleteSection"
        />
    </Panel>
</template>

<style>
[data-drop-indicator]:has(~ [data-empty-section]) {
    margin-bottom: 1rem;
}
</style>
