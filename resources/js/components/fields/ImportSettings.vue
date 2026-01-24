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
            </div>
        </CardPanel>
    </StackContent>
</template>

<script>
import { Button, Heading, CardPanel, Field, Input, StackHeader, StackContent } from '@/components/ui';

export default {
    components: { StackContent, StackHeader, Heading, Button, CardPanel, Field, Input },

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

    methods: {
        focus() {
            this.$els.display.select();
        },

        updateField(handle, value) {
            this.values[handle] = value;
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
