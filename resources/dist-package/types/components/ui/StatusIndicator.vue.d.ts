declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The status to display. Options: `published`, `scheduled`, `expired`, `draft`, `hidden` */
    status: {
        type: StringConstructor;
        required: false;
        default: string;
        validator: (value: unknown) => boolean;
    };
    /** When `true`, displays a colored dot indicator */
    showDot: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, displays the status label text */
    showLabel: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, indicates the entry is private */
    private: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The status to display. Options: `published`, `scheduled`, `expired`, `draft`, `hidden` */
    status: {
        type: StringConstructor;
        required: false;
        default: string;
        validator: (value: unknown) => boolean;
    };
    /** When `true`, displays a colored dot indicator */
    showDot: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, displays the status label text */
    showLabel: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, indicates the entry is private */
    private: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{}>, {
    private: boolean;
    status: string;
    showDot: boolean;
    showLabel: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
