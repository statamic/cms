<template>

    <div>

        <button class="btn flex w-full justify-center items-center" @click="open = true">
            <svg-icon name="hyperlink" class="mr-1 w-4 h-4" />
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
                            <template v-slot:no-options>
                               <div class="text-sm text-grey-70 text-left py-1 px-2" v-text="__('No options to choose from.')" />
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
                        <div class="text-2xs text-grey-60 mx-2" v-text="__('or')"></div>
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
                        >
                            <template v-slot:no-options>
                                <div class="text-sm text-grey-70 text-left py-1 px-2" v-text="__('No options to choose from.')" />
                            </template>
                        </v-select>
                        <p class="text-sm font-medium mt-3 mb-1" v-text="__('Prefix')" />
                        <p class="text-2xs text-grey mb-1" v-text="__('messages.fieldset_link_fields_prefix_instructions')" />
                        <text-input v-model="importPrefix" :placeholder="__('e.g. hero_')" />
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

    props: {
        excludeFieldset: String
    },

    data() {
        const fieldsets = JSON.parse(JSON.stringify(
            Object.values(this.$config.get('fieldsets')).filter(fieldset => 
                fieldset.handle != this.excludeFieldset
            )
        ));

        const fieldSuggestions = fieldsets.flatMap(fieldset => {
            return fieldset.fields
                .filter(field => field.type !== 'import')
                .map(field => ({
                    value: `${fieldset.handle}.${field.handle}`,
                    label: field.config.display,
                    fieldset: fieldset.title,
                }));
        });

        return {
            open: false,
            reference: null,
            fieldset: null,
            importPrefix: null,
            fieldSuggestions,
            fieldsetSuggestions: fieldsets.map(fieldset => ({
                value: fieldset.handle,
                label: fieldset.title,
            })),
            fieldsets,
        }
    },

    methods: {

        linkField() {
            const [fieldsetHandle, fieldHandle] = this.reference.split('.');

            const field = this.fieldsets
                .find(fieldset => fieldset.handle === fieldsetHandle).fields
                .find(field => field.handle === fieldHandle);

            field.config.isNew = true;

            this.linkAndClose({
                ...field,
                _id: uniqid(),
                type: 'reference',
                field_reference: this.reference,
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
