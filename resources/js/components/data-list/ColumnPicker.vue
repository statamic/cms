<template>
    <dropdown-list>
        <button class="btn btn-icon-only antialiased ml-2 dropdown-toggle" slot="trigger">
            <svg-icon name="picker" class="h-4 w-4 mr-1 text-current"></svg-icon>
            <span>{{ __('Columns') }}</span>
        </button>
        <div class="dropdown-menu">
            <h6>{{ __('Customize Columns') }}</h6>
            <div class="li divider mb-2"></div>

            <sortable-list
                v-model="columns"
                :vertical="true"
                item-class="item"
                handle-class="item-move"
            >
                <div>
                    <div class="item mb-1 text-sm column" v-for="column in sharedState.columns" :key="column.field">
                        <div class="item-move">&nbsp;</div>
                        <div class="item-inner">
                            <label><input type="checkbox" v-model="column.visible" /> {{ column.label }}</label>
                        </div>
                    </div>
                </div>
            </sortable-list>

            <div class="mt-2">
                <loading-graphic v-if="saving" :inline="true" :text="__('Saving')" />
                <button v-else class="btn btn-primary btn-sm" @click="save">Save</button>
            </div>
        </div>
    </dropdown-list>
</template>

<script>
import axios from 'axios';
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
            axios.post(this.saveUrl, { columns: this.selectedColumns }).then(response => {
                this.saving = false;
                this.$notify.success(__('Columns saved'), { timeout: 3000 });
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
