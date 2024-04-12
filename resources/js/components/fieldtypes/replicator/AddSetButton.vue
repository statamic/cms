<template>

    <div class="replicator-set-picker flex items-center">
        <set-picker :sets="groups" @added="addSet">
            <template #trigger>
                <div class="replicator-set-picker-button-wrapper">
                    <button v-if="enabled" class="btn-round flex items-center justify-center" :class="{ 'h-5 w-5': ! last }" @click="addSetButtonClicked">
                        <svg-icon name="micro/plus"
                            :class="{
                                'w-3 h-3 text-gray-800 group-hover:text-black': last,
                                'w-2 h-2 text-gray-700 group-hover:text-black transition duration-150': !last
                            }" />
                    </button>
                </div>
            </template>
        </set-picker>
        <button v-if="enabled && pasteEnabled" class="btn-round flex items-center justify-center h-5 w-5 ml-1" @click="pasteSet">
            <svg-icon name="micro/arrow-right"
                :class="{
                    'w-2 h-2 text-gray-800 group-hover:text-black': last,
                    'w-2 h-2 text-gray-700 group-hover:text-black transition duration-150': !last
                }" />
        </button>
    </div>

</template>

<script>
import SetPicker from './SetPicker.vue';

export default {

    components: {
        SetPicker,
    },

    props: {
        sets: Array,
        groups: Array,
        index: Number,
        last: Boolean,
        pasteEnabled: Boolean,
        enabled: { type: Boolean, default: true },
    },

    methods: {

        addSet(handle) {
            this.$emit('added', handle, this.index);
        },

        addSetButtonClicked() {
            if (this.sets.length === 1) {
                this.addSet(this.sets[0].handle);
            }
        },

        pasteSet() {
            this.$emit('pasted', this.index);
        },

    }

}
</script>
