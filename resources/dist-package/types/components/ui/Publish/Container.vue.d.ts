export const injectContainerContext: string | ((context: any) => void);
export const provideContainerContext: string | ((context: any) => void);
export const containerContextKey: string | ((context: any) => void);
declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    name: {
        type: StringConstructor;
        default: () => string;
    };
    /** Reference of the item being edited. eg. entry::the-entry-id */
    reference: {
        type: StringConstructor;
    };
    /** The blueprint's publish array. */
    blueprint: {
        type: ObjectConstructor;
        required: true;
    };
    /** The controlled publish form values. */
    modelValue: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** Extra values to be made available to field conditions. */
    extraValues: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** Fieldtype metadata. */
    meta: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** Publish form values from the origin localization. */
    originValues: {
        type: ObjectConstructor;
    };
    /** Fieldtype metadata from the origin localization. */
    originMeta: {
        type: ObjectConstructor;
    };
    /** Validation errors. */
    errors: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** The site handle of the active localization. */
    site: {
        type: StringConstructor;
    };
    /** Array of field handles, indicating which fields have changed. */
    modifiedFields: {
        type: ArrayConstructor;
    };
    /** Determines whether dirty state tracking is enabled. */
    trackDirtyState: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Confirmation text when syncing a localized field with the origin value. */
    syncFieldConfirmationText: {
        type: StringConstructor;
        default: () => any;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Marks it as a "config" form, which renders slightly differently. */
    asConfig: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Determines whether the active tab is remembered in the URL hash. */
    rememberTab: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Extra values to be provided through the context. */
    provide: {
        type: ObjectConstructor;
        default: () => {};
    };
}>, {
    name: string;
    values: import("vue").Ref<Record<string, any>, Record<string, any>>;
    saving: () => void;
    saved: () => void;
    revealerFields: import("vue").Ref<never[], never[]>;
    setFieldValue: (path: any, value: any) => void;
    desyncField: (path: any) => void;
    clearDirtyState: () => void;
    pushComponent: (name: any, { props }: {
        props: any;
    }) => Component;
    visibleValues: import("vue").ComputedRef<any>;
    setValues: (newValues: any) => void;
    setMeta: (newMeta: any) => void;
    setExtraValues: (newValues: any) => void;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
    "update:visibleValues": (...args: any[]) => void;
    "update:modifiedFields": (...args: any[]) => void;
    "update:meta": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    name: {
        type: StringConstructor;
        default: () => string;
    };
    /** Reference of the item being edited. eg. entry::the-entry-id */
    reference: {
        type: StringConstructor;
    };
    /** The blueprint's publish array. */
    blueprint: {
        type: ObjectConstructor;
        required: true;
    };
    /** The controlled publish form values. */
    modelValue: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** Extra values to be made available to field conditions. */
    extraValues: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** Fieldtype metadata. */
    meta: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** Publish form values from the origin localization. */
    originValues: {
        type: ObjectConstructor;
    };
    /** Fieldtype metadata from the origin localization. */
    originMeta: {
        type: ObjectConstructor;
    };
    /** Validation errors. */
    errors: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** The site handle of the active localization. */
    site: {
        type: StringConstructor;
    };
    /** Array of field handles, indicating which fields have changed. */
    modifiedFields: {
        type: ArrayConstructor;
    };
    /** Determines whether dirty state tracking is enabled. */
    trackDirtyState: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Confirmation text when syncing a localized field with the origin value. */
    syncFieldConfirmationText: {
        type: StringConstructor;
        default: () => any;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Marks it as a "config" form, which renders slightly differently. */
    asConfig: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Determines whether the active tab is remembered in the URL hash. */
    rememberTab: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Extra values to be provided through the context. */
    provide: {
        type: ObjectConstructor;
        default: () => {};
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
    "onUpdate:visibleValues"?: ((...args: any[]) => any) | undefined;
    "onUpdate:modifiedFields"?: ((...args: any[]) => any) | undefined;
    "onUpdate:meta"?: ((...args: any[]) => any) | undefined;
}>, {
    name: string;
    errors: Record<string, any>;
    meta: Record<string, any>;
    modelValue: Record<string, any>;
    readOnly: boolean;
    provide: Record<string, any>;
    asConfig: boolean;
    extraValues: Record<string, any>;
    rememberTab: boolean;
    trackDirtyState: boolean;
    syncFieldConfirmationText: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>, {
    default?: (props: {}) => any;
}>;
import Component from '@/components/Component.js';
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
