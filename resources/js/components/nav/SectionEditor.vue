<template>

    <stack narrow name="nav-item-editor" @closed="$emit('closed')">
        <div slot-scope="{ close }" class="bg-white h-full flex flex-col">

            <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                {{ creating ? __('Add Section') : __('Edit Section') }}
                <button
                    type="button"
                    class="btn-close"
                    @click="close"
                    v-html="'&times'" />
            </div>

            <div class="flex-1 overflow-auto p-3">
                <div class="publish-fields publish-fields-narrow">

                <div class="publish-field mb-4" :class="{ 'has-error': validate }">
                    <div class="field-inner">
                        <label class="text-sm font-medium mb-1">{{ __('Display') }} <span class="text-red">*</span></label>
                        <text-input v-model="section" :focus="true" />
                        <div v-if="validate" class="help-block text-red mt-1"><p>{{ __('statamic::validation.required') }}</p></div>
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
