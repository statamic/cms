<template>
    <div>
        <stack
            name="inline-editor"
            :before-close="shouldClose"
            :narrow="stackSize === 'narrow'"
            :half="stackSize === 'half'"
            :full="stackSize === 'full'"
            @closed="close"
        >
            <div class="h-full overflow-scroll overflow-x-auto bg-gray-300 p-6 dark:bg-dark-800">
                <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
                    <loading-graphic />
                </div>

                <component
                    class="mx-auto max-w-7xl"
                    :is="component"
                    v-if="!loading"
                    v-bind="componentPropValues"
                    :method="method"
                    :is-creating="creating"
                    :is-inline="true"
                    :publish-container="publishContainer"
                    @saved="saved"
                >
                    <template slot="action-buttons-right">
                        <slot name="action-buttons-right" />
                        <button type="button" class="btn-close" @click="confirmClose" v-html="'&times'" />
                    </template>
                </component>
            </div>
        </stack>

        <confirmation-modal
            v-if="closingWithChanges"
            :title="__('Unsaved Changes')"
            :body-text="__('Are you sure? Unsaved changes will be lost.')"
            :button-text="__('Discard Changes')"
            :danger="true"
            @confirm="confirmCloseWithChanges"
            @cancel="closingWithChanges = false"
        />
    </div>
</template>

<script>
export default {
    props: {
        component: String,
        componentProps: Object,
        stackSize: String,
    },

    data() {
        return {
            loading: true,
            readOnly: false,
            componentPropValues: {},
            closingWithChanges: false,
        };
    },

    computed: {
        publishContainer() {
            return `relate-fieldtype-inline-${this.$.uid}`;
        },
    },

    created() {
        this.getItem();
    },

    methods: {
        getItem() {
            this.$axios
                .get(this.itemUrl)
                .then((response) => {
                    for (const prop in this.componentProps) {
                        const value = data_get(response.data, this.componentProps[prop]);
                        this.componentPropValues[prop] = value;
                    }

                    this.loading = false;
                })
                .catch((error) => {
                    if (error.response.status === 500) {
                        this.$toast.error(error.response.data.message);
                        this.close();
                    }

                    if (error.response.status === 403) {
                        this.$toast.error(__('This action is unauthorized.'));
                        this.close();
                    }
                });
        },

        close() {
            this.$emit('closed');
        },

        confirmClose() {
            if (this.shouldClose()) this.close();
        },

        shouldClose() {
            if (this.$dirty.has(this.publishContainer)) {
                this.closingWithChanges = true;
                return false;
            }

            return true;
        },

        confirmCloseWithChanges() {
            this.closingWithChanges = false;
            this.$emit('closed');
        },
    },
};
</script>
