<template>
    <div :class="{ 'mb-4': rows.length > 0 }">
        <sortable-list
            :model-value="rows"
            :vertical="true"
            :item-class="sortableItemClass"
            :handle-class="sortableHandleClass"
            append-to="body"
            constrain-dimensions
            @dragstart="$emit('focus')"
            @dragend="$emit('blur')"
            @update:model-value="(rows) => $emit('sorted', rows)"
            v-slot="{}"
        >
            <div
                class="grid-stacked space-y-8"
                :class="{
                    // 'mt-0': !allowFullscreen && hideDisplay,
                    // 'mt-4': !hideDisplay,
                }"
            >
                <stacked-row
                    v-for="(row, index) in rows"
                    :key="`row-${row._id}`"
                    :index="index"
                    :fields="fields"
                    :values="row"
                    :meta="meta[row._id]"
                    :name="name"
                    :has-error="rowHasError(row._id)"
                    :field-path-prefix="fieldPathPrefix"
                    :meta-path-prefix="metaPathPrefix"
                    :can-delete="canDeleteRows"
                    :can-add-rows="canAddRows"
                    @updated="(row, value) => $emit('updated', row, value)"
                    @duplicate="(row) => $emit('duplicate', row)"
                    @meta-updated="$emit('meta-updated', row._id, $event)"
                    @removed="(row) => $emit('removed', row)"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')"
                />
            </div>
        </sortable-list>
    </div>
</template>

<script>
import View from './View.vue';
import StackedRow from './StackedRow.vue';
import { SortableList } from '../../sortable/Sortable';

export default {
    mixins: [View],

    components: {
        StackedRow,
        SortableList,
    },

    props: {
        errors: Object,
    },

    data() {
        return {
            errorsById: {},
        };
    },

    watch: {
        errors: {
            immediate: true,
            handler(errors) {
                this.errorsById = Object.entries(errors).reduce((acc, [key, value]) => {
                    if (!key.startsWith(this.fieldPathPrefix)) {
                        return acc;
                    }

                    const subKey = key.replace(`${this.fieldPathPrefix}.`, '');
                    const rowIndex = subKey.split('.').shift();
                    const rowId = this.rows[rowIndex]?._id;

                    if (rowId) {
                        acc[rowId] = value;
                    }

                    return acc;
                }, {});
            },
        },
    },


    methods: {
        rowHasError(id) {
            if (Object.keys(this.errorsById).length === 0) {
                return false;
            }

            return this.errorsById.hasOwnProperty(id) && this.errorsById[id].length > 0;
        }
    },
};
</script>
