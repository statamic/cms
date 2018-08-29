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
                    values: _.clone(this.values)
                },
                mutations: {
                    updateField(state, payload) {
                        const { handle, value } = payload;
                        state.values[handle] = value;
                    },
                    updateFields(state, values) {
                        state.values = values;
                    }
                },
                actions: {
                    updateField(context, payload) {
                        context.commit('updateField', payload);
                        vm.$emit('updated', context.state.values);
                    },
                    updateFields(context, payload) {
                        context.commit('updateFields', payload);
                        vm.$emit('updated', context.state.values);
                    }
                }
            });
        }

    },

    watch: {

        values: {
            deep: true,
            handler() {
                console.error(`The "values" prop is reserved for initializing the Publish store. You should use this.$store.commit('${this.name}/setValues', values) instead.`);
            }
        },

        fieldset: {
            deep: true,
            handler() {
                console.error(`The "fieldset" prop is reserved for initializing the Publish store. You should use this.$store.commit('${this.name}/setFieldset', fieldset) instead.`);
            }
        }

    },

    render() {
        return this.$scopedSlots.default({
            values: this.$store.state.publish[this.name].values
        });
    }

}
</script>
