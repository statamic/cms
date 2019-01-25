<template>
    <ul v-if="hasMultiplePages" class="w-full flex justify-center list-reset">
        <li v-if="hasPrevious" class="mx-1">
            <a @click.prevent="selectPreviousPage"><span>&laquo;</span></a>
        </li>

        <!-- Do we want to bring back segments, slider, ellipses, etc.? -->

        <li v-for="page in pages" :key="page" class="mx-1" :class="{ 'font-bold': page == currentPage }">
            <a @click.prevent="selectPage(page)">{{ page }}</a>
        </li>

        <li v-if="hasNext" class="mx-1">
            <a @click.prevent="selectNextPage"><span>&raquo;</span></a>
        </li>
    </ul>
</template>

<script>
    export default {
        props: {
            resourceMeta: {
                type: Object,
                required: true
            }
        },

        computed: {
            totalPages() {
                return this.resourceMeta.last_page;
            },

            pages() {
                return _.range(1, this.totalPages + 1);
            },

            currentPage() {
                return this.resourceMeta.current_page;
            },

            hasMultiplePages() {
                return this.totalPages > 1;
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
                if (page === this.currentPage) {
                    return;
                }

                this.$emit('page-selected', page);

                window.scrollTo(0, 0);
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
