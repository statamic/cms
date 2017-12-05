<template>

    <li :class="{ 'active': isCurrent }">
        <a href="" @click.prevent="select">
            {{ number }}
        </a>
    </li>

</template>


<script>
export default {

    props: ['number'],


    computed: {

        /**
         * Determine if this page is the current page.
         *
         * The "current" property should be a prop, but hey, this is a little cleaner.
         */
        isCurrent() {
            return this.number === this.$parent.current;
        }

    },


    methods: {

        /**
         * Select this page.
         *
         * This should probably emit an event and have the Pagination component
         * listen for it, but again, this is cleaner and still works in Vue 1.
         */
        select() {
            this.$parent.select(this.number);
        }

    }

}
</script>
