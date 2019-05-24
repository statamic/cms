<template>

    <div class="flex" :class="{ 'mb-1': isRoot }">
        <div class="page-move w-6" v-if="!root" />

        <div v-if="root" class="page-root">
            <i class="icon icon-home mx-auto opacity-25"></i>
        </div>

        <div class="flex items-center flex-1 p-1 ml-1 text-xs leading-normal">

            <div
                class="flex-1"
                :class="{ 'text-sm font-medium': isTopLevel }"
            >
                <i v-if="isRoot" class="icon icon-home mr-1 opacity-25" />
                <a :href="page.edit_url">{{ page.title }}</a>
            </div>

            <div class="pr-1">
                <dropdown-list>
                    <ul class="dropdown-menu">
                        <li><a @click.prevent="$emit('add-page')">{{ __('Add Page') }}</a></li>
                        <li class="warning"><a href="" @click.prevent="remove">{{ __('Delete') }}</a></li>
                    </ul>
                </dropdown-list>
            </div>

        </div>
    </div>

</template>

<script>
export default {

    props: {
        page: Object,
        depth: Number,
        root: Boolean,
        vm: Object,
        firstPageIsRoot: Boolean,
    },

    computed: {

        isTopLevel() {
            return this.depth === 1;
        },

        isRoot() {
            if (!this.firstPageIsRoot) return false;
            if (!this.isTopLevel) return false;

            const firstNodeId = this.vm.data.parent.children[0].id;
            return this.page.id === firstNodeId;
        }

    },

    methods: {

        remove() {
            let message = 'This will only remove the references (and any children) from the tree. No entries will be deleted.';

            if (! confirm(message)) return;

            const store = this.page._vm.store;
            store.deleteNode(this.page);
            this.$emit('removed', store);
        }

    }

}
</script>
