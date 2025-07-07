import debounce from '@statamic/util/debounce.js';
import mixin from './Fieldtype.vue';
import emits from './emits.js';
import props from './props.js';
import { computed, watch } from 'vue';
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

    const replicatorPreview = computed(() => {
        if (!props.showFieldPreviews || !props.config.replicator_preview) return;

        return props.value;
    });

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
        // fieldPathPrefix: this.fieldPathPrefix,
        // handle: this.handle,
        value: props.value,
        // config: this.config,
        // meta: this.meta,
        update,
        // updateMeta: this.updateMeta,
        // isReadOnly: this.isReadOnly,
        // store: this.fieldActionStore,
        // storeName: this.fieldActionStoreName,
    }));

    const fieldActionBinding = computed(() => `${props.config.type}-fieldtype`);

    const defineFieldActions = (actions) => {
        return [
            ...Statamic.$fieldActions.get(fieldActionBinding.value),
            ...actions
        ]
            .map((action) => new FieldAction(action, fieldActionPayload.value))
            .filter((action) => action.visible);
    };

    const fieldActions = computed(() => {
        return toFieldActions(
            `${props.config.type}-fieldtype`,
            fieldActionPayload.value,
        );
    });

    watch(replicatorPreview, (text) => {
        if (!props.showFieldPreviews || !props.config.replicator_preview) return;

        emit('replicator-preview-updated', text);
    }, { immediate: true });

    const expose = {
        handle: props.handle,
        name,
    };

    return {
        name,
        isReadOnly,
        replicatorPreview,
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
