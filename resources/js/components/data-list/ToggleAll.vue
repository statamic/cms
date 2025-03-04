<template>
    <label for="checkerOfAllBoxes" class="relative flex cursor-pointer items-center justify-center">
        <input
            type="checkbox"
            @change="toggle"
            :checked="anyItemsChecked"
            id="checkerOfAllBoxes"
            class="relative top-0"
        />
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
            this.anyItemsChecked ? this.uncheckAllItems() : this.checkMaximumAmountOfItems();
        },

        checkMaximumAmountOfItems() {
            let selections = this.sharedState.rows.map((row) => row.id);
            if (this.sharedState.maxSelections) selections = selections.slice(0, this.sharedState.maxSelections);
            this.sharedState.selections = selections;
        },

        uncheckAllItems() {
            this.sharedState.selections = [];
        },
    },
};
</script>
