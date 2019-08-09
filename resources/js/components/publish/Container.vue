<script>
export default {

    props: {
        reference: {
            type: String
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
        },
        localizedFields: {
            type: Array
        },
        isRoot: {
            // intentionally not a boolean. we rely on it being undefined in places.
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
                localizedFields: _.clone(this.localizedFields),
                site: this.site,
                isRoot: this.isRoot,
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
                    localizedFields: initial.localizedFields,
                    site: initial.site,
                    fieldLocks: {},
                    errors: {},
                    isRoot: initial.isRoot,
                    preloadedAssets: [],
                },
                mutations: {
                    setFieldValue(state, payload) {
                        const { handle, value } = payload;
                        state.values[handle] = value;
                    },
                    setValues(state, values) {
                        state.values = values;
                    },
                    setMeta(state, meta) {
                        state.meta = meta;
                    },
                    setFieldMeta(state, payload) {
                        const { handle, value } = payload;
                        state.meta[handle] = value;
                    },
                    setIsRoot(state, isRoot) {
                        state.isRoot = isRoot;
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
                    setLocalizedFields(state, fields) {
                        state.localizedFields = fields;
                    },
                    lockField(state, { handle, user }) {
                        Vue.set(state.fieldLocks, handle, user || true);
                    },
                    unlockField(state, handle) {
                        Vue.delete(state.fieldLocks, handle);
                    },
                    initialize(state, payload) {
                        state.fieldset = payload.fieldset;
                        state.values = payload.values;
                        state.meta = payload.meta;
                        state.site = payload.site;
                    },
                    setPreloadedAssets(state, assets) {
                        state.preloadedAssets = assets;
                    }
                },
                actions: {
                    setFieldValue(context, payload) {
                        context.commit('setFieldValue', payload);
                        vm.emitUpdatedEvent(context.state.values);
                    },
                    setFieldMeta(context, payload) {
                        context.commit('setFieldMeta', payload);
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
            this.dirty();
        },

        saved() {
            this.removeNavigationWarning();
        },

        removeNavigationWarning() {
            this.$dirty.remove(this.name);
        },

        pushComponent(component) {
            this.components.push(component);
        },

        setFieldValue(handle, value) {
            this.$store.dispatch(`publish/${this.name}/setFieldValue`, {
                handle, value,
                user: Statamic.user.id
            });
        },

        setFieldMeta(handle, value) {
            this.$store.dispatch(`publish/${this.name}/setFieldMeta`, {
                handle, value,
                user: Statamic.user.id
            });
        },

        dirty() {
            this.$dirty.add(this.name);
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

        meta: {
            deep: true,
            handler(after, before) {
                this.$store.commit(`publish/${this.name}/setMeta`, after);
            }
        },

        isRoot(isRoot) {
            this.$store.commit(`publish/${this.name}/setIsRoot`, isRoot);
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
        },

        localizedFields(fields) {
            this.$store.commit(`publish/${this.name}/setLocalizedFields`, fields);
        }

    },

    render() {
        return this.$scopedSlots.default({
            values: this.$store.state.publish[this.name].values,
            container: this._self,
            components: this.components,
            setFieldValue: this.setFieldValue,
            setFieldMeta: this.setFieldMeta,
        });
    }

}
</script>
