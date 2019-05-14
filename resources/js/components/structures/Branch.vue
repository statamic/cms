<template>

    <div class="flex">
        <div class="page-move w-6" v-if="!root" />

        <div v-if="root" class="page-root">
            <i class="icon icon-home mx-auto opacity-25"></i>
        </div>

        <div class="flex items-center flex-1 p-1 ml-1 text-xs leading-normal">

            <div
                class="flex-1"
                :class="{ 'text-sm font-medium': isTopLevel }"
            >
                <a :href="page.edit_url">{{ page.title }}</a>
            </div>

            <div class="pr-1">
                <dropdown-list>
                    <ul class="dropdown-menu">
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
    },

    computed: {

        isTopLevel() {
            return this.depth === 1;
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
