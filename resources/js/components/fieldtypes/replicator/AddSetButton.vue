<template>
    <div class="replicator-set-picker">
        <set-picker :enabled="enabled" :sets="groups" @added="addSet">
            <template #trigger>
                <div class="replicator-set-picker-button-wrapper flex items-center">
                    <button
                        v-if="enabled"
                        class="btn-round flex items-center justify-center"
                        :class="{
                            'h-5 w-5': !last,
                            'mr-2': label?.length > 0,
                        }"
                        @click.stop="addSetButtonClicked"
                    >
                        <svg-icon
                            name="micro/plus"
                            :class="{
                                'dark:group-hover:dark-text-100 h-3 w-3 text-gray-800 group-hover:text-black dark:text-dark-175':
                                    last,
                                'dark:group-hover:dark-text-100 h-2 w-2 text-gray-700 transition duration-150 group-hover:text-black dark:text-dark-200':
                                    !last,
                            }"
                        />
                    </button>
                    <span @click.stop="addSetButtonClicked" class="cursor-pointer text-sm dark:text-dark-175">{{
                        __(label)
                    }}</span>
                </div>
            </template>
        </set-picker>
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
        enabled: { type: Boolean, default: true },
        label: String,
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
    },
};
</script>
