<template>
    <div class="flex flex-wrap">
        <div v-for="(part, index) in pathParts" :key="index" class="flex items-center">
            <ui-icon name="ui/chevron-right" class="size-4 text-gray-500" v-if="index !== 0" />
            <ui-button
                variant="ghost"
                :icon="isHomeFolder(part) ? 'home' : !isLastFolder(index) ? 'folder' : 'folder-open'"
                :text="isHomeFolder(part) ? __('All') : part"
                @click="selectFolder(index)"
                class="h-8! gap-2"
            />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        path: String,
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

    methods: {
        selectFolder(index) {
            const path = index === 0 ? '/' : this.pathParts.slice(1, index + 1).join('/');

            this.$emit('navigated', path);
        },

        isHomeFolder(part) {
            return part === '/';
        },

        isLastFolder(index) {
            return index === this.pathParts.length - 1;
        },
    },
};
</script>
