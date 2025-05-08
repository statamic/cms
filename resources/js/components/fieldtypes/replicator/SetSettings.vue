<template>
    <popover
        v-if="fields.length"
        :autoclose="false"
        :clickaway="true"
        :placement="'bottom-end'"
        :offset="[10, 0]"
        :context="{ storeName: storeName }"
    >
        <template #trigger>
            <button
                class="set-settings-button btn-icon ltr:mr-2 rtl:ml-2"
                v-tooltip.top="__('Settings')"
            >
                <svg-icon name="light/settings" class="h-4 w-4" />
            </button>
        </template>
        <div class="publish-fields w-[70vw] max-w-[40rem] @container flex flex-wrap">
            <set-field
                v-for="field in fields"
                :key="field.handle"
                :field="field"
                :meta="meta[field.handle]"
                :value="values[field.handle]"
                :parent-name="parentName"
                :set-index="index"
                :field-path="fieldPath(field)"
                :read-only="isReadOnly"
                @updated="updated(field.handle, $event)"
                @meta-updated="metaUpdated(field.handle, $event)"
            />
        </div>
    </popover>
</template>

<script>
import SetField from './Field.vue';

export default {
    components: { SetField },

    inject: ['storeName'],

    props: {
        fields: {
            type: Array,
            required: true,
        },
        meta: {
            type: Object,
            required: true,
        },
        values: {
            type: Object,
            required: true,
        },
        parentName: {
            type: String,
            required: true,
        },
        index: {
            type: Number,
            required: true,
        },
        fieldPathPrefix: {
            type: String,
            required: true,
        },
        isReadOnly: Boolean,
    },

    methods: {
        fieldPath(field) {
            return `${this.fieldPathPrefix}.${this.index}.${field.handle}`;
        },

        updated(handle, value) {
            this.$emit('updated', handle, value);
        },

        metaUpdated(handle, value) {
            this.$emit('meta-updated', handle, value);
        },
    },
};
</script>
