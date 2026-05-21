<script setup lang="ts">
import { Button, Field, Icon, Label } from '@ui';
import { computed } from 'vue';
import { __ } from '@/bootstrap/globals.js';
import { categoryColorClasses } from './categories';
import { injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    field: any;
}>();

defineEmits<{
    (e: 'remove'): void;
}>();

const { inspecting, inspectorType, inspect } = injectBuilderContext();

const fieldsets = Object.values(usePage().props.fieldsets);

const isInspecting = computed(() => inspectorType.value === InspectorType.FieldsetImport && inspecting.value?._id === props.field._id);

const fieldsetFields = computed(() => {
    const fieldset = fieldsets.find((fs) => fs.handle === props.field.fieldset);
    if (!fieldset) return [];
    return fieldset.fields.filter((f) => f.type !== 'import');
});

const inspectFieldsetImport = () => inspect(InspectorType.FieldsetImport, props.field);
</script>

<template>
    <div
        :id="`import-${field._id}`"
        data-field-item
        :data-editing-field="isInspecting ? '' : undefined"
        :data-editing-item="isInspecting ? '' : undefined"
        :class="[{ 'cursor-pointer': !isInspecting }]"
        @click.stop="isInspecting || inspectFieldsetImport()"
    >
        <div
            v-if="isInspecting"
            class="!absolute z-(--z-index-above) -top-0.5 end-0.5 flex items-center"
        >
            <Button
                size="sm"
                inset
                icon="trash"
                variant="subtle"
                :aria-label="__('Remove fieldset')"
                :title="__('Remove fieldset')"
                class="[&_svg]:opacity-45"
                @click.stop="$emit('remove')"
            />
        </div>
        <div data-fieldset-group class="space-y-7">
            <template v-for="(fieldsetField, fieldsetFieldIndex) in fieldsetFields" :key="fieldsetField.handle">
                <div :id="fieldsetFieldIndex === 0 ? 'fieldset-start' : (fieldsetFieldIndex === fieldsetFields.length - 1 ? 'fieldset-end' : undefined)">
                    <span v-if="fieldsetFieldIndex === 0" data-fieldset-label class="inline-flex gap-1.75 rounded-md font-mono text-2xs text-indigo-800">
                        <span class="inline-flex" v-tooltip="__('Linked Fieldset')">
                            <Icon name="link" class="size-3.5" aria-hidden="true" />
                        </span>
                        <span class="sr-only">{{ __('Linked Fieldset') }}</span>
                    </span>
                    <Field :label="__(fieldsetField.config.display)" :instructions="fieldsetField.config.instructions">
                        <template #label>
                            <Label>
                                <span class="inline-flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <Icon name="link" data-collapsed-field-icon class="size-3.5 me-1 " :class="categoryColorClasses['fieldsets']?.icon" aria-hidden="true" />
                                    {{ __(fieldsetField.config.display) }}
                                    <span v-if="fieldsetField.config.validate?.includes('required')" class="relative -top-px ms-0.5 text-red-600">*</span>
                                </span>
                            </Label>
                        </template>
                        <div class="pointer-events-none" inert>
                            <component
                                :is="`${field.previews[fieldsetField.handle].config.component || field.previews[fieldsetField.handle].config.type}-fieldtype`"
                                :config="field.previews[fieldsetField.handle].config"
                                :value="field.previews[fieldsetField.handle].value"
                                :meta="field.previews[fieldsetField.handle].meta"
                                :handle="fieldsetField.handle"
                            />
                        </div>
                    </Field>
                </div>
            </template>
        </div>
    </div>
</template>
