import debounce from '@statamic/util/debounce.js';
import mixin from './Fieldtype.vue';
import emits from './emits.js';
import props from './props.js';
import { computed, ref, watch } from 'vue';
import FieldAction from '@statamic/components/field-actions/FieldAction.js';
import toFieldActions from '@statamic/components/field-actions/toFieldActions.js';

const use = function(emit, props) {
    const name = computed(() => {
        if (props.namePrefix) {
            return `${props.namePrefix}[${props.handle}]`;
        }

        return props.handle;
    });

    const isReadOnly = computed(() => {
        return (
            props.readOnly ||
            props.config.visibility === 'read_only' ||
            props.config.visibility === 'computed' ||
            false
        );
    });

    const customReplicatorPreview = ref(null);

    const replicatorPreview = computed(() => {
        if (!props.showFieldPreviews || !props.config.replicator_preview) return;

        if (customReplicatorPreview.value) return customReplicatorPreview.value.value;

        return props.value;
    });

    function defineReplicatorPreview(definition) {
        customReplicatorPreview.value = computed(definition);
    }

    watch(replicatorPreview, (text) => {
        if (!props.showFieldPreviews || !props.config.replicator_preview) return;

        emit('replicator-preview-updated', text);
    }, { immediate: true });

    const fieldPathKeys = computed(() => {
        const prefix = props.fieldPathPrefix || props.handle;

        return prefix.split('.');
    });

    const update = (value) => {
        emit('update:value', value);
    };

    const updateDebounced = debounce(function (value) {
        update(value);
    }, 150);

    const updateMeta = (value) => {
        emit('update:meta', value);
    };

    const fieldActionPayload = computed(() => ({
        // vm: this,
        fieldPathPrefix: props.fieldPathPrefix,
        handle: props.handle,
        value: props.value,
        config: props.config,
        meta: props.meta,
        update,
        updateMeta,
        isReadOnly: isReadOnly.value,
        // store: this.fieldActionStore,
        // storeName: this.fieldActionStoreName,
    }));

    const internalFieldActions = ref([]);

    const defineFieldActions = (actions) => internalFieldActions.value = actions;

    const fieldActions = computed(() => {
        return toFieldActions(
            `${props.config.type}-fieldtype`,
            fieldActionPayload.value,
            internalFieldActions.value,
        );
    });

    const expose = {
        handle: props.handle,
        name,
        fieldActions,
    };

    return {
        name,
        isReadOnly,
        replicatorPreview,
        defineReplicatorPreview,
        fieldPathKeys,
        defineFieldActions,
        fieldActions,
        update,
        updateDebounced,
        updateMeta,
        expose,
    };
}

export default {
    use,
    emits,
    props,
    mixin
};
