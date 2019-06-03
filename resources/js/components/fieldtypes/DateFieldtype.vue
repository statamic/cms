<template>
    <v-date-picker
        v-model="date"
        :attributes="attrs"
        :formats="formats"
        :mode="config.mode"
        :input="value"
        @input="handleUpdate"
        is-inline />
</template>

<script>

export default {

    mixins: [Fieldtype],

    data() {
        return {
            date: this.value,
            formats: {
                title: 'MMMM YYYY',
                weekdays: 'W',
                navMonths: 'MMM',
                input: ['L', 'YYYY-MM-DD HH:mm', 'YYYY-MM-DD'],
                dayPopover: 'L',
            },
            attrs: [
                {
                    key: 'today',
                    dot: true,
                    popover: {
                        label: __('Today'),
                    },
                    dates: new Date()
                },
            ],
        }
    },

    computed: {

    },

    watch: {

        data(value) {
            this.update(value);
        }

    },

    methods: {
        handleUpdate(value) {
            this.update(Vue.moment(value).format('YYYY-MM-DD HH:ss'))
        },
        /**
         * Return the date string.
         * `this.data` is the full datetime string. This will get just the date.
         */
        dateString() {
            if (this.data && this.data.length >= 10) {
                return this.data.substr(0, 10)
            } else {
                return Vue.moment().format('YYYY-MM-DD')
            }
        },
    },

    mounted() {


    }
};
</script>
