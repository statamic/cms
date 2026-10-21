type Condition = {
    field: string | null;
    operator: string;
    value: unknown;
    join?: 'and' | 'or';
};
type __VLS_Props = {
    conditions?: Condition[];
    fallback?: string | null;
};
declare const __VLS_export: import("vue").DefineComponent<__VLS_Props, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<__VLS_Props> & Readonly<{}>, {
    conditions: Condition[];
    fallback: string | null;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, false, {}, any>;
declare const _default: typeof __VLS_export;
export default _default;
