<template>
    <StackHeader :title="__('Linked fieldset')" icon="fieldsets">
        <template #actions>
            <Button variant="primary" @click.prevent="commit" :text="__('Apply')" />
            <Button v-if="isInsideSet" variant="primary" @click.prevent="commit(true)" :text="__('Apply & Close All')" />
        </template>
    </StackHeader>

    <StackContent>
        <CardPanel :heading="__('Linked fieldset')">
            <div class="publish-fields">
                <Field :label="__('Fieldset')" :instructions="__('messages.fieldset_import_fieldset_instructions')" class="form-group field-w-100">
                    <Input autofocus :model-value="config.fieldset" @update:model-value="updateField('fieldset', $event)" />
                </Field>

                <Field :label="__('Prefix')" :instructions="__('messages.fieldset_import_prefix_instructions')" class="form-group field-w-100">
                    <Input autofocus :model-value="config.prefix" @update:model-value="updateField('prefix', $event)" />
                </Field>

                <Field
                    v-if="fieldsetHasSections"
                    :label="__('Section Behavior')"
                    :instructions="sectionBehaviorInstructions"
                    class="form-group field-w-100"
                >
                    <RadioGroup
                        :model-value="sectionBehavior"
                        @update:model-value="updateField('section_behavior', $event)"
                    >
                        <Radio
                            v-for="option in sectionBehaviorOptions"
                            :key="option.value"
                            :label="option.label"
                            :description="option.description"
                            :value="option.value"
                        />
                    </RadioGroup>
                </Field>
            </div>
        </CardPanel>
    </StackContent>
</template>

<script>
import { Button, Heading, CardPanel, Field, Input, StackHeader, StackContent, RadioGroup, Radio } from '@/components/ui';

export default {
    components: { StackContent, StackHeader, Heading, Button, CardPanel, Field, Input, RadioGroup, Radio },

    props: ['config', 'isInsideSet'],

    inject: {
        commitParentField: {
            default: () => {}
        }
    },

    model: {
        prop: 'config',
        event: 'input',
    },

    data: function () {
        return {
            values: clone(this.config),
        };
    },

    computed: {
        fieldsetMeta() {
            const handle = this.values.fieldset;

            return this.$page?.props?.fieldsets?.[handle] ?? null;
        },

        fieldsetHasSections() {
            return this.fieldsetMeta?.has_sections === true;
        },

        sectionBehavior() {
            return this.values.section_behavior ?? 'preserve';
        },

        sectionBehaviorInstructions() {
            return __('messages.fieldset_import_section_behavior_instructions');
        },

        sectionBehaviorOptions() {
            return [
                {
                    label: __('Preserve'),
                    description: __('Keep imported sections as-is.'),
                    value: 'preserve',
                },
                {
                    label: __('Flatten'),
                    description: __('Merge all fields into this section.'),
                    value: 'flatten',
                },
            ];
        },
    },

    methods: {
        focus() {
            this.$els.display.select();
        },

        updateField(handle, value) {
            this.values[handle] = value;

            if (handle === 'fieldset' && ! this.fieldsetHasSections) {
                this.values.section_behavior = 'preserve';
            }
        },

        commit(shouldCommitParent = false) {
            this.$emit('committed', this.values);
            this.close();

            if (shouldCommitParent && this.commitParentField) {
                this.commitParentField(true);
            }
        },

        close() {
            this.$emit('closed');
        },
    },
};
</script>
