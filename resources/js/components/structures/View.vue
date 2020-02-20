<template>

    <div>

        <header class="mb-3" v-if="mounted">
            <breadcrumb :url="breadcrumbUrl" :title="__('Navigation')" />

            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <dropdown-list class="mr-1">
                    <dropdown-item :text="__('Edit Navigation Config')" :redirect="editUrl" />
                </dropdown-list>

                <a @click="$refs.tree.cancel" class="text-2xs text-blue mr-2 underline" v-if="isDirty" v-text="__('Discard changes')" />

                <dropdown-list>
                    <template #trigger>
                        <button class="btn" v-text="`${__('Add Link')}`" />
                    </template>
                    <dropdown-item :text="__('Link to URL')" @click="$refs.tree.linkPage" />
                    <dropdown-item :text="__('Link to Entry')" @click="$refs.tree.linkToEntries" />
                </dropdown-list>

                <button
                    class="btn-primary ml-2"
                    :class="{ 'disabled': !changed }"
                    :disabled="!changed"
                    @click="$refs.tree.save"
                    v-text="__('Save Changes')" />
            </div>
        </header>

        <page-tree
            ref="tree"
            :collections="collections"
            :has-collection="false"
            :pages-url="pagesUrl"
            :submit-url="submitUrl"
            :max-depth="maxDepth"
            :expects-root="expectsRoot"
            :site="site"
            @changed="changed = true"
            @saved="changed = false"
            @canceled="changed = false"
        />

    </div>

</template>

<script>
import PageTree from './PageTree.vue';

export default {

    components: {
        PageTree
    },

    props: {
        title: { type: String, required: true },
        collections: { type: Array, required: true },
        breadcrumbUrl: { type: String, required: true },
        editUrl: { type: String, required: true },
        pagesUrl: { type: String, required: true },
        submitUrl: { type: String, required: true },
        maxDepth: { type: Number, default: Infinity, },
        expectsRoot: { type: Boolean, required: true },
        site: { type: String, required: true },
    },

    data() {
        return {
            mounted: false,
            changed: false,
        }
    },

    computed: {

        isDirty() {
            return this.$dirty.has('page-tree');
        }

    },

    watch: {

        changed(changed) {
            this.$dirty.state('page-tree', changed);
        }

    },

    mounted() {
        this.mounted = true;
    }

}
</script>
