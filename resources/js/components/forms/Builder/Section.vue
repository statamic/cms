<script setup lang="ts">
import { Button, Card, Field, Heading, Icon, Label, Panel, PanelHeader, PublishContainer } from '@ui';
import WidthSelector from '@/components/fields/WidthSelector.vue';
import { computed, ref } from 'vue';
import { uniqid } from '@/bootstrap/globals.js';
import { categories, categoryColorClasses } from './categories';
import { FieldView, injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';

const emit = defineEmits<{
    (e: 'deleted', value: null): void;
}>();

const props = defineProps<{
    section: Object,
    fieldtypes: Array,
    canDeleteSection: Boolean,
}>();

const { fieldView, inspecting, inspectorType, inspect, clearInspector } = injectBuilderContext();

const editingField = computed(() => inspectorType.value === InspectorType.Field ? inspecting.value : null);
const isEditingField = (field) => editingField.value?._id === field._id;
const inspectField = (field: any) => inspect(InspectorType.Field, field);

const toggleCollapsed = () => props.section.collapsed = !props.section.collapsed;

const blueprint = computed(() => ({
    tabs: [{
        handle: 'main',
        sections: [{
            fields: props.section.fields
                .filter((field) => field.publishConfig)
                .map((field) => field.publishConfig),
        }],
    }],
}));

const fieldtypeCategory = (field) => {
    const fieldtype = props.fieldtypes.find((f) => f.handle === field.fieldtype);
    const hue = fieldtype?.categories?.[0] || 'other';
    return categories[hue] ?? categories.other;
};

const fieldtypeIconColorClass = (field) => categoryColorClasses[fieldtypeCategory(field).color].icon;

const addField = (fieldtypeHandle, index = null) => {
    const { section } = props;
    const handle = uniqid();
    const fieldtype = props.fieldtypes.find((f) => f.handle === fieldtypeHandle);

    const field = {
        _id: `${section._id}-${section.fields.length}`,
        config: {
            display: __(fieldtype.title),
            hidden: false,
        },
        fieldtype: fieldtypeHandle,
        handle,
        icon: fieldtype?.icon || 'fieldtype-generic',
        type: 'inline',
        publishConfig: { ...fieldtype.preview.config, handle },
    };

    section.fields.splice(index ?? section.fields.length, 0, field);
    section.values[handle] = fieldtype.preview.value;
    section.meta[handle] = fieldtype.preview.meta;

    inspectField(field);
};

const toggleFieldVisibility = (field) => field.config.hidden = !field.config.hidden;

const duplicateField = (fieldId) => {
    const { section } = props;
    const field = section.fields.find((f) => f._id === fieldId);
    if (!field) return;

    const index = section.fields.indexOf(field);
    const handle = uniqid();

    const newField = {
        ...field,
        _id: `${section._id}-${section.fields.length}`,
        handle,
        config: { ...field.config, display: `${field.config.display} (${__('Duplicate')})` },
        publishConfig: { ...field.publishConfig, handle },
    };

    section.fields.splice(index + 1, 0, newField);
    section.values[handle] = section.values[field.handle];
    section.meta[handle] = section.meta[field.handle];

    inspectField(newField);
};

const removeField = (fieldId) => {
    const { section } = props;
    const field = section.fields.find((f) => f._id === fieldId);
    if (!field) return;

    section.fields.splice(section.fields.indexOf(field), 1);
    delete section.values[field.handle];
    delete section.meta[field.handle];

    if (section.fields.length === 0) {
        toggleCollapsed();
    }

    clearInspector();
};

const editSection = () => inspect(InspectorType.Section, props.section);
const deleteSection = () => emit('deleted', props.section._id);

defineExpose({ addField });

// TODO: Refactor everything below this line
const inspectorTarget = ref('field');
const fanLength = ref('');
const favoriteThing = ref('');
const panelCollapsed = ref(false);
const nextPageButtonLabel = ref(__('Next Page'));

const inspectActionButton = (target) => {
    inspectorTarget.value = target;
};
</script>

<template>
    <Panel
        :id="`section-${section._id}`"
        class="mx-auto max-w-5xl"
        :class="{ 'pb-0': section.collapsed }"
        :data-panel-collapsed="section.collapsed ? 'true' : 'false'"
    >
        <PanelHeader class="flex items-center justify-between" @click="toggleCollapsed">
            <Heading class="cursor-pointer flex-1" :text="__(section.title)" />
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
                <!-- Empty state - sort container always present for drag indicators -->
                <div
                    v-if="section.fields.length === 0"
                    class="field-sort-container"
                    :data-sort-section="section._id"
                >
                    <div data-empty-section class="h-[670px] flex items-center justify-center rounded-lg border border-dashed border-zinc-300">
                        <div>
                            <span class="text-zinc-500 mr-2">{{ __('Drag fields here to build your form or') }}</span>
                            <Button size="xs" pill icon="link" :text="__('Link Existing')" />
                        </div>
                    </div>
                </div>

                <PublishContainer
                    v-else
                    :name="'form-builder-' + section._id"
                    :blueprint="blueprint"
                    v-model="section.values"
                    :meta="section.meta"
                    :track-dirty-state="false"
                >
                    <div class="field-sort-container field-grid" :data-sort-section="section._id" :data-fields-collapsed="fieldView === FieldView.Collapsed ? 'true' : null">
                        <div
                            v-for="field in section.fields"
                            :key="field._id"
                            :id="`field-${field._id}`"
                            data-field-item
                            :data-editing-field="isEditingField(field) ? '' : undefined"
                            :data-editing-item="isEditingField(field) ? '' : undefined"
                            :class="[`field-w-${field.config.width || 100}`, { 'cursor-pointer': !isEditingField(field) }]"
                            @click.stop="isEditingField(field) || inspectField(field)"
                        >
                            <div
                                v-if="isEditingField(field)"
                                class="!absolute z-(--z-index-above) -top-0.5 end-0.5 flex items-center"
                            >
                                <WidthSelector
                                    size="base"
                                    variant="filled"
                                    class="me-2 bg-blue-50! border-blue-300! dark:bg-blue-950/40! dark:border-blue-600! [&_[data-state]]:!border-blue-200 dark:[&_[data-state]]:!border-blue-700 [&_[data-state='selected']]:bg-blue-100! [&_[data-state='selected'][data-last='false']]:!border-blue-100 [&_[data-last='true']]:!border-blue-300 dark:[&_[data-state='selected']]:bg-blue-900! dark:[&_[data-state='selected'][data-last='false']]:!border-blue-900 dark:[&_[data-last='true']]:!border-blue-600"
                                    :model-value="field.config.width || 100"
                                    @update:model-value="field.config.width = $event"
                                />
                                <Button
                                    size="sm"
                                    inset
                                    icon="duplicate"
                                    variant="subtle"
                                    :aria-label="__('Duplicate field')"
                                    :title="__('Duplicate field')"
                                    class="[&_svg]:opacity-45"
                                    @click.stop="duplicateField(field._id)"
                                />
                                <Button
                                    size="sm"
                                    inset
                                    :icon="field.config.hidden ? 'eye-closed' : 'eye'"
                                    variant="subtle"
                                    :aria-label="field.config.hidden ? __('Show field') : __('Hide field')"
                                    :title="field.config.hidden ? __('Show field') : __('Hide field')"
                                    class="[&_svg]:opacity-45"
                                    @click.stop="toggleFieldVisibility(field)"
                                />
                                <Button
                                    size="sm"
                                    inset
                                    icon="trash"
                                    variant="subtle"
                                    :aria-label="__('Remove field')"
                                    :title="__('Remove field')"
                                    class="[&_svg]:opacity-45"
                                    @click.stop="removeField(field._id)"
                                />
                            </div>
                            <Field
                                :class="{ 'opacity-60': field.config.hidden }"
                                :label="field.config.display"
                                :instructions="field.config.instructions"
                            >
                                <template #label>
                                    <Label :class="{ 'cursor-pointer': !isEditingField(field) }">
                                        <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <Icon :name="field.icon" data-collapsed-field-icon :class="['size-3.5 me-1', fieldtypeIconColorClass(field)]" aria-hidden="true" />
                                            {{ __(field.config.display) }}
                                            <Icon v-if="field.config.hidden" name="eye-closed" class="size-3.5! text-gray-400 dark:text-gray-500" :aria-label="__('Hidden')" v-tooltip="__('Hidden')" />
                                        </span>
                                    </Label>
                                </template>
                                <div v-if="field.publishConfig" inert>
                                    <component
                                        :is="`${field.publishConfig.component || field.publishConfig.type}-fieldtype`"
                                        :config="field.publishConfig"
                                        :value="section.values[field.handle]"
                                        :meta="section.meta[field.handle]"
                                        :handle="field.handle"
                                    />
                                </div>
                            </Field>
                        </div>
                    </div>
                </PublishContainer>
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
