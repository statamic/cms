<template>

    <div class="breadcrumbs">
        <a href=""
           v-for="(i, part) in pathParts"
           @click.prevent="selectFolder(i)">
            <span class="icon icon-folder"></span>
            {{ part }}
        </a>
    </div>

</template>


<script>
export default {

    props: ['path'],


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

        selectFolder(part) {
            const path = (part === 0)
                ? '/'
                : this.pathParts.slice(1, part + 1).join('/');

            this.$emit('navigated', path);
        }

    }

}
</script>
