<script setup lang="ts">
import { Button, Card, Heading, Icon, Panel, PanelHeader, PublishContainer } from '@ui';
import { computed } from 'vue';
import { uniqid, __ } from '@/bootstrap/globals.js';
import { FieldView, injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import RegularFormField from './RegularFormField.vue';
import ImportField from './ImportField.vue';

const emit = defineEmits<{
    (e: 'deleted', value: null): void;
}>();

const props = defineProps<{
    section: object,
    canDeleteSection: boolean,
}>();

const { fieldtypes, fieldView, inspecting, inspectorType, inspect, clearInspector, dirty } = injectBuilderContext();

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
};

const removeField = (field) => {
    props.section.fields.splice(props.section.fields.indexOf(field), 1);

    dirty();
    clearInspector();
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
            style="--tw-ease: ease;"
            class="h-auto visible transition-[height,visibility] duration-[250ms,2s]"
            :class="{ 'h-0! invisible! overflow-clip': section.collapsed }"
        >
            <Card class="section-drop-zone" :data-section-drop-zone="section._id">
                <div
                    v-if="section.fields.length === 0"
                    class="field-sort-container"
                    :data-sort-section="section._id"
                >
                    <div data-empty-section class="h-[670px] flex items-center justify-center rounded-lg border border-dashed border-zinc-300">
                        <div>
                            <span class="text-zinc-500 mr-2">{{ __('Drag fields here to build your form or') }}</span>
                            <Button size="xs" pill icon="link" :text="__('Link Existing')" @click="addLinkFieldsPlaceholder" />
                        </div>
                    </div>
                </div>

                <PublishContainer
                    v-else
                    :name="'form-builder-' + section._id"
                    :blueprint="blueprint"
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
    </Panel>

<!--    <Panel-->
<!--        class="mx-auto max-w-5xl"-->
<!--        :class="{ 'pb-0': panelCollapsed }"-->
<!--        :data-panel-collapsed="panelCollapsed ? 'true' : 'false'"-->
<!--    >-->
<!--        <PanelHeader class="relative flex items-center justify-between">-->
<!--            <Heading :text="__('Main Section')" />-->
<!--            <Button-->
<!--                @click="panelCollapsed = !panelCollapsed"-->
<!--                class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"-->
<!--                :icon="panelCollapsed ? 'expand' : 'collapse'"-->
<!--                size="sm"-->
<!--                variant="ghost"-->
<!--                :aria-label="__('Toggle section visibility')"-->
<!--            />-->
<!--        </PanelHeader>-->

<!--        <div-->
<!--            style="&#45;&#45;tw-ease: ease;"-->
<!--            class="h-auto visible transition-[height,visibility] duration-[250ms,2s]"-->
<!--            :class="{ 'h-0! invisible! overflow-clip': panelCollapsed }"-->
<!--        >-->
<!--            <Card>-->
<!--                <div class="space-y-7" :data-fields-collapsed="fieldView === FieldView.Collapsed ? 'true' : null">-->
<!--                    <div data-fieldset-group class="space-y-7">-->
<!--                        <div id="fieldset-start">-->
<!--                                <span data-fieldset-label class="inline-flex gap-1.75 rounded-md font-mono text-2xs text-indigo-800">-->
<!--                                    <span class="inline-flex" v-tooltip="__('Fieldset')">-->
<!--                                        <Icon name="link" class="size-3.5" aria-hidden="true" />-->
<!--                                    </span>-->
<!--                                    <span class="sr-only">{{ __('Fieldset') }}</span>-->
<!--                                </span>-->
<!--                            <Field :label="__('What do you like most about our band?')">-->
<!--                                <template #label>-->
<!--                                    <Label for="favorite-thing-field">-->
<!--                                            <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">-->
<!--                                                <Icon name="text-long" data-collapsed-field-icon class="size-3.5 me-1 text-purple-500 dark:text-purple-400" aria-hidden="true" />-->
<!--                                                {{ __('What do you like most about our band?') }}-->
<!--                                                <span class="relative -top-px -ms-0.5 text-red-600" :aria-label="__('Required')">*</span>-->
<!--                                            </span>-->
<!--                                    </Label>-->
<!--                                </template>-->
<!--                                <span class="absolute z-(&#45;&#45;z-index-above) top-1 max-sm:-right-2 sm:-left-14" v-tooltip="__('Logic attached')">-->
<!--                                        <Icon data-logic-attached name="logic-tree" class="size-3.5! text-gray-400 dark:text-gray-600" aria-hidden="true" />-->
<!--                                    </span>-->
<!--                                <Textarea id="favorite-thing-field" v-model="favoriteThing" :rows="4" resize="vertical" required />-->
<!--                            </Field>-->
<!--                        </div>-->

<!--                        <div id="fieldset-end">-->
<!--                            <Field :label="__('How long have you been a fan?')" :instructions="__('If you don\'t remember, just give your best estimate')">-->
<!--                                <template #label>-->
<!--                                    <Label for="fan-length-field">-->
<!--                                            <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">-->
<!--                                                <Icon name="text-short" data-collapsed-field-icon class="size-3.5 me-1 text-purple-500 dark:text-purple-400" aria-hidden="true" />-->
<!--                                                {{ __('How long have you been a fan?') }}-->
<!--                                            </span>-->
<!--                                    </Label>-->
<!--                                </template>-->
<!--                                <span class="absolute z-(&#45;&#45;z-index-above) top-1 max-sm:-right-2 sm:-left-14" v-tooltip="__('Logic attached')">-->
<!--                                        <Icon data-logic-attached name="logic-tree" class="size-3.5! text-gray-400 dark:text-gray-600" aria-hidden="true" />-->
<!--                                    </span>-->
<!--                                <Input id="fan-length-field" v-model="fanLength" />-->
<!--                            </Field>-->
<!--                        </div>-->
<!--                    </div>-->

<!--                    <div-->
<!--                        id="action-next-button"-->
<!--                        class="mt-8"-->
<!--                    >-->
<!--                        <Button-->
<!--                            variant="primary"-->
<!--                            @click.prevent="inspectActionButton('action_next')"-->
<!--                            :data-editing-field="inspectorTarget === 'action_next' ? '' : undefined"-->
<!--                            :data-editing-item="inspectorTarget === 'action_next' ? '' : undefined"-->
<!--                            class="border-0! dark:border-0! ring-0! shadow-none!"-->
<!--                            style="&#45;&#45;theme-color-primary: var(&#45;&#45;theme-color-gray-950)"-->
<!--                            :text="nextPageButtonLabel"-->
<!--                        />-->
<!--                    </div>-->
<!--                </div>-->
<!--            </Card>-->
<!--        </div>-->
<!--    </Panel>-->
</template>

<style>
[data-drop-indicator]:has(~ [data-empty-section]) {
    margin-bottom: 1rem;
}
</style>
