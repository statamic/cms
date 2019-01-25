<template>
    <div class="px-2 w-4">
        <input type="checkbox" @change="toggle" :checked="allItemsChecked">
    </div>
</template>

<script>
export default {
    inject: ['sharedState'],
    computed: {
        allItemsChecked() {
            if (this.sharedState.rows.length === 0) return false;

            return this.sharedState.selections.length === this.sharedState.rows.length;
        },
    },
    methods: {
        toggle() {
            this.allItemsChecked ? this.uncheckAllItems() : this.checkAllItems()
        },

        checkAllItems() {
            this.sharedState.selections = _.values(_.map(this.sharedState.rows, item => item.id))
        },

        uncheckAllItems() {
            this.sharedState.selections = []
        },
    },
}
</script>
