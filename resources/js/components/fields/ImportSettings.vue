<template>
    <div class="h-full overflow-auto bg-white dark:bg-gray-800 p-3 rounded-l-xl">
        <header class="flex items-center justify-between pl-3 mb-6">
            <Heading :text="__('Linked fieldset')" size="lg" icon="fieldsets" />
            <div class="flex items-center gap-3">
                <Button variant="ghost" :text="__('Cancel')" @click.prevent="close" />
                <Button variant="primary" @click.prevent="commit" :text="__('Apply')" />
                <Button v-if="isInsideSet" variant="primary" @click.prevent="commit(true)" :text="__('Apply & Close All')" />
            </div>
        </header>

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
    </div>
</template>

<script>
import { Button, Heading, CardPanel, Field, Input } from '@statamic/cms/ui';

export default {
    components: { Heading, Button, CardPanel, Field, Input },

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
