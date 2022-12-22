<template>

    <stack narrow name="nav-item-editor" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-white h-full flex flex-col">

            <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                {{ creating ? __('Add Nav Item') : __('Edit Nav Item') }}
                <button
                    type="button"
                    class="btn-close"
                    @click="close"
                    v-html="'&times'" />
            </div>

            <div class="flex-1 overflow-auto p-3">
                <div class="publish-fields publish-fields-narrow">

                <div class="publish-field mb-4" :class="{ 'has-error': validateDisplay }">
                    <div class="field-inner">
                        <label class="text-sm font-medium mb-1">{{ __('Display') }} <span class="text-red">*</span></label>
                        <text-input v-model="config.display" :focus="true" />
                        <div v-if="validateDisplay" class="help-block text-red mt-1"><p>{{ __('statamic::validation.required') }}</p></div>
                    </div>
                </div>

                <div class="publish-field mb-4" :class="{ 'has-error': validateUrl }">
                    <div class="field-inner">
                        <label class="text-sm font-medium mb-1">{{ __('URL') }} <span class="text-red">*</span></label>
                        <div class="help-block -mt-1">
                            <p v-text="__('Enter any internal or external URL.')"></p>
                        </div>
                        <text-input v-model="config.url" />
                        <div v-if="validateUrl" class="help-block text-red mt-1"><p>{{ __('statamic::validation.required') }}</p></div>
                    </div>
                </div>

                <button
                    class="btn-primary w-full mt-3"
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
    },

    data() {
        return {
            config: data_get(this.item, 'config', this.createNewItem()),
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

            this.$emit('updated', this.config, this.item);
        },

    },

}
</script>
