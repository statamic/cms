<template>
    <v-date-picker
        v-model="date"
        :attributes="attrs"
        :formats="formats"
        :mode="config.mode"
        :input="value"
        :is-required="config.required"
        :is-inline="config.inline"
        :is-expanded="name === 'date' || config.full_width"
        :columns="$screens({ default: 1, lg: config.columns })"
        :rows="$screens({ default: 1, lg: config.rows })"
        @input="handleUpdate" />
</template>

<script>

export default {

    mixins: [Fieldtype],

    data() {
        return {
            date: this.value ? Vue.moment(this.value).toDate() : (this.config.required) ? Vue.moment().toDate() : null,
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

    methods: {
        handleUpdate(value) {
            if (this.mode === "multiple") {
                this.update(value);
            } else {
                this.update(Vue.moment(value).format('YYYY-MM-DD HH:mm'))
            }
        }
    },

    mounted() {


    }
};
</script>
