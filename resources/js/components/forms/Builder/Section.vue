<script setup>
import { Button, Card, Field, Heading, Icon, Label, Panel, PanelHeader, PublishContainer } from '@ui';
import WidthSelector from '@/components/fields/WidthSelector.vue';
import { computed } from 'vue';
import { uniqid } from '@/bootstrap/globals.js';

const props = defineProps({
    section: Object,
    fieldtypes: Array,
    fieldView: String,
});

const editingField = defineModel('editingField');

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

const selectField = (field) => editingField.value = field;
const isEditingField = (field) => editingField.value?._id === field._id;

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

    editingField.value = field;
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

    editingField.value = newField;
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

    editingField.value = null;
};

defineExpose({ addField });
</script>

<template>
    <Panel
        class="mx-auto max-w-5xl"
        :class="{ 'pb-0': section.collapsed }"
        :data-panel-collapsed="section.collapsed ? 'true' : 'false'"
    >
        <PanelHeader class="relative flex items-center justify-between">
            <Heading :text="__(section.title)" />
            <Button
                class="static! [&_svg]:size-3.5 rounded-xl after:content-[''] after:absolute after:inset-0"
                :icon="section.collapsed ? 'expand' : 'collapse'"
                size="sm"
                variant="ghost"
                :aria-label="__('Toggle section visibility')"
                @click="toggleCollapsed"
            />
        </PanelHeader>

        <div
            style="--tw-ease: ease;"
            class="h-auto visible transition-[height,visibility] duration-[250ms,2s]"
            :class="{ 'h-0! invisible! overflow-clip': section.collapsed }"
        >
            <Card class="section-drop-zone" :data-section-drop-zone="section._id">
                <div v-if="section.fields.length === 0" class="h-[670px] flex items-center justify-center rounded-lg border border-dashed border-zinc-300">
                    <div>
                        <span class="text-zinc-500 mr-2">{{ __('Drag fields here to build your form or') }}</span>
                        <Button size="xs" pill icon="link" :text="__('Link Existing')" />
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
                    <div class="field-sort-container space-y-7" :data-sort-section="section._id" :data-fields-collapsed="fieldView === 'collapsed' ? 'true' : null">
                        <div
                            v-for="field in section.fields"
                            :key="field._id"
                            data-field-item
                            :data-editing-field="isEditingField(field) ? '' : undefined"
                            :data-editing-item="isEditingField(field) ? '' : undefined"
                            :class="{ 'cursor-pointer': !isEditingField(field) }"
                            @click.stop="isEditingField(field) || selectField(field)"
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
                                :id="field._id"
                                :class="{ 'opacity-60': field.config.hidden }"
                                :label="field.config.display"
                            >
                                <template #label>
                                    <Label :for="field._id" :class="{ 'cursor-pointer': !isEditingField(field) }">
                                        <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <Icon :name="field.icon" data-collapsed-field-icon class="size-3.5 me-1 text-teal-600 dark:text-teal-400" aria-hidden="true" />
                                            {{ field.config.display }}
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
</template>
