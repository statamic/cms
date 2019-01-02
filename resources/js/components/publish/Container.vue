<script>
import _ from 'lodash';

export default {

    props: {
        name: {
            type: String,
            required: true
        },
        fieldset: {
            type: Object,
            default: () => {}
        },
        values: {
            type: Object,
            default: () => {}
        },
        errors: {
            type: Object
        }
    },

    created() {
        this.registerVuexModule();
    },

    provide() {
        return {
            storeName: this.name
        }
    },

    methods: {

        registerVuexModule() {
            const vm = this;

            this.$store.registerModule(['publish', this.name], {
                namespaced: true,
                state: {
                    fieldset: _.clone(this.fieldset),
                    values: _.clone(this.values),
                    errors: {}
                },
                mutations: {
                    updateField(state, payload) {
                        const { handle, value } = payload;
                        state.values[handle] = value;
                    },
                    updateFields(state, values) {
                        state.values = values;
                    },
                    setErrors(state, errors) {
                        state.errors = errors;
                    }
                },
                actions: {
                    updateField(context, payload) {
                        context.commit('updateField', payload);
                        vm.emitUpdatedEvent(context.state.values);
                    },
                    updateFields(context, payload) {
                        context.commit('updateFields', payload);
                        vm.emitUpdatedEvent(context.state.values);
                    }
                }
            });
        },

        emitUpdatedEvent(values) {
            this.$emit('updated', values);
            this.enableNavigationWarning();
        },

        enableNavigationWarning() {
            window.onbeforeunload = () => '';
        }

    },

    watch: {

        values: {
            deep: true,
            handler(after, before) {
                if (JSON.stringify(after) === JSON.stringify(before)) return;

                console.error(`The "values" prop is reserved for initializing the Publish store. You should use this.$store.commit('${this.name}/setValues', values) instead.`);
            }
        },

        fieldset: {
            deep: true,
            handler() {
                console.error(`The "fieldset" prop is reserved for initializing the Publish store. You should use this.$store.commit('${this.name}/setFieldset', fieldset) instead.`);
            }
        },

        errors(errors) {
            this.$store.commit(`publish/${this.name}/setErrors`, errors);
        }

    },

    render() {
        return this.$scopedSlots.default({
            values: this.$store.state.publish[this.name].values
        });
    }

}
</script>
