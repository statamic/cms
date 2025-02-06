<template>
    <div
        class="flex flex-wrap border-b border-t bg-gray-200 px-4 py-2 text-sm shadow-inner dark:border-dark-900 dark:bg-dark-700"
    >
        <a v-for="(part, index) in pathParts" :key="index" @click="selectFolder(index)" class="group flex items-center">
            <svg-icon
                name="micro/chevron-right"
                class="h-4 w-4 text-gray-700 ltr:mr-1 rtl:ml-1 rtl:rotate-180"
                v-if="index !== 0"
            />
            <svg-icon
                name="folder-home"
                class="h-5 w-5 text-blue-400 group-hover:text-blue-600 ltr:mr-2 rtl:ml-2"
                v-if="part === '/'"
            />
            <svg-icon name="folder" class="h-5 w-5 text-blue-400 group-hover:text-blue-600 ltr:mr-2 rtl:ml-2" v-else />
            <span class="text-2xs text-gray-700 group-hover:text-gray-800" v-if="part !== '/'" v-text="part" />
        </a>
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
    },
};
</script>
