<template>

    <div class="h-full overflow-auto p-4 bg-grey-lighter h-full">

        <div v-if="loading" class="absolute pin z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <entry-publish-form
            v-if="blueprint"
            :action="action"
            method="patch"
            :publish-container="publishContainer"
            :collection-title="collection.title"
            :collection-url="collection.url"
            :initial-title="item.title"
            :initial-fieldset="blueprint"
            :initial-values="initialValues"
            :initial-meta="initialMeta"
            @saved="saved"
        >
            <template slot="action-buttons-right">
                <button class="btn ml-1" v-text="__('Cancel')" @click="close" />
            </template>
        </entry-publish-form>

    </div>

</template>

<script>
import axios from 'axios';

export default {

    props: {
        item: Object
    },

    data() {
        return {
            action: null,
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
            axios.get(this.item.edit_url).then(response => {
                const data = response.data;
                this.blueprint = data.blueprint;
                this.values = this.initialValues = data.values;
                this.initialMeta = data.meta;
                this.action = data.actions.update;
                this.collection = data.collection;
                this.loading = false;
            });
        },

        saved(response) {
            this.$emit('updated', response.data);
            this.$nextTick(() => this.close());
        },

        close() {
            if (this.$dirty.has(this.publishContainer)) {
                if (! confirm('Are you sure? Unsaved changes will be lost.')) {
                    return;
                }
            }

            this.$emit('closed');
        }
    }

}
</script>
