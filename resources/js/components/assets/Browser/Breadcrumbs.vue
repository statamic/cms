<template>

    <div class="px-2 pt-1 flex flex-wrap">
        <a
            v-for="(part, index) in pathParts"
            :key="index"
            @click="selectFolder(index)"
            class="mr-1"
        >
            <span class="icon icon-folder mr-0.5 text-blue-lighter hover:text-blue" />
            <span>{{ part }}</span>
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
