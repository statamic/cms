<template>

    <div>
    <stack name="inline-editor"
        :before-close="shouldClose"
        @closed="close"
    >
    <div class="h-full overflow-auto p-3 bg-grey-30">

        <div v-if="loading" class="absolute pin z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <entry-publish-form
            v-if="blueprint"
            :is-creating="creating"
            :initial-actions="initialActions"
            :method="method"
            :publish-container="publishContainer"
            :collection-title="collection.title"
            :collection-url="collection.url"
            :initial-title="title"
            :initial-fieldset="blueprint"
            :initial-values="initialValues"
            :initial-meta="initialMeta"
            :initial-localizations="initialLocalizations"
            :initial-read-only="readOnly"
            @saved="saved"
        >
            <template slot="action-buttons-right">
                <slot name="action-buttons-right" />
                <button
                    type="button"
                    class="btn-close"
                    @click="confirmClose"
                    v-html="'&times'" />
            </template>
        </entry-publish-form>

    </div>
    </stack>
    </div>

</template>

<script>
export default {

    data() {
        return {
            loading: true,
            blueprint: null,
            values: null,
            initialValues: null,
            initialMeta: null,
            initialLocalizations: null,
            initialActions: null,
            collection: Object,
            readOnly: false,
        }
    },

    computed: {

        publishContainer() {
            return `relate-fieldtype-inline-${this._uid}`;
        }

    },

    created() {
        this.getItem();
    },

    methods: {

        getItem() {
            this.$axios.get(this.itemUrl).then(response => {
                const data = response.data;
                this.blueprint = data.blueprint;
                this.values = this.initialValues = data.values;
                this.initialMeta = data.meta;
                this.initialLocalizations = data.localizations;
                this.initialActions = data.actions;
                this.collection = data.collection;
                this.loading = false;
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
                if (! confirm('Are you sure? Unsaved changes will be lost.')) {
                    return false;
                }
            }

            return true;
        }
    }

}
</script>
