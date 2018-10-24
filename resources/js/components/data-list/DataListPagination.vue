<template>
    <ul class="w-full flex justify-center list-reset">
        <li v-if="hasPrevious" class="mx-1">
            <a @click.prevent="selectPreviousPage"><span>&laquo;</span></a>
        </li>

        <!-- Do we want to bring back segments, slider, ellipses, etc.? -->

        <li v-for="page in pages" class="mx-1" :class="{ 'font-bold': page == currentPage }">
            <a @click.prevent="selectPage(page)">{{ page }}</a>
        </li>

        <li v-if="hasNext" class="mx-1">
            <a @click.prevent="selectNextPage"><span>&raquo;</span></a>
        </li>
    </ul>
</template>

<script>
    export default {
        inject: ['sharedState'],

        props: [
            'total',
            'current',
        ],

        computed: {
            totalPages() {
                return this.total || Math.ceil(this.$parent.rows.length / window.Statamic.paginationSize);
            },

            pages() {
                return _.range(1, this.totalPages + 1);
            },

            currentPage() {
                return this.current || this.sharedState.currentPage;
            },

            hasPrevious() {
                return this.currentPage > 1;
            },

            hasNext() {
                return this.currentPage < this.totalPages;
            },
        },

        methods: {
            selectPage(page) {
                this.sharedState.currentPage = page;

                this.$emit('selected', page);
            },

            selectPreviousPage() {
                this.selectPage(this.currentPage - 1);
            },

            selectNextPage() {
                this.selectPage(this.currentPage + 1);
            },
        }
    }
</script>
