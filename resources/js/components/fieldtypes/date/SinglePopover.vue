<template>
    <div class="flex-1">
        <v-date-picker ref="picker" v-bind="bindings" @update:model-value="dateSelected" :is-dark="darkMode">
            <template #default="{ inputValue, inputEvents }">
                <div class="input-group">
                    <div class="input-group-prepend flex items-center">
                        <svg-icon name="light/calendar" class="h-4 w-4" />
                    </div>
                    <div
                        class="input-text flex items-center border border-gray-500 dark:border-dark-900 ltr:border-l-0 ltr:pr-0 rtl:border-r-0 rtl:pl-0"
                        :class="{ 'read-only': isReadOnly }"
                    >
                        <input
                            class="input-text-minimal bg-transparent p-0 leading-none"
                            :readonly="isReadOnly"
                            :value="inputValue"
                            v-on="inputEvents"
                            @focus="$emit('focus', $event.target)"
                            @blur="$emit('blur-sm')"
                        />
                        <button
                            v-if="!isReadOnly"
                            @click="clear"
                            type="button"
                            title="Clear"
                            aria-label="Clear"
                            class="cursor-pointer px-2 hover:text-blue-500"
                        >
                            <span>×</span>
                        </button>
                    </div>
                </div>
            </template>
        </v-date-picker>
    </div>
</template>

<script>
import Picker from './Picker';

export default {
    emits: ['update:model-value', 'focus', 'blur-sm'],

    mixins: [Picker],

    computed: {
        darkMode() {
            return Statamic.darkMode;
        },
    },
    methods: {
        dateSelected(date) {
            this.$emit('update:model-value', date);
            this.$nextTick(() => this.$refs.popover?.close());
        },

        clear() {
            this.$emit('update:model-value', null);
        },
    },
};
</script>
