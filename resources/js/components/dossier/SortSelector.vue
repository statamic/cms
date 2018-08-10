<template>

    <div class="dossier-sort-options">
        <button class="btn btn-default" data-toggle="dropdown">
            <span class="icon icon-list"></span>
            {{ sortLabel }}
        </button>
        <ul class="dropdown-menu">
            <li v-for="column in $parent.columns">
                <a href="" @click.prevent="changeSortColumn(column.field)">
                    {{ column.header }}
                    <span class="icon icon-check float-right mr-0" v-if="sort === column.field"></span>
                </a>
            </li>
            <li class="divider"></li>
            <li><a href="" @click.prevent="changeSortOrder('asc')">
                Ascending
                <span class="icon icon-check float-right mr-0" v-if="sortOrder === 'asc'"></span>
            </a></li>
            <li><a href="" @click.prevent="changeSortOrder('desc')">
                Descending
                <span class="icon icon-check float-right mr-0" v-if="sortOrder === 'desc'"></span>
            </a></li>
        </ul>
    </div>

</template>


<script>
export default {

    computed: {

        sort() {
            return this.$parent.sort;
        },

        sortOrder() {
            return this.$parent.sortOrder;
        },

        sortLabel() {
            return _.find(this.$parent.columns, { field: this.sort }).header;
        }

    },

    methods: {

        changeSortColumn(sort) {
            this.$parent.sortBy(sort, this.sortOrder);
        },

        changeSortOrder(order) {
            this.$parent.sortBy(this.sort, order);
        }

    }

};
</script>
