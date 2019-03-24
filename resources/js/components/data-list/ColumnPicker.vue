<template>
    <div>
        <button class="btn btn-flat btn-icon-only ml-2 dropdown-toggle" @click="customizing = !customizing">
            <svg-icon name="settings-vertical" class="w-4 h-4 mr-1" />
            <span>{{ __('Columns') }}</span>
        </button>

        <pane name="columns" v-if="customizing">
            <div>

                <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                    {{ __('Columns') }}
                    <button
                        type="button"
                        class="ml-2 p-1 text-xl text-grey-60"
                        @click="customizing = false"
                        v-html="'&times'" />
                </div>

                <div class="p-2">

                    <sortable-list
                        v-model="columns"
                        :vertical="true"
                        item-class="column-picker-item"
                        handle-class="column-picker-item"
                    >
                        <div>
                            <div class="column-picker-item column" v-for="column in sharedState.columns" :key="column.field">
                                <label><input type="checkbox" v-model="column.visible" /> {{ column.label }}</label>
                            </div>
                        </div>
                    </sortable-list>

                    <div class="flex justify-center mt-3">
                        <loading-graphic v-if="saving" :inline="true" :text="__('Saving')" />
                        <button v-else class="btn-flat w-full block btn-sm" @click="save">Save</button>
                    </div>

                </div>
            </div>
        </pane>
    </div>
</template>

<script>
import { SortableList } from '../sortable/Sortable';

export default {

    components: {
        SortableList
    },

    props: {
        saveUrl: String,
    },

    data() {
        return {
            customizing: false,
            saving: false,
        }
    },

    computed: {

        columns: {
            get() {
                return this.sharedState.columns;
            },
            set(columns) {
                this.sharedState.columns = columns;
            }
        },

        selectedColumns() {
            return this.sharedState.columns
                .filter(column => column.visible)
                .map(column => column.field);
        }

    },

    inject: ['sharedState'],

    methods: {

        save() {
            this.saving = true;
            this.$axios.post(this.saveUrl, { columns: this.selectedColumns }).then(response => {
                this.saving = false;
                this.$notify.success(__('Columns saved'));
                this.customizing = false;
            }).catch(e => {
                this.saving = false;
                if (e.response) {
                    this.$notify.error(e.response.data.message);
                } else {
                    this.$notify.error(__('Something went wrong'));
                }
            });
        }

    }
}
</script>
