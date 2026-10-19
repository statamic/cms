<template>
    <div class="flex flex-wrap gap-y-4">
        <div v-for="(part, index) in pathParts" :key="index" class="flex items-center">
            <ui-icon name="chevron-right" class="size-4 text-gray-500" v-if="index !== 0" />
            <ui-button
                variant="ghost"
                :icon="isHomeFolder(part) ? 'home' : !isLastFolder(index) ? 'folder' : 'folder-open'"
                :text="isHomeFolder(part) ? __('All') : part"
                @click="selectFolder(index)"
                @dragover="dragOver($event, index)"
                @dragleave="dragLeave($event)"
                @drop="drop($event, index)"
                class="gap-2 h-8!"
                :class="{ 'ring-2 ring-blue-400': dropTarget === index }"
            />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        path: String,
        droppable: Boolean,
    },

    emits: ['navigated', 'dropped'],

    data() {
        return {
            dropTarget: null,
        };
    },

    computed: {
        pathParts() {
            let parts = ['/'];

            if (this.path === '/') {
                return parts;
            }

            return parts.concat(this.path.split('/'));
        },
    },

    watch: {
        droppable(droppable) {
            if (!droppable) this.dropTarget = null;
        },
    },

    methods: {
        pathFor(index) {
            return index === 0 ? '/' : this.pathParts.slice(1, index + 1).join('/');
        },

        selectFolder(index) {
            this.$emit('navigated', this.pathFor(index));
        },

        canDropOn(index) {
            return this.droppable && !this.isLastFolder(index);
        },

        dragOver(event, index) {
            if (!this.canDropOn(index)) return;

            event.preventDefault();
            this.dropTarget = index;
        },

        dragLeave(event) {
            if (event.currentTarget.contains(event.relatedTarget)) return;

            this.dropTarget = null;
        },

        drop(event, index) {
            if (!this.canDropOn(index)) return;

            event.preventDefault();
            this.dropTarget = null;
            this.$emit('dropped', this.pathFor(index));
        },

        isHomeFolder(part) {
            return part === '/';
        },

        isLastFolder(index) {
            return index === this.pathParts.length - 1;
        }
    },
};
</script>
