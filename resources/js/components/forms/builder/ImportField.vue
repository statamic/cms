<script setup lang="ts">
import { Button, Description, Field, Icon, Label } from '@ui';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { injectBuilderContext, InspectorType } from '@/pages/forms/Builder.vue';
import { __ } from '@/bootstrap/globals';

const props = defineProps<{
    field: any;
    isFirstRow?: boolean;
    isLastRow?: boolean;
}>();

defineEmits<{
    (e: 'remove'): void;
}>();

const { errors, inspect, inspecting, inspectorType } = injectBuilderContext();

const fieldsets = Object.values(usePage().props.fieldsets);

const isInspecting = computed(() => inspectorType.value === InspectorType.FieldsetImport && inspecting.value?._id === props.field._id);

const fieldset = computed(() => fieldsets.find((fs) => fs.handle === props.field.fieldset));

const fieldsetFields = computed(() => {
    if (!fieldset.value) return [];
    return fieldset.value.fields.filter((f) => f.type !== 'import');
});

const inspectFieldsetImport = () => inspect(InspectorType.FieldsetImport, props.field);

const errorMessage = computed(() => {
    const allErrors = errors?.value ?? {};
    const key = Object.keys(allErrors).find(k => k.startsWith(`${props.field._id}.`));
    return key ? allErrors[key]?.[0] : null;
});
</script>

<template>
    <div
        :id="`import-${field._id}`"
        data-field-item
        :data-editing-field="isInspecting ? '' : undefined"
        :data-editing-item="isInspecting ? '' : undefined"
        :data-first-row="isFirstRow ? '' : undefined"
        :data-last-row="isLastRow ? '' : undefined"
        :class="[{ 'cursor-pointer': !isInspecting }]"
        @click.stop="isInspecting || inspectFieldsetImport()"
    >
        <div
            v-if="isInspecting"
            class="!absolute z-(--z-index-above) -top-0.5 end-0.5 flex items-center bg-blue-50 ps-2.5"
            data-editing-field-actions
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
                        <span class="sr-only">{{ __('Linked Fieldset: :title', { title: __(fieldset.title) }) }}</span>
                    </span>
                    <Field :label="__(fieldsetField.config.display)" :instructions="fieldsetField.config.instructions">
                        <template #label>
                            <Label>
                                <FieldNumber :field-key="`${field._id}:${fieldsetField.handle}`" class="me-1" />
                                <Icon
                                    :name="fieldsetField.icon || 'generic-field'"
                                    data-collapsed-field-icon
                                    class="size-3.5 mb-0.25! me-2.5 text-gray-600 dark:text-gray-400"
                                    aria-hidden="true"
                                />
                                <span>
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
                    <Description
                        v-if="errorMessage && fieldsetFieldIndex === fieldsetFields.length - 1"
                        :text="errorMessage"
                        class="!text-red-600 mt-4"
                    />
                </div>
            </template>
        </div>
    </div>
</template>
