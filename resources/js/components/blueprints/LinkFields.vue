<template>
    <div>
        <ui-button icon="link" @click="open = true" :text="__('Link Existing')" />

        <stack narrow v-if="open" @closed="open = false" name="field-linker" v-slot="{ close }">
            <div class="h-full overflow-auto bg-white dark:bg-gray-800 p-3 rounded-l-xl">
                <header class="flex items-center justify-between pl-3">
                    <Heading :text="__('Link Fields')" size="lg" icon="fieldsets" />
                    <Button type="button" icon="x" variant="subtle" @click="close" />
                </header>

                <div class="flex-1 overflow-auto px-3 py-4">
                    <Field
                        :label="__('Link a single field')"
                        :instructions="__('Changes to this field in the fieldset will stay in sync.')"
                    >
                        <Combobox
                            class="w-full"
                            :placeholder="__('Fields')"
                            :options="fieldSuggestions"
                            searchable
                            :model-value="reference"
                            @update:modelValue="reference = $event"
                        >
                            <template #option="option">
                                <div class="flex items-center">
                                    <span
                                        v-text="option.fieldset"
                                        class="text-2xs text-gray-500 dark:text-dark-150 ltr:mr-2 rtl:ml-2"
                                    />
                                    <span v-text="option.label" />
                                </div>
                            </template>
                            <template #no-options>
                                <div
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-dark-200 ltr:text-left rtl:text-right"
                                    v-text="__('No options to choose from.')"
                                />
                            </template>
                        </Combobox>
                    </Field>

                    <Button
                        class="w-full mt-6"
                        variant="primary"
                        :disabled="!reference"
                        :text="__('Link')"
                        @click="linkField"
                    />

                    <div class="my-4 flex items-center">
                        <div class="flex-1 border-b border-gray-300 dark:border-dark-200" />
                        <div class="mx-4 text-2xs text-gray-600 dark:text-dark-175" v-text="__('or')"></div>
                        <div class="flex-1 border-b border-gray-300 dark:border-dark-200" />
                    </div>

                    <Field
                        class="mb-6"
                        :label="__('Link a fieldset')"
                        :instructions="__('Changes to this fieldset will stay in sync.')"
                    >
                        <Combobox
                            class="w-full"
                            :placeholder="__('Fieldsets')"
                            :options="fieldsetSuggestions"
                            searchable
                            :model-value="fieldset"
                            @update:modelValue="fieldset = $event"
                        >
                            <template #no-options>
                                <div
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-dark-200 ltr:text-left rtl:text-right"
                                    v-text="__('No options to choose from.')"
                                />
                            </template>
                        </Combobox>
                    </Field>

                    <Field
                        :label="__('Prefix')"
                        :instructions="__('messages.fieldset_link_fields_prefix_instructions')"
                    >
                        <Input v-model="importPrefix" :placeholder="__('e.g. hero_')" />
                    </Field>

                    <Button
                        class="w-full mt-6"
                        variant="primary"
                        :disabled="!fieldset"
                        :text="__('Link')"
                        @click="linkFieldset"
                    />
                </div>
            </div>
        </stack>
    </div>
</template>

<script>
import uniqid from 'uniqid';
import { Combobox, Button, Input, Heading, Field } from '@statamic/ui';

export default {
    components: { Heading, Combobox, Button, Input, Field },

    props: {
        excludeFieldset: String,
    },

    data() {
        const fieldsets = JSON.parse(
            JSON.stringify(
                Object.values(this.$config.get('fieldsets')).filter(
                    (fieldset) => fieldset.handle != this.excludeFieldset,
                ),
            ),
        );

        const fieldSuggestions = fieldsets.flatMap((fieldset) => {
            return fieldset.fields
                .filter((field) => field.type !== 'import')
                .map((field) => ({
                    value: `${fieldset.handle}.${field.handle}`,
                    label: __(field.config.display),
                    fieldset: __(fieldset.title),
                }));
        });

        return {
            open: false,
            reference: null,
            fieldset: null,
            importPrefix: null,
            fieldSuggestions,
            fieldsetSuggestions: fieldsets.map((fieldset) => ({
                value: fieldset.handle,
                label: __(fieldset.title),
            })),
            fieldsets,
        };
    },

    methods: {
        linkField() {
            const [fieldsetHandle, fieldHandle] = this.reference.split('.');

            const field = this.fieldsets
                .find((fieldset) => fieldset.handle === fieldsetHandle)
                .fields.find((field) => field.handle === fieldHandle);

            field.config.isNew = true;

            this.linkAndClose({
                ...field,
                _id: uniqid(),
                type: 'reference',
                field_reference: this.reference,
                config_overrides: [],
            });
        },

        linkFieldset() {
            this.linkAndClose({
                _id: uniqid(),
                type: 'import',
                fieldset: this.fieldset,
                prefix: this.importPrefix,
            });
        },

        linkAndClose(field) {
            this.$emit('linked', field);
            this.open = false;
            this.reference = null;
            this.fieldset = null;
            this.importPrefix = null;
        },
    },
};
</script>
