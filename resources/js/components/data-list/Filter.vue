<template>
    <div class="no-label w-full">

        <ui-publish-container
            :model-value="containerValues"
            @update:model-value="updateValues"
            :meta="filter.meta"
            :track-dirty-state="false"
        >
            <ui-publish-fields-provider :fields="filter.fields">
                <ui-publish-fields />
            </ui-publish-fields-provider>
        </ui-publish-container>

        <div class="flex border-t dark:border-dark-900">
            <button
                class="flex-1 p-2 text-xs hover:bg-gray-100 dark:hover:bg-dark-600 ltr:rounded-bl rtl:rounded-br"
                v-text="__('Clear')"
                @click="resetAll"
            />
            <button
                class="flex-1 p-2 text-xs hover:bg-gray-100 dark:border-dark-900 dark:hover:bg-dark-600 ltr:rounded-br ltr:border-l rtl:rounded-bl rtl:border-r"
                v-text="__('Close')"
                @click="$emit('closed')"
            />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        filter: Object,
        values: Object,
    },

    computed: {
        defaultValues() {
            return this.filter.values || {};
        },

        containerValues() {
            return clone(this.values || this.defaultValues);
        },
    },

    methods: {
        updateValues(values) {
            let filteredValues = clone(values);

            Object.keys(values).forEach((key) => {
                if (values[key] === null || values[key] === undefined) delete filteredValues[key];
            });

            this.$emit('changed', filteredValues);
        },

        resetAll() {
            this.$emit('changed', null);
            this.$emit('cleared');
        },

        updateMeta(value) {
            this.meta = value;
        },

        close() {
            this.$emit('closed');
        },
    },
};
</script>
