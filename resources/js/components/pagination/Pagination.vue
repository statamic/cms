<template>

    <ul class="pagination">
        <li v-if="hasPrevious">
            <a href="" @click.prevent="selectPreviousPage"><span>&laquo;</span></a>
        </li>

        <template v-if="segmented">
            <li is="page"
                v-for="item in segments.first"
                :number="item.page"></li>

            <li v-if="showFirstEllipsis" class="disabled"><span>...</span></li>

            <li is="page"
                v-for="item in segments.slider"
                :number="item.page"></li>

            <li v-if="showLastEllipsis" class="disabled"><span>...</span></li>

            <li is="page"
                v-for="item in segments.last"
                :number="item.page"></li>
        </template>

        <li is="page" v-if="!segmented" v-for="n in total" :number="n+1"></li>

        <li v-if="hasNext">
            <a href="" @click.prevent="selectNextPage"><span>&raquo;</span></a>
        </li>
    </ul>

</template>


<script>
export default {

    components: {
        page: require('./Page.vue')
    },


    props: ['total', 'current', 'segments'],


    computed: {

        hasPrevious() {
            return this.current > 1;
        },

        hasNext() {
            return this.current < this.total;
        },

        segmented() {
            return this.segments !== undefined;
        },

        hasSlider() {
            return Boolean(this.segments.slider.length);
        },

        showFirstEllipsis() {
            return this.hasSlider;
        },

        showLastEllipsis() {
            if (this.hasSlider) return true;

            return Boolean(this.segments.last.length);
        }

    },


    methods: {

        select(page) {
            this.$emit('selected', page);
        },

        selectPreviousPage() {
            this.select(this.current - 1);
        },

        selectNextPage() {
            this.select(this.current + 1);
        }

    }

}
</script>
