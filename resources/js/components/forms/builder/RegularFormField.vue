<script setup lang="ts">
import { Button, Field, Icon, Label } from '@ui';
import { computed } from 'vue';
import { FieldView, injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import { categories, categoryColorClasses } from './categories';
import FieldNumber from '@/components/forms/FieldNumber.vue';
import WidthSelector from '@/components/fields/WidthSelector.vue';
import { __ } from '@/bootstrap/globals';
import { KEYS } from '@/components/field-conditions/Constants';

defineEmits<{
    (e: 'duplicate'): void;
    (e: 'remove'): void;
    (e: 'width-changed', width: number): void;
}>();

const props = defineProps<{
    field: any;
    fieldtypes: any[];
    isFirstRow?: boolean;
    isLastRow?: boolean;
}>();

const { dirty, errors, fieldView, inspect, inspecting, inspectorType } = injectBuilderContext();

const inspectField = () => inspect(InspectorType.Field, props.field);
const isInspecting = computed(() => inspectorType.value === InspectorType.Field && inspecting.value?._id === props.field._id);

const toggleVisibility = () => {
    props.field.config.hidden = !props.field.config.hidden;
    dirty();
};

const fieldtypeCategory = computed(() => {
    const fieldtype = props.fieldtypes.find((f) => f.handle === props.field.fieldtype);
    const hue = fieldtype?.categories?.[0] || 'other';
    return categories[hue] ?? categories.other;
});

const isInformationField = computed(() => fieldtypeCategory.value === categories.information);

const iconColorClass = computed(() => categoryColorClasses[fieldtypeCategory.value.color].icon);

const hasLogic = computed(() => KEYS.some(key => props.field.config[key] && Object.keys(props.field.config[key]).length > 0));

const hasErrors = computed(() => {
    const allErrors = errors?.value ?? {};
    return Object.keys(allErrors).some(key => key.startsWith(`${props.field._id}.`));
});
</script>

<template>
    <div
        :id="`field-${field._id}`"
        data-field-item
        :data-editing-field="isInspecting ? '' : undefined"
        :data-editing-item="isInspecting ? '' : undefined"
        :data-first-row="isFirstRow ? '' : undefined"
        :data-last-row="isLastRow ? '' : undefined"
        :class="[`field-w-${field.config.width || 100}`, { 'cursor-pointer': !isInspecting }]"
        @click.stop="isInspecting || inspectField()"
    >
        <div
            v-if="isInspecting"
            class="!absolute z-(--z-index-above) -top-0.5 end-0.5 flex items-center bg-blue-50 dark:bg-blue-950 ps-3"
            data-editing-field-actions
        >
            <WidthSelector
                size="base"
                variant="filled"
                class="me-2 bg-blue-50! border-blue-300! dark:bg-blue-950/40! dark:border-blue-600! [&_[data-state]]:!border-blue-200 dark:[&_[data-state]]:!border-blue-700 [&_[data-state='selected']]:bg-blue-100! [&_[data-state='selected'][data-last='false']]:!border-blue-100 [&_[data-last='true']]:!border-blue-300 dark:[&_[data-state='selected']]:bg-blue-900! dark:[&_[data-state='selected'][data-last='false']]:!border-blue-900 dark:[&_[data-last='true']]:!border-blue-600"
                :model-value="field.config.width || 100"
                @update:model-value="$emit('width-changed', $event)"
            />
            <Button
                size="sm"
                inset
                icon="duplicate"
                variant="subtle"
                :aria-label="__('Duplicate field')"
                :title="__('Duplicate field')"
                class="[&_svg]:opacity-45"
                @click.stop="$emit('duplicate')"
            />
            <Button
                size="sm"
                inset
                :icon="field.config.hidden ? 'eye-closed' : 'eye'"
                variant="subtle"
                :aria-label="field.config.hidden ? __('Show field') : __('Hide field')"
                :title="field.config.hidden ? __('Show field') : __('Hide field')"
                class="[&_svg]:opacity-45"
                @click.stop="toggleVisibility"
            />
            <Button
                size="sm"
                inset
                icon="trash"
                variant="subtle"
                :aria-label="__('Remove field')"
                :title="__('Remove field')"
                class="[&_svg]:opacity-45"
                @click.stop="$emit('remove')"
            />
        </div>
        <Field
            v-if="isInformationField && fieldView === FieldView.Collapsed"
            :class="{ 'opacity-60': field.config.hidden }"
        >
            <template #label>
                <Label :class="['mb-0', { 'cursor-pointer': !isInspecting }]">
                    <FieldNumber :field-key="field._id" class="me-1" />
                    <Icon :name="field.icon" data-collapsed-field-icon :class="['size-3.5 mb-0.25! me-2.5', iconColorClass]" aria-hidden="true" />
                    <Icon
                        v-if="hasLogic"
                        data-logic-attached
                        name="logic-tree"
                        class="size-3.5! inline-block text-gray-400 dark:text-gray-500 me-2.5 mb-0.5"
                        :aria-label="__('Logic attached')"
                        v-tooltip="__('Logic attached')"
                    />
                    <span>{{ __(field.config.display) }}</span>
                    <Icon v-if="field.type === 'reference'" name="link" class="inline-block size-3! text-indigo-500 dark:text-indigo-400 mb-0.5 ms-2" :aria-label="__('Linked Field')" v-tooltip="__('Linked Field')" />
                    <Icon v-if="field.config.hidden" name="eye-closed" class="inline-block size-3.5! text-gray-400 dark:text-gray-500 mb-0.5 ms-2" :aria-label="__('Hidden')" v-tooltip="__('Hidden')" />
                </Label>
            </template>
        </Field>
        <div v-else-if="isInformationField" :class="{ 'opacity-60': field.config.hidden }">
            <div v-if="field.preview" inert>
                <component
                    :is="`${field.preview.config.component || field.preview.config.type}-fieldtype`"
                    :config="field.preview.config"
                    :value="field.preview.value"
                    :meta="field.preview.meta"
                    :handle="field.handle"
                />
            </div>
        </div>
        <Field
            v-else
            :class="{ 'opacity-60': field.config.hidden }"
            :label="__(field.config.display)"
            :instructions="field.config.instructions"
            :error="hasErrors ? __('This field has errors. Please fix them before saving.') : null"
        >
            <template #label>
                <Label :class="['', { 'cursor-pointer': !isInspecting }]">
                    <FieldNumber :field-key="field._id" class="me-1" />
                    <Icon :name="field.icon" data-collapsed-field-icon :class="['size-3.5 mb-0.25! me-2.5', iconColorClass]" aria-hidden="true" />
                    <Icon
                        v-if="hasLogic"
                        data-logic-attached
                        name="logic-tree"
                        class="size-3.5! inline-block text-gray-400 dark:text-gray-500 me-2.5 mb-0.5"
                        :aria-label="__('Logic attached')"
                        v-tooltip="__('Logic attached')"
                    />
                    <span>
                        {{ __(field.config.display) }}
                        <span v-if="field.config.validate?.includes('required')" class="relative -top-px ms-0.5 text-red-600">*</span>
                    </span>
                    <Icon
                        v-if="field.type === 'reference'"
                        name="link"
                        class="inline-block size-3! text-indigo-500 dark:text-indigo-400 mb-0.5 ms-2"
                        :aria-label="__('Linked Field: :reference', { reference: field.field_reference })"
                        v-tooltip="__('Linked Field: :reference', { reference: field.field_reference })"
                    />
                    <Icon
                        v-if="field.config.hidden"
                        name="eye-closed"
                        class="inline-block size-3.5! text-gray-400 dark:text-gray-500 mb-0.5 ms-2"
                        :aria-label="__('Hidden')"
                        v-tooltip="__('Hidden')"
                    />
                </Label>
            </template>
            <div v-if="field.preview" inert>
                <component
                    :is="`${field.preview.config.component || field.preview.config.type}-fieldtype`"
                    :config="field.preview.config"
                    :value="field.preview.value"
                    :meta="field.preview.meta"
                    :handle="field.handle"
                />
            </div>
        </Field>
    </div>
</template>
