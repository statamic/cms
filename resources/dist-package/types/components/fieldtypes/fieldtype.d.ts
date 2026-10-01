declare namespace _default {
    export { use };
    export { emits };
    export { props };
    export { mixin };
}
export default _default;
declare function use(emit: any, props: any): {
    name: import("vue").ComputedRef<any>;
    isReadOnly: import("vue").ComputedRef<any>;
    replicatorPreview: import("vue").ComputedRef<any>;
    defineReplicatorPreview: (definition: any) => void;
    fieldPathKeys: import("vue").ComputedRef<any>;
    defineFieldActions: (actions: any) => any;
    fieldActions: import("vue").ComputedRef<import("vue").Raw<FieldAction>[]>;
    update: (value: any) => void;
    updateDebounced: {
        (...args: any[]): void;
        cancel(): void;
    };
    updateMeta: (value: any) => void;
    expose: {
        handle: any;
        name: import("vue").ComputedRef<any>;
        fieldActions: import("vue").ComputedRef<import("vue").Raw<FieldAction>[]>;
        replicatorPreview: import("vue").ComputedRef<any>;
    };
};
import emits from './emits.js';
import props from './props.js';
import mixin from './Fieldtype.vue';
import FieldAction from '@/components/field-actions/FieldAction.js';
