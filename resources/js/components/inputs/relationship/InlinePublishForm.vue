<template>

    <div>
    <stack name="inline-editor"
        :before-close="shouldClose"
        @closed="close"
    >
    <div class="h-full overflow-auto p-4 bg-grey-lighter h-full">

        <div v-if="loading" class="absolute pin z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <entry-publish-form
            v-if="blueprint"
            :action="publishUrl"
            :method="method"
            :publish-container="publishContainer"
            :collection-title="collection.title"
            :collection-url="collection.url"
            :initial-title="title"
            :initial-fieldset="blueprint"
            :initial-values="initialValues"
            :initial-meta="initialMeta"
            @saved="saved"
        >
            <template slot="action-buttons-right">
                <button class="btn ml-1" v-text="__('Cancel')" @click="confirmClose" />
            </template>
        </entry-publish-form>

    </div>
    </stack>
    </div>

</template>

<script>
import axios from 'axios';

export default {

    data() {
        return {
            publishUrl: null,
            loading: true,
            blueprint: null,
            values: null,
            initialValues: null,
            initialMeta: null,
            collection: Object
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
            axios.get(this.itemUrl).then(response => {
                const data = response.data;
                this.blueprint = data.blueprint;
                this.values = this.initialValues = data.values;
                this.initialMeta = data.meta;
                this.publishUrl = data.actions[this.action];
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
