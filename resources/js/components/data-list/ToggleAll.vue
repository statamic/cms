<template>
    <label for="checkerOfAllBoxes" class="btn btn-sm px-sm flex items-center justify-center -ml-sm relative -left-px cursor-pointer">
        <input type="checkbox" @change="toggle" :checked="anyItemsChecked" id="checkerOfAllBoxes">
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
