<script>
export default {

    props: {
        reference: {
            type: String,
            required: true
        },
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
        meta: {
            type: Object,
            default: () => {}
        },
        errors: {
            type: Object
        },
        site: {
            type: String
        }
    },

    data() {
        return {
            components: [], // extra components to be injected
        }
    },

    created() {
        this.registerVuexModule();
        this.$events.$emit('publish-container-created', this);
    },

    destroyed() {
        this.removeVuexModule();
        this.removeNavigationWarning();
        this.$events.$emit('publish-container-destroyed', this);
    },

    provide() {
        return {
            storeName: this.name
        }
    },

    methods: {

        registerVuexModule() {
            const vm = this;

            const initial = {
                fieldset: _.clone(this.fieldset),
                values: _.clone(this.values),
                meta: _.clone(this.meta),
                site: this.site,
            };

            // If the store already exists, just reinitialize the state.
            if (this.$store.state.hasOwnProperty('publish')
            && this.$store.state.publish.hasOwnProperty(this.name)) {
                this.$store.commit(`publish/${this.name}/initialize`, initial);
                return;
            }

            this.$store.registerModule(['publish', this.name], {
                namespaced: true,
                state: {
                    fieldset: initial.fieldset,
                    values: initial.values,
                    meta: initial.meta,
                    site: initial.site,
                    errors: {},
                },
                mutations: {
                    setValue(state, payload) {
                        const { handle, value } = payload;
                        state.values[handle] = value;
                    },
                    setValues(state, values) {
                        state.values = values;
                    },
                    setFieldset(state, fieldset) {
                        state.fieldset = fieldset;
                    },
                    setErrors(state, errors) {
                        state.errors = errors;
                    },
                    setSite(state, site) {
                        state.site = site;
                    },
                    initialize(state, payload) {
                        state.fieldset = payload.fieldset;
                        state.values = payload.values;
                        state.meta = payload.meta;
                        state.site = payload.site;
                    }
                },
                actions: {
                    setValue(context, payload) {
                        context.commit('setValue', payload);
                        vm.emitUpdatedEvent(context.state.values);
                    },
                    setValues(context, payload) {
                        context.commit('setValues', payload);
                        vm.emitUpdatedEvent(context.state.values);
                    }
                }
            });
        },

        removeVuexModule() {
            this.$store.unregisterModule(['publish', this.name]);
        },

        emitUpdatedEvent(values) {
            this.$emit('updated', values);
            this.$dirty.add(this.name);
        },

        saved() {
            this.removeNavigationWarning();
        },

        removeNavigationWarning() {
            this.$dirty.remove(this.name);
        },

        pushComponent(component) {
            this.components.push(component);
        }

    },

    watch: {

        values: {
            deep: true,
            handler(after, before) {
                if (before === after) return;
                this.$store.commit(`publish/${this.name}/setValues`, after);
            }
        },

        fieldset: {
            deep: true,
            handler(fieldset) {
                this.$store.commit(`publish/${this.name}/setFieldset`, fieldset);
            }
        },

        site(site) {
            this.$store.commit(`publish/${this.name}/setSite`, site);
        },

        errors(errors) {
            this.$store.commit(`publish/${this.name}/setErrors`, errors);
        }

    },

    render() {
        return this.$scopedSlots.default({
            values: this.$store.state.publish[this.name].values,
            container: this._self,
            components: this.components,
        });
    }

}
</script>
