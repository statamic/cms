<template>
    <div>
        <ui-button icon="link" @click="open = true" :text="__('Link Existing')" />

        <stack narrow v-if="open" @closed="open = false" name="field-linker" v-slot="{ close }">
            <div class="flex h-full flex-col bg-white dark:bg-dark-800">
                <div
                    class="flex items-center justify-between border-b border-gray-300 bg-gray-200 px-6 py-2 text-lg font-medium dark:border-dark-900 dark:bg-dark-600"
                >
                    {{ __('Link Fields') }}
                    <button type="button" class="btn-close" @click="close" v-html="'&times'" />
                </div>

                <div class="flex-1 overflow-auto p-6">
                    <div>
                        <p class="mb-2 text-sm font-medium" v-text="__('Link a single field')" />
                        <p
                            class="mb-2 text-2xs text-gray"
                            v-text="__('Changes to this field in the fieldset will stay in sync.')"
                        />
                        <v-select
                            name="field"
                            :placeholder="__('Fields')"
                            :options="fieldSuggestions"
                            :multiple="false"
                            :searchable="true"
                            :reduce="(opt) => opt.value"
                            v-model="reference"
                        >
                            <template v-slot:option="option">
                                <div class="flex items-center">
                                    <span
                                        v-text="option.fieldset"
                                        class="text-2xs text-gray-500 dark:text-dark-150 ltr:mr-2 rtl:ml-2"
                                    />
                                    <span v-text="option.label" />
                                </div>
                            </template>
                            <template v-slot:no-options>
                                <div
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-dark-200 ltr:text-left rtl:text-right"
                                    v-text="__('No options to choose from.')"
                                />
                            </template>
                        </v-select>
                        <button
                            class="btn-primary mt-6 w-full"
                            :class="{ 'opacity-50': !reference }"
                            :disabled="!reference"
                            @click="linkField"
                            v-text="__('Link')"
                        />
                    </div>
                    <div class="my-4 flex items-center">
                        <div class="flex-1 border-b border-gray-300 dark:border-dark-200" />
                        <div class="mx-4 text-2xs text-gray-600 dark:text-dark-175" v-text="__('or')"></div>
                        <div class="flex-1 border-b border-gray-300 dark:border-dark-200" />
                    </div>
                    <div>
                        <p class="mb-2 text-sm font-medium" v-text="__('Link a fieldset')" />
                        <p
                            class="mb-2 text-2xs text-gray dark:text-dark-175"
                            v-text="__('Changes to this fieldset will stay in sync.')"
                        />
                        <v-select
                            name="field"
                            :placeholder="__('Fieldsets')"
                            :options="fieldsetSuggestions"
                            :multiple="false"
                            :searchable="true"
                            :reduce="(opt) => opt.value"
                            v-model="fieldset"
                        >
                            <template v-slot:no-options>
                                <div
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-dark-200 ltr:text-left rtl:text-right"
                                    v-text="__('No options to choose from.')"
                                />
                            </template>
                        </v-select>
                        <p class="mb-2 mt-6 text-sm font-medium" v-text="__('Prefix')" />
                        <p
                            class="mb-2 text-2xs text-gray dark:text-dark-175"
                            v-text="__('messages.fieldset_link_fields_prefix_instructions')"
                        />
                        <text-input v-model="importPrefix" :placeholder="__('e.g. hero_')" />
                        <button
                            class="btn-primary mt-6 w-full"
                            :class="{ 'opacity-50': !fieldset }"
                            :disabled="!fieldset"
                            @click="linkFieldset"
                            v-text="__('Link')"
                        />
                    </div>
                </div>
            </div>
        </stack>
    </div>
</template>

<script>
import uniqid from 'uniqid';

export default {
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
