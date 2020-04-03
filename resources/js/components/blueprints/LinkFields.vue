<template>

    <div>

        <button class="btn flex w-full justify-center items-center" @click="open = true">
            <svg-icon name="hyperlink" class="mr-1" />
            <span>{{ __('Link Existing') }}</span>
        </button>

        <stack narrow v-if="open" @closed="open = false" name="field-linker">
            <div slot-scope="{ close }" class="bg-white h-full flex flex-col">

                <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                    {{ __('Link Fields') }}
                    <button
                        type="button"
                        class="btn-close"
                        @click="close"
                        v-html="'&times'" />
                </div>

                <div class="flex-1 overflow-auto p-3">

                    <div>
                        <p class="text-sm font-medium mb-1" v-text="__('Link a single field')" />
                        <p class="text-2xs text-grey mb-1" v-text="__('Changes to this field will stay in sync.')" />
                        <v-select
                            name="field"
                            :placeholder="__('Fields')"
                            :options="fieldSuggestions"
                            :multiple="false"
                            :searchable="true"
                            :reduce="(opt) => opt.value"
                            v-model="reference">
                            <template slot="option" slot-scope="option">
                                <div class="flex items-center">
                                    <span v-text="option.fieldset" class="text-2xs text-grey-50 mr-1" />
                                    <span v-text="option.label" />
                                </div>
                            </template>
                        </v-select>
                        <button
                            class="btn-primary w-full mt-3"
                            :class="{ 'opacity-50': !reference }"
                            :disabled="!reference"
                            @click="linkField"
                            v-text="__('Link')" />
                    </div>
                    <div class="my-2 flex items-center">
                        <div class="border-b border-grey-30 flex-1" />
                        <div class="text-2xs text-grey-60 mx-2">or</div>
                        <div class="border-b border-grey-30 flex-1" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1" v-text="__('Link a fieldset')" />
                        <p class="text-2xs text-grey mb-1" v-text="__('Changes to this fieldset will stay in sync.')" />
                        <v-select
                            name="field"
                            :placeholder="__('Fieldsets')"
                            :options="fieldsetSuggestions"
                            :multiple="false"
                            :searchable="true"
                            :reduce="(opt) => opt.value"
                            v-model="fieldset"
                        />
                        <p class="text-sm font-medium mt-3 mb-1" v-text="__('Prefix')" />
                        <p class="text-2xs text-grey mb-1" v-text="__('messages.fieldset_link_fields_prefix_instructions')" />
                        <text-input v-model="importPrefix" placeholder="eg. hero_" />
                        <button
                            class="btn-primary w-full mt-3"
                            :class="{ 'opacity-50': !fieldset }"
                            :disabled="!fieldset"
                            @click="linkFieldset"
                            v-text="__('Link')" />
                    </div>

                </div>

            </div>
        </stack>

    </div>

</template>

<script>
import uniqid from 'uniqid';

export default {

    data() {
        let fields = this.$config.get('fieldsetFields');
        let fieldsets = this.$config.get('fieldsets');

        return {
            open: false,
            reference: null,
            fieldset: null,
            importPrefix: null,
            fieldSuggestions: Object.values(fields).map(field => {
                return {
                    value: `${field.fieldset.handle}.${field.handle}`,
                    label: field.display,
                    fieldset: fieldsets[field.fieldset.handle].title
                };
            }),
            fieldsetSuggestions: Object.values(fieldsets).map(fieldset => {
                return {
                    value: fieldset.handle,
                    label: fieldset.title,
                };
            })
        }
    },

    methods: {

        linkField() {
            const field = JSON.parse(JSON.stringify(window.Statamic.$config.get('fieldsetFields')[this.reference]));

            this.linkAndClose({
                _id: uniqid(),
                type: 'reference',
                fieldtype: field.type,
                field_reference: this.reference,
                handle: field.handle,
                config: { ...field.config, isNew: true },
                config_overrides: []
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
        }

    }

}
</script>
