<template>
    <div>
        <Stack
            size="narrow"
            v-model:open="open"
            :title="__('Link Fields')"
            icon="fieldsets"
        >
	        <template #trigger>
		        <Button icon="link" :text="__('Link Existing')" />
	        </template>

            <div class="">
                <div class="">
                    <Field
                        :label="__('Link a single field')"
                        :instructions="__('Changes to this field in the fieldset will stay in sync.')"
                    >
                        <Combobox
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
                        :text="__('Link Field')"
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
                        :text="__('Link Fieldset')"
                        @click="linkFieldset"
                    />
                </div>
            </div>
        </Stack>
    </div>
</template>

<script>
import { nanoid as uniqid } from 'nanoid';
import { Combobox, Button, Input, Heading, Field, Stack, StackClose } from '@/components/ui';
import { usePage } from '@inertiajs/vue3';

export default {
    components: { Heading, Combobox, Button, Input, Field, Stack, StackClose },

    props: {
        excludeFieldset: String,
        withCommandPalette: Boolean,
    },

    data() {
        const fieldsetsData = usePage().props.fieldsets;
        const fieldsets = JSON.parse(
            JSON.stringify(
                Object.values(fieldsetsData).filter(
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

    mounted() {
        if (this.withCommandPalette) {
            this.addToCommandPalette();
        }
    },

    methods: {
        linkField() {
            const lastDot = this.reference.lastIndexOf('.');
            const fieldsetHandle = this.reference.substring(0, lastDot);
            const fieldHandle = this.reference.substring(lastDot + 1);

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

        addToCommandPalette() {
            if (!this.withCommandPalette) {
                return;
            }

            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Link Existing'),
                icon: 'link',
                action: () => this.open = true,
            });
        },
    },
};
</script>
