<template>
    <set-picker :enabled="enabled" :sets="groups" @added="addSet">
        <template #trigger>
            <div class="flex justify-center pt-3" v-if="variant === 'button'">
                <Button v-if="enabled" @click="addSetButtonClicked" :text="__('Add Block')" icon="plus" />
            </div>
            <Motion
                v-if="variant === 'between'"
                layout
                class="group relative flex justify-center py-3"
                :initial="{ paddingTop: '0.75rem', paddingBottom: '0.75rem' }"
                :hover="{ paddingTop: '1.25rem', paddingBottom: '1.25rem' }"
                :transition="{ duration: 0.2 }"
            >
                <div
                    v-if="showConnector"
                    class="dark:bg-dark-700 absolute inset-y-0 left-3.5 z-0 h-full border-l-1 border-dashed border-gray-400 transition-opacity delay-25 duration-125 group-hover:opacity-0 dark:border-gray-600"
                />
                <button
                    class="absolute inset-0 h-full w-full cursor-pointer opacity-0 transition-opacity delay-25 duration-75 group-hover:opacity-100"
                    @click="addSetButtonClicked"
                >
                    <div class="flex h-full flex-col justify-center">
                        <div class="h-2 rounded-full bg-gray-200" />
                    </div>
                </button>
                <Button
                    v-if="enabled"
                    @click="addSetButtonClicked"
                    round
                    icon="plus"
                    size="sm"
                    class="z-3 -my-4 opacity-0 transition-opacity delay-25 duration-75 group-hover:opacity-100"
                />
            </Motion>
        </template>
    </set-picker>
</template>

<script>
import SetPicker from './SetPicker.vue';
import { Button } from '@statamic/ui';
import { Motion } from 'motion-v';

export default {
    components: {
        SetPicker,
        Button,
        Motion,
    },

    props: {
        sets: Array,
        groups: Array,
        index: Number,
        enabled: { type: Boolean, default: true },
        label: String,
        showConnector: { type: Boolean, default: true },
        variant: { type: String, default: 'button' },
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
