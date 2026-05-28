<script setup lang="ts">
import { Button, Field, Icon, Label } from '@ui';
import { computed } from 'vue';
import { injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import { categories, categoryColorClasses } from './categories';
import WidthSelector from '@/components/fields/WidthSelector.vue';
import { __ } from '@/bootstrap/globals';

const props = defineProps<{
    field: any;
    fieldtypes: any[];
}>();

defineEmits<{
    (e: 'duplicate'): void;
    (e: 'remove'): void;
    (e: 'width-changed', width: number): void;
}>();

const { dirty, errors, inspect, inspecting, inspectorType } = injectBuilderContext();

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

const iconColorClass = computed(() => categoryColorClasses[fieldtypeCategory.value.color].icon);

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
        :class="[`field-w-${field.config.width || 100}`, { 'cursor-pointer': !isInspecting }]"
        @click.stop="isInspecting || inspectField()"
    >
        <div
            v-if="isInspecting"
            class="!absolute z-(--z-index-above) -top-0.5 end-0.5 flex items-center bg-blue-50 dark:bg-blue-950 ps-3"
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
            :class="{ 'opacity-60': field.config.hidden }"
            :label="field.config.display"
            :instructions="field.config.instructions"
            :error="hasErrors ? __('This field has errors. Please fix them before saving.') : null"
        >
            <template #label>
                <Label :class="{ 'cursor-pointer': !isInspecting }">
                    <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                        <Icon :name="field.icon" data-collapsed-field-icon :class="['size-3.5 me-1', iconColorClass]" aria-hidden="true" />
                        <span>
                            {{ __(field.config.display) }}
                            <span v-if="field.config.validate?.includes('required')" class="relative -top-px ms-0.5 text-red-600">*</span>
                        </span>
                        <Icon v-if="field.type === 'reference'" name="link" class="size-3! text-indigo-500 dark:text-indigo-400" :aria-label="__('Linked Field')" v-tooltip="__('Linked Field')" />
                        <Icon v-if="field.config.hidden" name="eye-closed" class="size-3.5! text-gray-400 dark:text-gray-500" :aria-label="__('Hidden')" v-tooltip="__('Hidden')" />
                    </span>
                </Label>
            </template>
            <!-- Keep logic icon farther left for full-width fields, but pull it closer on narrower/non-editing cards to avoid clashing with surrounding fields. -->
            <span
                v-if="field.config.if || field.config.unless"
                class="absolute z-(&#45;&#45;z-index-above) top-1 max-sm:-right-2"
                :class="
                    (field.config.width || 100) === 100
                        ? 'sm:-left-13'
                        : (isInspecting ? 'sm:-left-12' : 'sm:-left-8')
                "
                v-tooltip="__('Logic attached')"
            >
                <Icon data-logic-attached name="logic-tree" class="size-3.5! text-gray-400 dark:text-gray-500" aria-hidden="true" />
            </span>
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
