declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** When `true`, displays a mode selector dropdown */
    allowModeSelection: {
        type: BooleanConstructor;
        default: boolean;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    fieldActions: {
        type: ArrayConstructor;
        default: () => never[];
    };
    /** Controls whether to indent with tabs or spaces. Options: `tabs`, `spaces` */
    indentType: {
        type: StringConstructor;
        default: string;
    };
    /** Keyboard mapping for the editor. Options: `sublime`, `vim` */
    keyMap: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, line numbers are displayed */
    lineNumbers: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, long lines will wrap */
    lineWrapping: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The syntax highlighting mode. Options: `clike`, `css`, `diff`, `go`, `haml`, `handlebars`, `htmlmixed`, `less`, `markdown`, `gfm`, `nginx`, `text/x-java`, `javascript`, `jsx`, `text/x-objectivec`, `php`, `python`, `ruby`, `scss`, `shell`, `sql`, `twig`, `vue`, `xml`, `yaml-frontmatter` */
    mode: {
        type: StringConstructor;
        default: string;
    };
    /** The controlled value of the code editor */
    modelValue: {
        type: StringConstructor;
        default: string;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Rulers configuration */
    rulers: {
        type: ObjectConstructor;
        default: () => void;
    };
    /** When `true`, displays the current mode label */
    showModeLabel: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The width of a tab character */
    tabSize: {
        type: NumberConstructor;
        required: false;
    };
    /** Theme of the code editor. Options: `system`, `light`, `dark` */
    colorMode: {
        type: StringConstructor;
        default: string;
    };
    /** Title displayed in fullscreen mode */
    title: {
        type: StringConstructor;
        default: () => any;
    };
}>, {
    toggleFullscreen: typeof toggleFullscreen;
    fullScreenMode: import("vue").Ref<boolean, boolean>;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    blur: (...args: any[]) => void;
    focus: (...args: any[]) => void;
    "update:model-value": (...args: any[]) => void;
    "update:mode": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** When `true`, displays a mode selector dropdown */
    allowModeSelection: {
        type: BooleanConstructor;
        default: boolean;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    fieldActions: {
        type: ArrayConstructor;
        default: () => never[];
    };
    /** Controls whether to indent with tabs or spaces. Options: `tabs`, `spaces` */
    indentType: {
        type: StringConstructor;
        default: string;
    };
    /** Keyboard mapping for the editor. Options: `sublime`, `vim` */
    keyMap: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, line numbers are displayed */
    lineNumbers: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, long lines will wrap */
    lineWrapping: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The syntax highlighting mode. Options: `clike`, `css`, `diff`, `go`, `haml`, `handlebars`, `htmlmixed`, `less`, `markdown`, `gfm`, `nginx`, `text/x-java`, `javascript`, `jsx`, `text/x-objectivec`, `php`, `python`, `ruby`, `scss`, `shell`, `sql`, `twig`, `vue`, `xml`, `yaml-frontmatter` */
    mode: {
        type: StringConstructor;
        default: string;
    };
    /** The controlled value of the code editor */
    modelValue: {
        type: StringConstructor;
        default: string;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Rulers configuration */
    rulers: {
        type: ObjectConstructor;
        default: () => void;
    };
    /** When `true`, displays the current mode label */
    showModeLabel: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The width of a tab character */
    tabSize: {
        type: NumberConstructor;
        required: false;
    };
    /** Theme of the code editor. Options: `system`, `light`, `dark` */
    colorMode: {
        type: StringConstructor;
        default: string;
    };
    /** Title displayed in fullscreen mode */
    title: {
        type: StringConstructor;
        default: () => any;
    };
}>> & Readonly<{
    onBlur?: ((...args: any[]) => any) | undefined;
    onFocus?: ((...args: any[]) => any) | undefined;
    "onUpdate:model-value"?: ((...args: any[]) => any) | undefined;
    "onUpdate:mode"?: ((...args: any[]) => any) | undefined;
}>, {
    title: string;
    disabled: boolean;
    modelValue: string;
    readOnly: boolean;
    allowModeSelection: boolean;
    fieldActions: unknown[];
    indentType: string;
    keyMap: string;
    lineNumbers: boolean;
    lineWrapping: boolean;
    mode: string;
    rulers: Record<string, any>;
    showModeLabel: boolean;
    colorMode: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
declare function toggleFullscreen(): void;
