<script>
import uniqid from 'uniqid';
import Component from '../Component';

export default {

    model: {
        prop: 'values',
        event: 'updated',
    },

    props: {
        reference: {
            type: String
        },
        name: {
            type: String,
            required: true
        },
        blueprint: {
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
        },
        trackDirtyState: {
            type: Boolean,
            default: true,
        },
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
        this.clearDirtyState();
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
                blueprint: _.clone(this.blueprint),
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
                    blueprint: initial.blueprint,
                    values: initial.values,
                    hiddenFields: {},
                    jsonSubmittingFields: [],
                    revealerFields: [],
                    meta: initial.meta,
                    localizedFields: initial.localizedFields,
                    site: initial.site,
                    fieldLocks: {},
                    errors: {},
                    isRoot: initial.isRoot,
                    preloadedAssets: [],
                    autosaveInterval: null,
                },
                mutations: {
                    setFieldValue(state, payload) {
                        const { handle, value } = payload;
                        state.values[handle] = value;
                    },
                    setValues(state, values) {
                        state.values = values;
                    },
                    setHiddenField(state, field) {
                        state.hiddenFields[field.dottedKey] = {
                            hidden: field.hidden,
                            omitValue: field.omitValue,
                        };
                    },
                    setFieldSubmitsJson(state, dottedKey) {
                        if (state.jsonSubmittingFields.indexOf(dottedKey) === -1) {
                            state.jsonSubmittingFields.push(dottedKey);
                        }
                    },
                    unsetFieldSubmitsJson(state, dottedKey) {
                        const index = state.jsonSubmittingFields.indexOf(dottedKey);
                        if (index !== -1) {
                            state.jsonSubmittingFields.splice(index, 1);
                        }
                    },
                    setRevealerField(state, dottedKey) {
                        if (state.revealerFields.indexOf(dottedKey) === -1) {
                            state.revealerFields.push(dottedKey);
                        }
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
                    setBlueprint(state, blueprint) {
                        state.blueprint = blueprint;
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
                        state.blueprint = payload.blueprint;
                        state.values = payload.values;
                        state.meta = payload.meta;
                        state.site = payload.site;
                    },
                    setPreloadedAssets(state, assets) {
                        state.preloadedAssets = assets;
                    },
                    setAutosaveInterval(state, interval) {
                        if (state.autosaveInterval) {
                            clearInterval(state.autosaveInterval);
                        }
                        state.autosaveInterval = interval;
                    },
                    clearAutosaveInterval(state) {
                        clearInterval(state.autosaveInterval);
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
                    },
                    setMeta(context, payload) {
                        context.commit('setMeta', payload);
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
            this.clearDirtyState();
        },

        clearDirtyState() {
            this.$dirty.remove(this.name);
        },

        pushComponent(name, { props }) {
            const component = new Component(uniqid(), name, props);
            this.components.push(component);
            return component;
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
            if (this.trackDirtyState) this.$dirty.add(this.name);
        }

    },

    watch: {

        values: {
            deep: true,
            handler(after, before) {
                if (_.isEqual(before, after)) return;
                this.$store.commit(`publish/${this.name}/setValues`, after);
            }
        },

        meta: {
            deep: true,
            handler(after, before) {
                if (_.isEqual(before, after)) return;
                this.$store.commit(`publish/${this.name}/setMeta`, after);
            }
        },

        isRoot(isRoot) {
            this.$store.commit(`publish/${this.name}/setIsRoot`, isRoot);
        },

        blueprint: {
            deep: true,
            handler(blueprint) {
                this.$store.commit(`publish/${this.name}/setBlueprint`, blueprint);
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
