<template>
    <set-picker :enabled="enabled" :sets="groups" @added="addSet">
        <template #trigger>
            <div class="flex justify-center pt-3" v-if="variant === 'button'">
                <Button v-if="enabled" @click="addSetButtonClicked" :text="__('Add Block')" icon="plus" />
            </div>
            <Motion
                v-if="variant === 'between'"
                layout
                class="flex justify-center py-3 relative group"
                :initial="{ paddingTop: '0.75rem', paddingBottom: '0.75rem' }"
                :hover="{ paddingTop: '1.25rem', paddingBottom: '1.25rem' }"
                :transition="{ duration: 0.2 }"
            >
                <div v-if="showConnector" class="absolute group-hover:opacity-0 transition-opacity delay-25 duration-125 inset-y-0 h-full left-3.5 border-l-1 border-gray-400 dark:border-gray-600 border-dashed z-0 dark:bg-dark-700" />
                <div class="w-full absolute inset-0 h-full opacity-0 group-hover:opacity-100 transition-opacity delay-25 duration-75">
                    <div class="h-full flex flex-col justify-center">
                        <div class="rounded-full bg-gray-200 h-2" />
                    </div>
                </div>
                <Button v-if="enabled" @click="addSetButtonClicked" round icon="plus" size="sm" class="-my-4 z-3 opacity-0 group-hover:opacity-100 transition-opacity delay-25 duration-75" />
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
