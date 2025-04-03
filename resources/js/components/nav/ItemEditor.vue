<template>

    <stack narrow name="nav-item-editor" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-white dark:bg-dark-800 h-full flex flex-col">

            <div class="bg-gray-200 dark:bg-dark-600 px-6 py-2 border-b border-gray-300 dark:border-dark-900 text-lg font-medium flex items-center justify-between">
                {{ creating ? __('Add Nav Item') : __('Edit Nav Item') }}
                <button
                    type="button"
                    class="btn-close"
                    @click="close"
                    v-html="'&times'" />
            </div>

            <div class="flex-1 overflow-auto">
                <div class="px-2">
                    <div class="publish-fields @container">

                        <div class="form-group publish-field w-full" :class="{ 'has-error': validateDisplay }">
                            <div class="field-inner">
                                <label class="text-sm font-medium mb-2">{{ __('Display') }} <span class="text-red-500">*</span></label>
                                <text-input v-model="config.display" :focus="true" />
                                <div v-if="validateDisplay" class="help-block text-red-500 mt-2"><p>{{ __('statamic::validation.required') }}</p></div>
                            </div>
                        </div>

                        <div class="form-group publish-field w-full" :class="{ 'has-error': validateUrl }">
                            <div class="field-inner">
                                <label class="text-sm font-medium mb-2">{{ __('URL') }} <span class="text-red-500">*</span></label>
                                <div class="help-block -mt-2">
                                    <p v-text="__('Enter any internal or external URL.')"></p>
                                </div>
                                <text-input v-model="config.url" />
                                <div v-if="validateUrl" class="help-block text-red-500 mt-2"><p>{{ __('statamic::validation.required') }}</p></div>
                            </div>
                        </div>

                        <div class="form-group publish-field w-full" v-if="! isChild">
                            <div class="field-inner">
                                <label class="text-sm font-medium mb-2">{{ __('Icon') }}</label>
                                <publish-field-meta
                                    :config="{ handle: 'icon', type: 'icon', folder: 'light' }"
                                    :initial-value="config.icon"
                                    v-slot="{ meta, value, loading, config }"
                                >
                                    <icon-fieldtype v-if="!loading" handle="icon" :config="config" :meta="meta" :value="value" @input="iconUpdated" />
                                </publish-field-meta>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="p-6">
                    <button
                        class="btn-primary w-full"
                        :class="{ 'opacity-50': false }"
                        :disabled="false"
                        @click="save"
                        v-text="__('Save')" />
                </div>
            </div>

        </div>
    </stack>

</template>

<script>
import { data_get } from  '../../bootstrap/globals.js'

export default {

    props: {
        creating: false,
        item: {},
        isChild: false,
    },

    data() {
        return {
            config: clone(data_get(this.item, 'config', this.createNewItem())),
            saveKeyBinding: null,
            validateDisplay: false,
            validateUrl: false,
        }
    },

    created() {
        this.saveKeyBinding = this.$keys.bindGlobal(['enter', 'mod+enter', 'mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    },

    destroyed() {
        this.saveKeyBinding.destroy();
    },

    methods: {

        createNewItem() {
            return {
                display: '',
                url: '',
                icon: null,
            };
        },

        save() {
            this.validateDisplay = false;
            this.validateUrl = false;

            if (! this.config.display) {
                this.validateDisplay = true;
            }

            if (! this.config.url) {
                this.validateUrl = true;
            }

            if (this.validateDisplay || this.validateUrl) {
                return;
            }

            let config = clone(this.config);

            if (this.isChild) {
                config.icon = null;
            } else if (! config.icon) {
                config.icon = data_get(this.item, 'original.icon');
            }

            this.$emit('updated', this.config, this.item);
        },

        iconUpdated(icon) {
            this.config.icon = icon;
        },

    },

}
</script>
