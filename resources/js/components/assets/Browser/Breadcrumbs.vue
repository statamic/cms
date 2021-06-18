<template>

    <div class="px-2 py-1 flex flex-wrap text-sm bg-grey-10 border-t border-b">
        <a
            v-for="(part, index) in pathParts"
            :key="index"
            @click="selectFolder(index)"
            class="mr-1 group"
        >
            <span v-if="index !== 0" class="px-sm text-grey-70">></span>
            <span class="icon icon-folder text-blue-lighter group-hover:text-blue" />
            <span class="text-grey-70 group-hover:text-grey-80">{{ part }}</span>
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
