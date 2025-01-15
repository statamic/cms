<template>

    <stack narrow name="nav-item-editor" @closed="$emit('closed')" v-slot="{ close }">
        <div class="bg-white dark:bg-dark-800 h-full flex flex-col">

            <div class="bg-gray-200 dark:bg-dark-600 px-6 py-2 border-b border-gray-300 dark:border-dark-900 text-lg font-medium flex items-center justify-between">
                {{ creating ? __('Add Section') : __('Edit Section') }}
                <button
                    type="button"
                    class="btn-close"
                    @click="close"
                    v-html="'&times'" />
            </div>

            <div class="flex-1 overflow-auto">
                <div class="px-2">
                    <div class="publish-fields @container">

                        <div class="form-group publish-field w-full" :class="{ 'has-error': validate }">
                            <div class="field-inner">
                                <label class="text-sm font-medium mb-2">{{ __('Display') }} <span class="text-red-500">*</span></label>
                                <text-input v-model="section" :focus="true" />
                                <div v-if="validate" class="help-block text-red-500 mt-2"><p>{{ __('statamic::validation.required') }}</p></div>
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
        sectionItem: {},
    },

    data() {
        return {
            section: data_get(this.sectionItem, 'text') || '',
            saveKeyBinding: null,
            validate: false,
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

        save() {
            this.validate = false;

            if (! this.section) {
                this.validate = true;
                return;
            }

            this.$emit('updated', this.section, this.sectionItem);
        },

    },

}
</script>
