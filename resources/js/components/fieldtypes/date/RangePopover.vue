<template>

    <div class="w-full">

        <v-date-picker
                ref="picker"
                v-bind="pickerBindings"
                @update:model-value="dateSelected"
                :is-dark="darkMode"
        >
        <template #default="{ inputValue, inputEvents }">

        <div
            class="w-full flex items-start @md:items-center flex-col @md:flex-row"
        >
                    <div class="input-group">
                        <div class="input-group-prepend flex items-center">
                            <svg-icon name="light/calendar" class="w-4 h-4" />
                        </div>
                        <div class="input-text border border-gray-500 dark:border-dark-400 rtl:border-r-0 ltr:border-l-0 flex items-center rtl:pl-0 ltr:pr-0" :class="{ 'read-only': isReadOnly }">
                            <input
                                class="input-text-minimal p-0 bg-transparent leading-none"
                                :readonly="isReadOnly"
                                :value="inputValue.start"
                                v-on="inputEvents.start"
                                @focus="$emit('focus', $event.target)"
                                @blur="$emit('blur')"
                            />
                            <button v-if="!isReadOnly" @click="clear" type="button" title="Clear" aria-label="Clear" class="cursor-pointer px-2 hover:text-blue-500">
                                <span>×</span>
                            </button>
                        </div>
                    </div>

            <svg-icon name="micro/arrow-right" class="w-6 h-6 my-1 mx-2 text-gray-700 hidden @md:block" />
            <svg-icon name="micro/arrow-right" class="w-3.5 h-3.5 my-2 mx-2.5 rotate-90 text-gray-700 @md:hidden" />

                    <div class="input-group">
                        <div class="input-group-prepend flex items-center">
                            <svg-icon name="light/calendar" class="w-4 h-4" />
                        </div>
                        <div class="input-text border border-gray-500 dark:border-dark-400 rtl:border-r-0 ltr:border-l-0" :class="{ 'read-only': isReadOnly }">
                            <input
                                class="input-text-minimal p-0 bg-transparent leading-none"
                                :readonly="isReadOnly"
                                :value="inputValue.end"
                                v-on="inputEvents.end"
                                @focus="$emit('focus', $event.target)"
                                @blur="$emit('blur')"
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
    emits: ['update:model-value', 'focus', 'blur'],

    mixins: [Picker],

    computed: {

        darkMode() {
            return Statamic.darkMode;
        },

        pickerBindings() {
            return {
                ...this.bindings,
                disabledDates: this.isReadOnly ? { weekdays: [1, 2, 3, 4, 5, 6, 7] } : null
            }
        },

    },

    methods: {

        dateSelected(date) {
            this.$emit('update:model-value', date)
        },

        clear() {
            this.$emit('update:model-value', null)
        },

    }

}
</script>
