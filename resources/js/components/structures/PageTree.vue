<template>
    <div class="page-tree">

        <div class="flex mb-3">
            <slot name="header" />

            <a :href="editUrl" class="btn mr-1" v-text="__('Edit')" />

            <button
                class="btn btn-primary"
                :class="{ 'disabled': !changed }"
                :disabled="!changed"
                @click="save"
                v-text="__('Save Page Order')" />
        </div>

        <loading-graphic v-if="loading"></loading-graphic>

        <div class="tree-node-inner mb-1">
            <tree-branch :root="true" :page="root" :depth="1" />
        </div>

        <draggable-tree
            draggable
            :data="treeData"
            :space="1"
            :indent="24"
            @change="treeChanged"
        >
            <tree-branch
                slot-scope="{ data: page, store, vm }"
                :page="page"
                :depth="vm.level"
            />
        </draggable-tree>

        <audio ref="soundDrop">
            <source :src="soundDropUrl" type="audio/mp3">
        </audio>

    </div>
</template>


<script>
import axios from 'axios';
import {Sortable, Plugins} from '@shopify/draggable';
import {DraggableTree} from 'vue-draggable-nested-tree';
import TreeBranch from './Branch.vue';

export default {

    components: {
        DraggableTree,
        TreeBranch
    },

    props: {
        initialPages: Array,
        pagesUrl: String,
        submitUrl: String,
        editUrl: String,
        soundDropUrl: String,
        root: Object
    },

    data() {
        return {
            loading: false,
            saving: false,
            changed: false,
            pages: this.initialPages,
            treeData: JSON.parse(JSON.stringify(this.initialPages)),
        }
    },

    watch: {

        changed(changed) {
            this.$dirty.state('page-tree', changed);
        }

    },

    methods: {

        getPages() {
            this.loading = true;
            const url = this.pagesUrl;

            axios.get(url).then(response => {
                this.pages = response.data.pages;
                this.treeData = JSON.parse(JSON.stringify(this.pages));
                this.loading = false;
            });
        },

        treeChanged(node, tree) {
            this.pages = tree.getPureData();
            this.$refs.soundDrop.play();
            this.changed = true;
        },

        save() {
            this.saving = true;

            axios.post(this.submitUrl, { pages: this.pages }).then(response => {
                this.changed = false;
                this.saving = false;
                this.$notify.success(__('Pages reordered.'), { timeout: 3000 });
            });
        }

    }

}
</script>
