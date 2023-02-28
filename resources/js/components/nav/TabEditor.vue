<template>

    <stack narrow name="nav-item-editor" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-white h-full flex flex-col">

            <div class="bg-gray-200 px-6 py-2 border-b border-gray-300 text-lg font-medium flex items-center justify-between">
                {{ creating ? __('Add Tab') : __('Edit Tab') }}
                <button
                    type="button"
                    class="btn-close"
                    @click="close"
                    v-html="'&times'" />
            </div>

            <div class="flex-1 overflow-auto p-6">
                <div class="publish-fields">

                <div class="publish-field mb-8" :class="{ 'has-error': validate }">
                    <div class="field-inner">
                        <label class="text-sm font-medium mb-2">{{ __('Display') }} <span class="text-red">*</span></label>
                        <text-input v-model="tab" :focus="true" />
                        <div v-if="validate" class="help-block text-red mt-2"><p>{{ __('statamic::validation.required') }}</p></div>
                    </div>
                </div>

                <button
                    class="btn-primary w-full mt-6"
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
        tabItem: {},
    },

    data() {
        return {
            tab: data_get(this.tabItem, 'text') || '',
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

            if (! this.tab) {
                this.validate = true;
                return;
            }

            this.$emit('updated', this.tab, this.tabItem);
        },

    },

}
</script>
