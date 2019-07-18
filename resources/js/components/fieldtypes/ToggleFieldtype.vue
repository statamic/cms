<template>
    <div class="toggle-fieldtype-wrapper">
        <toggle-input :value="state" @input="state = $event" :read-only="isReadOnly" />
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    data() {
        return {
            state: null
        }
    },

    watch: {
        state(value) {
            this.update(value);
        }
    },

    created() {
        this.state = this.initialValue();
    },

    methods: {
        initialValue() {
            if (typeof this.value === 'boolean') {
                return this.value;
            }

            if (typeof this.config.default === 'boolean') {
                return this.config.default;
            }

            return false;
        }
    }

};
</script>
