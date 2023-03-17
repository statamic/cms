<template>
    <label for="checkerOfAllBoxes" class="flex items-center justify-center relative cursor-pointer">
        <input type="checkbox" @change="toggle" :checked="anyItemsChecked" id="checkerOfAllBoxes" class="relative top-0">
    </label>
</template>

<script>
export default {
    inject: ['sharedState'],
    computed: {
        anyItemsChecked() {
            return this.sharedState.selections.length > 0;
        },
    },
    methods: {
        toggle() {
            this.anyItemsChecked ? this.uncheckAllItems() : this.checkMaximumAmountOfItems()
        },

        checkMaximumAmountOfItems() {
            this.sharedState.selections = _.chain(this.sharedState.rows)
                .map(item => item.id)
                .first(this.sharedState.maxSelections ?? Infinity)
                .value()
        },

        uncheckAllItems() {
            this.sharedState.selections = []
        },
    },
}
</script>
