type BlueprintField = {
    handle: string;
    [key: string]: unknown;
};
type __VLS_Props = {
    /** Handles of the fields to render, in order. Defaults to every field in the blueprint's first tab. */
    fields?: string[];
    /** Handles of fields to leave out. */
    except?: string[];
    /** Extra values made available to field conditions. */
    extraValues?: Record<string, unknown>;
    /** Wraps the fields in a bordered box. */
    bordered?: boolean;
    /** The connection being edited. Defaults to the one provided by ConnectionList. */
    connection?: Record<string, unknown>;
    /** The blueprint's publish array. Defaults to the one passed to ConnectionList. */
    blueprint?: Record<string, any>;
    /** Fieldtype meta for this connection. Defaults to the one managed by ConnectionList. */
    meta?: Record<string, unknown>;
    /** Validation errors for this connection, keyed by field path. Defaults to the ones from ConnectionList. */
    errors?: Record<string, string[]>;
    /** Prefix for the container name. Defaults to ConnectionList's name. */
    name?: string;
};
declare var __VLS_7: {
    connection: Record<string, unknown>;
}, __VLS_9: {
    connection: Record<string, unknown>;
    fields: BlueprintField[];
    pick: (...handles: string[]) => BlueprintField[];
}, __VLS_22: {
    connection: Record<string, unknown>;
};
type __VLS_Slots = {} & {
    before?: (props: typeof __VLS_7) => any;
} & {
    default?: (props: typeof __VLS_9) => any;
} & {
    after?: (props: typeof __VLS_22) => any;
};
declare const __VLS_base: import("vue").DefineComponent<__VLS_Props, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<__VLS_Props> & Readonly<{}>, {
    bordered: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, false, {}, any>;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
declare const _default: typeof __VLS_export;
export default _default;
type __VLS_WithSlots<T, S> = T & {
    new (): {
        $slots: S;
    };
};
