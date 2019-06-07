<template>
    <div class="datetime">

        <button type="button" class="btn flex mb-1 md:mb-0 items-center pl-1.5" v-if="!hasDate" @click="addDate" tabindex="0">
            <svg-icon name="calendar" class="w-4 h-4 mr-1"></svg-icon>
    		{{ __('Add Date') }}
    	</button>

        <div class="date-time-container md:flex" v-if="hasDate">

            <div class="flex-1 input-group mb-1 md:mb-0">
                <div class="input-group-prepend flex items-center">
                    <svg-icon name="calendar" class="w-4 h-4" />
                </div>
                <v-date-picker
                    v-model="date"
                    class="input-text border border-grey-50 border-l-0"
                    :attributes="attrs"
                    :locale="$config.get('locale')"
                    :formats="formats"
                    :mode="config.mode"
                    :input="value"
                    :is-required="config.required"
                    :is-inline="config.inline"
                    :is-expanded="name === 'date' || config.full_width"
                    :columns="$screens({ default: 1, lg: config.columns })"
                    :rows="$screens({ default: 1, lg: config.rows })"
                    @input="handleUpdate">
                        <input
                            slot-scope="{ inputProps, inputEvents }"
                            class="bg-transparent leading-none w-full"
                            v-bind="inputProps"
                            v-on="inputEvents" />
                </v-date-picker>
            </div>

            <div v-if="config.time_enabled" class="md:ml-1 time-fieldtype">
				<time-fieldtype ref="time" v-if="time" v-model="time" :required="config.time_required" :config="{}" name=""></time-fieldtype>
				<button type="button" class="btn flex items-center pl-1.5" v-if="! time" @click="addTime" tabindex="0">
					<svg-icon name="time" class="w-4 h-4 mr-1"></svg-icon>
                    <span v-text="__('Add Time')"></span>
				</button>
			</div>
        </div>
    </div>

</template>

<script>

export default {

    mixins: [Fieldtype],

    data() {
        return {
            date: this.value ? Vue.moment(this.value).toDate() : (this.config.required) ? Vue.moment().toDate() : null,
            time: this.value ? Vue.moment(this.date).format('HH:mm') : null,
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
        hasDate() {
            return (this.config.required) ? true : this.date !== null;
        },

        dateTime() {
            return Vue.moment(this.date).set({'hour': this.hour, 'minute': this.minutes}).format(this.format);
        },

        hour() {
            return this.time ? this.time.split(':')[0] : 0;
        },

        minutes() {
            return this.time ? this.time.split(':')[1] : 0;
        },

        format() {
            return 'YYYY-MM-DD HH:mm';
        }
    },

    watch: {
        time(value) {
            this.handleUpdate(value)
        }
    },

    methods: {
        handleUpdate(value) {
            if (this.mode === "multiple") {
                this.update(this.dateTime);
            } else {
                this.update(Vue.moment(this.dateTime).format(this.format))
            }
        },
        addDate() {
            this.date = Vue.moment().format(this.format);
        },
        addTime() {
            this.time = Vue.moment().format('HH:mm');
            this.$nextTick(function() {
                $(this.$refs.time.$refs.hour).focus().select();
            });
        },
        removeTime() {
            this.time = null;
        },
    },
};
</script>
