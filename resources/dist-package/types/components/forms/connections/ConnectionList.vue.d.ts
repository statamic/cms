type Connection = {
    id: string;
    enabled: boolean;
    conditions: {
        _id: string;
        field: string;
        operator: string;
        value: string;
    }[];
    [key: string]: unknown;
};
type __VLS_Props = {
    modelValue: Connection[];
    errors: Record<string, string[]>;
    defaults: {
        values?: Record<string, unknown>;
        meta?: Record<string, unknown>;
    };
    /** The blueprint's publish array, used by ConnectionFields. */
    blueprint?: Record<string, unknown>;
    /** Fieldtype meta for existing connections, keyed by connection id. */
    meta?: Record<string, Record<string, unknown>>;
    /** Prefix for each connection's publish container name. */
    name?: string;
    addLabel: string;
    description?: string;
    deleteHeading: string;
    deleteDescription: string;
    /** Label for the "always" option when no default slot is given. */
    alwaysLabel?: string;
    /** Label for the "if" option when no default slot is given. */
    ifLabel?: string;
    /** Whether to render ConnectionRules around the fields when no default slot is given. */
    rules?: boolean;
};
declare var __VLS_47: {
    item: Connection;
    index: number;
    collapsed: boolean;
}, __VLS_49: {
    item: Connection;
    index: number;
    errors: {};
    meta: Record<string, unknown>;
};
type __VLS_Slots = {} & {
    header?: (props: typeof __VLS_47) => any;
} & {
    default?: (props: typeof __VLS_49) => any;
};
declare const __VLS_base: import("vue").DefineComponent<__VLS_Props, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<__VLS_Props> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    name: string;
    errors: Record<string, string[]>;
    meta: Record<string, Record<string, unknown>>;
    modelValue: Connection[];
    defaults: {
        values?: Record<string, unknown>;
        meta?: Record<string, unknown>;
    };
    addLabel: string;
    deleteHeading: string;
    deleteDescription: string;
    rules: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, false, {}, any>;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
declare const _default: typeof __VLS_export;
export default _default;
type __VLS_WithSlots<T, S> = T & {
    new (): {
        $slots: S;
    };
};
