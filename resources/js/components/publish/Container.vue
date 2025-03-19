<script>
import { defineStore, getActivePinia } from 'pinia';
import uniqid from 'uniqid';
import Component from '../Component';
import { getCurrentInstance, computed } from 'vue';
import { usePublishContainerStore } from '@statamic/stores/publish-container.js';
import { isEqual } from 'lodash-es';
import clone from '@statamic/util/clone.js';

export default {
    emits: ['updated', 'focus', 'blur'],

    props: {
        reference: {
            type: String,
        },
        name: {
            type: String,
            required: true,
        },
        blueprint: {
            type: Object,
            default: () => {},
        },
        values: {
            type: Object,
            default: () => {},
        },
        extraValues: {
            type: Object,
            default: () => {},
        },
        meta: {
            type: Object,
            default: () => {},
        },
        errors: {
            type: Object,
        },
        site: {
            type: String,
        },
        localizedFields: {
            type: Array,
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
            store: usePublishContainerStore(this.name, {
                blueprint: clone(this.blueprint),
                values: clone(this.values),
                extraValues: clone(this.extraValues),
                meta: clone(this.meta),
                localizedFields: clone(this.localizedFields),
                site: this.site,
                isRoot: this.isRoot,
                reference: this.reference,
            }),
            components: [], // extra components to be injected
        };
    },

    created() {
        this.$events.$emit('publish-container-created', this);

        this.store.$subscribe((mutation, state) => {
            this.emitUpdatedEvent(state.values);
        });
    },

    beforeUnmount() {
        this.store.$dispose();
        delete getActivePinia().state.value[this.store.$id];
    },

    unmounted() {
        this.clearDirtyState();
        this.$events.$emit('publish-container-destroyed', this);
    },

    provide() {
        return {
            storeName: this.name,
            publishContainer: this,
            store: computed(() => this.store),
        };
    },

    methods: {
        emitUpdatedEvent(values) {
            this.$emit('updated', values);
            this.dirty();
        },

        saving() {
            // Let fieldtypes do any pre-save work, like triggering a "change" event for the focused field.
            this.$events.$emit(`container.${this.name}.saving`);
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
            this.store.setFieldValue({
                handle,
                value,
                user: Statamic.user.id,
            });
        },

        setFieldMeta(handle, value) {
            this.store.setFieldMeta({
                handle,
                value,
                user: Statamic.user.id,
            });
        },

        setValues(values) {
            this.store.values = values;
        },

        setMeta(meta) {
            this.store.meta = meta;
        },

        dirty() {
            if (this.trackDirtyState) this.$dirty.add(this.name);
        },
    },

    watch: {
        values: {
            deep: true,
            handler(after) {
                const before = this.store.values;
                if (isEqual(before, after)) return;
                this.store.setValues(after);
            },
        },

        extraValues: {
            deep: true,
            handler(after) {
                const before = this.store.extraValues;
                if (isEqual(before, after)) return;
                this.store.setExtraValues(after);
            },
        },

        meta: {
            deep: true,
            handler(after) {
                const before = this.store.meta;
                if (isEqual(before, after)) return;
                this.store.setMeta(after);
            },
        },

        isRoot(isRoot) {
            this.store.setIsRoot(isRoot);
        },

        blueprint: {
            deep: true,
            handler(blueprint) {
                this.store.setBlueprint(blueprint);
            },
        },

        site(site) {
            this.store.setSite(site);
        },

        errors(errors) {
            this.store.setErrors(errors);
        },

        localizedFields(fields) {
            this.store.setLocalizedFields(fields);
        },
    },

    render() {
        return this.$slots.default({
            values: this.store.values,
            meta: this.store.meta,
            container: this,
            components: this.components,
            setFieldValue: this.setFieldValue,
            setFieldMeta: this.setFieldMeta,
        })[0];
    },
};
</script>
