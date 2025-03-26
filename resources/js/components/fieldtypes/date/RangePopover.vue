<template>
    <div class="w-full">
        <v-date-picker ref="picker" v-bind="pickerBindings" @update:model-value="dateSelected" :is-dark="darkMode">
            <template #default="{ inputValue, inputEvents }">
                <div class="flex w-full flex-col items-start @md:flex-row @md:items-center">
                    <div class="input-group">
                        <div class="input-group-prepend flex items-center">
                            <svg-icon name="light/calendar" class="h-4 w-4" />
                        </div>
                        <div
                            class="input-text flex items-center border border-gray-500 dark:border-dark-400 ltr:border-l-0 ltr:pr-0 rtl:border-r-0 rtl:pl-0"
                            :class="{ 'read-only': isReadOnly }"
                        >
                            <input
                                class="input-text-minimal bg-transparent p-0 leading-none"
                                :readonly="isReadOnly"
                                :value="inputValue.start"
                                v-on="inputEvents.start"
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

                    <svg-icon name="micro/arrow-right" class="mx-2 my-1 hidden h-6 w-6 text-gray-700 @md:block" />
                    <svg-icon
                        name="micro/arrow-right"
                        class="mx-2.5 my-2 h-3.5 w-3.5 rotate-90 text-gray-700 @md:hidden"
                    />

                    <div class="input-group">
                        <div class="input-group-prepend flex items-center">
                            <svg-icon name="light/calendar" class="h-4 w-4" />
                        </div>
                        <div
                            class="input-text border border-gray-500 dark:border-dark-400 ltr:border-l-0 rtl:border-r-0"
                            :class="{ 'read-only': isReadOnly }"
                        >
                            <input
                                class="input-text-minimal bg-transparent p-0 leading-none"
                                :readonly="isReadOnly"
                                :value="inputValue.end"
                                v-on="inputEvents.end"
                                @focus="$emit('focus', $event.target)"
                                @blur="$emit('blur-sm')"
                            />
                        </div>
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

        pickerBindings() {
            return {
                ...this.bindings,
                disabledDates: this.isReadOnly ? { weekdays: [1, 2, 3, 4, 5, 6, 7] } : null,
            };
        },
    },

    methods: {
        dateSelected(date) {
            this.$emit('update:model-value', date);
        },

        clear() {
            this.$emit('update:model-value', null);
        },
    },
};
</script>
