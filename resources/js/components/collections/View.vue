<template>

    <div>

        <header class="mb-3">

            <breadcrumb :url="breadcrumbUrl" :title="__('Collections')" />

            <div class="flex items-center">
                <h1 class="flex-1" v-text="title" />

                <dropdown-list class="mr-1">
                    <slot name="twirldown" />
                </dropdown-list>

                <div class="btn-group-flat mr-2" v-if="structured">
                    <button @click="view = 'tree'" :class="{'active': view === 'tree'}">
                        <svg-icon name="structures" class="h-4 w-4"/>
                    </button>
                    <button @click="view = 'list'" :class="{'active': view === 'list'}">
                        <svg-icon name="assets-mode-table" class="h-4 w-4" />
                    </button>
                </div>

                <template v-if="view === 'tree'">

                    <a
                        class="text-2xs text-blue mr-2 underline"
                        v-if="treeIsDirty"
                        v-text="__('Discard changes')"
                        @click="cancelTreeProgress"
                    />

                    <dropdown-list>
                        <template #trigger>
                            <button class="btn" v-text="`${__('Add Link')}`" />
                        </template>
                        <dropdown-item :text="__('Link to URL')" @click="linkTreePage" />
                        <dropdown-item :text="__('Link to Entry')" @click="linkTreeEntries" />
                    </dropdown-list>

                    <button
                        class="btn mx-2"
                        :class="{ 'disabled': !treeIsDirty }"
                        :disabled="!treeIsDirty"
                        @click="saveTree"
                        v-text="__('Save Changes')" />

                </template>

                <create-entry-button
                    button-class="btn-primary"
                    :url="createUrl"
                    :blueprints="blueprints" />
            </div>

        </header>

        <entry-list
            v-show="view === 'list'"
            :collection="handle"
            :initial-sort-column="sortColumn"
            :initial-sort-direction="sortDirection"
            :filters="filters"
            :action-url="actionUrl"
            :reorderable="reorderable"
            :reorder-url="reorderUrl"
        />

        <page-tree
            v-if="structured"
            v-show="view === 'tree'"
            ref="tree"
            :has-collection="true"
            :pages-url="structurePagesUrl"
            :submit-url="structureSubmitUrl"
            :max-depth="structureMaxDepth"
            :expects-root="structureExpectsRoot"
            :site="site"
            @changed="markTreeDirty"
            @saved="markTreeClean"
            @canceled="markTreeClean"
        />

    </div>

</template>

<script>
import ListView from './ListView.vue';
import TreeView from './TreeView.vue';
import PageTree from '../structures/PageTree.vue';

export default {

    components: {
        ListView,
        TreeView,
        PageTree,
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        canCreate: { type: Boolean, required: true },
        createUrl: { type: String, required: true },
        blueprints: { type: Array, required: true },
        breadcrumbUrl: { type: String, required: true },
        structured: { type: Boolean, required: true },
        sortColumn: { type: String, required: true },
        sortDirection: { type: String, required: true },
        filters: { type: Array, required: true },
        actionUrl: { type: String, required: true },
        reorderable: { type: Boolean, required: true },
        reorderUrl: { type: String, required: true },
        blueprints: { type: Array, required: true },
        site: { type: String, required: true },
        structurePagesUrl: { type: String },
        structureSubmitUrl: { type: String },
        structureMaxDepth: { type: Number, default: Infinity },
        structureExpectsRoot: { type: Boolean },
    },

    data() {
        return {
            mounted: false,
            view: this.initialView(),
        }
    },

    computed: {

        treeIsDirty() {
            return this.$dirty.has('page-tree');
        }

    },

    watch: {

        view: {
            immediate: true,
            handler(view) {
                this.$config.set('wrapperClass', view === 'tree' ? undefined : 'max-w-full');

                localStorage.setItem('statamic.collection-view.'+this.handle, view);
            }
        }

    },

    mounted() {
        this.mounted = true;
    },

    methods: {

        cancelTreeProgress() {
            this.$refs.tree.cancel();
        },

        linkTreePage() {
            this.$refs.tree.linkPage();
        },

        linkTreeEntries() {
            this.$refs.tree.linkToEntries();
        },

        saveTree() {
            this.$refs.tree.save();
        },

        markTreeDirty() {
            this.$dirty.add('page-tree');
        },

        markTreeClean() {
            this.$dirty.remove('page-tree');
        },

        initialView() {
            if (!this.structured) return 'list';

            const fallback = this.structured ? 'tree' : 'list';

            return localStorage.getItem('statamic.collection-view.'+this.handle) || fallback;
        }

    }

}
</script>
