<template>

    <stack narrow name="nav-item-editor" :before-close="shouldClose" @closed="$emit('closed')">
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

                <div class="publish-field mb-4">
                    <p class="text-sm font-medium mb-1" v-text="__('Display')" />
                    <text-input v-model="config.display" />
                </div>

                <div class="publish-field mb-4">
                    <p class="text-sm font-medium mb-1" v-text="__('URL')" />
                    <div class="help-block -mt-1">
                        <p v-text="__('Enter any internal or external URL.')"></p>
                    </div>
                    <text-input v-model="config.url" />
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
        }
    },

    created() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+enter', 'mod+s'], e => {
            e.preventDefault();
            this.save();
        });
    },

    methods: {
        createNewItem() {
            return {
                display: '',
                url: '',
            };
        },

        shouldClose() {
            // if (this.$dirty.has(this.publishContainer)) {
            //     if (! confirm(__('Are you sure? Unsaved changes will be lost.'))) {
            //         return false;
            //     }
            // }

            return true;
        },

        confirmClose(close) {
            if (this.shouldClose()) close();
        },

        save() {
            this.$emit('updated', this.config, this.item);
        },

    },

}
</script>
