<template>

    <div class="px-4 py-2 flex flex-wrap text-sm bg-gray-200 border-t border-b shadow-inner">
        <a
            v-for="(part, index) in pathParts"
            :key="index"
            @click="selectFolder(index)"
            class="group flex items-center"
        >
            <svg-icon name="micro/chevron-right" class="text-gray-700 h-4 w-4 mr-1" v-if="index !== 0" />
            <svg-icon name="folder-home" class="mr-2 h-5 w-5 text-blue-400 group-hover:text-blue-600" v-if="part === '/'" />
            <svg-icon name="folder" class="mr-2 h-5 w-5 text-blue-400 group-hover:text-blue-600" v-else />
            <span class="text-gray-700 text-2xs group-hover:text-gray-800" v-if="part !== '/'" v-text="part" />
        </a>
    </div>

</template>

<script>
export default {

    props: {
        path: String
    },

    computed: {

        pathParts() {
            let parts = ['/'];

            if (this.path === '/') {
                return parts;
            }

            return parts.concat(this.path.split('/'));
        }

    },

    methods: {

        selectFolder(index) {
            const path = (index === 0)
                ? '/'
                : this.pathParts.slice(1, index + 1).join('/');

            this.$emit('navigated', path);
        }

    }

}
</script>
