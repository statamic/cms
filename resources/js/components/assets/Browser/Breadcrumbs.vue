<template>

    <div class="px-4 py-2 flex flex-wrap text-sm bg-grey-100 border-t border-b">
        <a
            v-for="(part, index) in pathParts"
            :key="index"
            @click="selectFolder(index)"
            class="mr-2 group"
        >
            <span v-if="index !== 0" class="px-1 mr-1 text-grey-700">></span>
            <span class="icon icon-folder mr-1 text-blue-lighter group-hover:text-blue" />
            <span class="text-grey-700 group-hover:text-grey-800">{{ part }}</span>
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
