<template>
    <input
        type="search"
        :placeholder="translate('cp.search')"
        @keydown.esc.prevent="reset"
        v-model="keyword"
        class="filter-control search"
        debounce="500"
    />
</template>

<script>
export default {
    props: ['keyword'],
    methods: {
        reset: function() {
            this.keyword = '';
        }
    }
};
</script>
