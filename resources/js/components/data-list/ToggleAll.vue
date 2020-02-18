<template>
    <label for="checkerOfAllBoxes" class="btn btn-sm px-sm py-sm -ml-sm cursor-pointer">
        <input type="checkbox" @change="toggle" :checked="allItemsChecked" id="checkerOfAllBoxes">
    </label>
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
