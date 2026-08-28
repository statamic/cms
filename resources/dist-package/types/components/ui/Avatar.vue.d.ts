interface Asset {
    permalink: string;
}
interface User {
    /** Custom initials to display */
    initials?: string;
    /** Avatar URL or Asset object */
    avatar?: string | Asset;
    /** User's name (used to generate initials if not provided) */
    name?: string;
}
interface Props {
    /** Object with optional properties: `name`, `initials`, `avatar` */
    user: User;
}
declare const __VLS_export: import("vue").DefineComponent<Props, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<Props> & Readonly<{}>, {
    user: User;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, false, {}, any>;
declare const _default: typeof __VLS_export;
export default _default;
