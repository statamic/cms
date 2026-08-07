declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    showOptions: {
        type: BooleanConstructor;
        default: boolean;
    };
    preferencesPrefix: {
        type: StringConstructor;
        required: true;
    };
}>, {}, {
    currentOption: null;
}, {
    preferencesKey(): string;
    wrapperComponent(): "div" | "ui-button-group";
}, {
    setInitialValue(): void;
    setPreference(value: any): void;
}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    showOptions: {
        type: BooleanConstructor;
        default: boolean;
    };
    preferencesPrefix: {
        type: StringConstructor;
        required: true;
    };
}>> & Readonly<{}>, {
    showOptions: boolean;
}, {}, {
    Button: {
        new (...args: any[]): import("vue").CreateComponentPublicInstanceWithMixins<Readonly<import("vue").ExtractPropTypes<{
            as: {
                type: StringConstructor;
                default: null;
            };
            disabled: {
                type: BooleanConstructor;
                default: boolean;
            };
            href: {
                type: StringConstructor;
                default: null;
            };
            target: {
                type: StringConstructor;
                default: null;
            };
            icon: {
                type: StringConstructor;
                default: null;
            };
            iconAppend: {
                type: StringConstructor;
                default: null;
            };
            iconOnly: {
                type: BooleanConstructor;
                default: boolean;
            };
            inset: {
                type: BooleanConstructor;
                default: boolean;
            };
            loading: {
                type: BooleanConstructor;
                default: boolean;
            };
            round: {
                type: BooleanConstructor;
                default: boolean;
            };
            size: {
                type: StringConstructor;
                default: string;
            };
            text: {
                type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
                default: null;
            };
            type: {
                type: StringConstructor;
                default: string;
            };
            variant: {
                type: StringConstructor;
                default: string;
            };
        }>> & Readonly<{}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, import("vue").PublicProps, {
            text: string | number | boolean | null;
            target: string;
            size: string;
            type: string;
            variant: string;
            icon: string;
            as: string;
            href: string;
            iconAppend: string;
            disabled: boolean;
            iconOnly: boolean;
            inset: boolean;
            loading: boolean;
            round: boolean;
        }, true, {}, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, {}, any, import("vue").ComponentProvideOptions, {
            P: {};
            B: {};
            D: {};
            C: {};
            M: {};
            Defaults: {};
        }, Readonly<import("vue").ExtractPropTypes<{
            as: {
                type: StringConstructor;
                default: null;
            };
            disabled: {
                type: BooleanConstructor;
                default: boolean;
            };
            href: {
                type: StringConstructor;
                default: null;
            };
            target: {
                type: StringConstructor;
                default: null;
            };
            icon: {
                type: StringConstructor;
                default: null;
            };
            iconAppend: {
                type: StringConstructor;
                default: null;
            };
            iconOnly: {
                type: BooleanConstructor;
                default: boolean;
            };
            inset: {
                type: BooleanConstructor;
                default: boolean;
            };
            loading: {
                type: BooleanConstructor;
                default: boolean;
            };
            round: {
                type: BooleanConstructor;
                default: boolean;
            };
            size: {
                type: StringConstructor;
                default: string;
            };
            text: {
                type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
                default: null;
            };
            type: {
                type: StringConstructor;
                default: string;
            };
            variant: {
                type: StringConstructor;
                default: string;
            };
        }>> & Readonly<{}>, {}, {}, {}, {}, {
            text: string | number | boolean | null;
            target: string;
            size: string;
            type: string;
            variant: string;
            icon: string;
            as: string;
            href: string;
            iconAppend: string;
            disabled: boolean;
            iconOnly: boolean;
            inset: boolean;
            loading: boolean;
            round: boolean;
        }>;
        __isFragment?: never;
        __isTeleport?: never;
        __isSuspense?: never;
    } & import("vue").ComponentOptionsBase<Readonly<import("vue").ExtractPropTypes<{
        as: {
            type: StringConstructor;
            default: null;
        };
        disabled: {
            type: BooleanConstructor;
            default: boolean;
        };
        href: {
            type: StringConstructor;
            default: null;
        };
        target: {
            type: StringConstructor;
            default: null;
        };
        icon: {
            type: StringConstructor;
            default: null;
        };
        iconAppend: {
            type: StringConstructor;
            default: null;
        };
        iconOnly: {
            type: BooleanConstructor;
            default: boolean;
        };
        inset: {
            type: BooleanConstructor;
            default: boolean;
        };
        loading: {
            type: BooleanConstructor;
            default: boolean;
        };
        round: {
            type: BooleanConstructor;
            default: boolean;
        };
        size: {
            type: StringConstructor;
            default: string;
        };
        text: {
            type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
            default: null;
        };
        type: {
            type: StringConstructor;
            default: string;
        };
        variant: {
            type: StringConstructor;
            default: string;
        };
    }>> & Readonly<{}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, {
        text: string | number | boolean | null;
        target: string;
        size: string;
        type: string;
        variant: string;
        icon: string;
        as: string;
        href: string;
        iconAppend: string;
        disabled: boolean;
        iconOnly: boolean;
        inset: boolean;
        loading: boolean;
        round: boolean;
    }, {}, string, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, import("vue").ComponentProvideOptions> & import("vue").VNodeProps & import("vue").AllowedComponentProps & import("vue").ComponentCustomProps & (new () => {
        $slots: {
            default?: (props: {}) => any;
        };
    });
    Dropdown: {
        new (...args: any[]): import("vue").CreateComponentPublicInstanceWithMixins<Readonly<import("vue").ExtractPropTypes<{
            align: {
                type: StringConstructor;
                default: string;
            };
            offset: {
                type: NumberConstructor;
                default: number;
            };
            side: {
                type: StringConstructor;
                default: string;
            };
        }>> & Readonly<{}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, import("vue").PublicProps, {
            align: string;
            side: string;
            offset: number;
        }, true, {}, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, {}, any, import("vue").ComponentProvideOptions, {
            P: {};
            B: {};
            D: {};
            C: {};
            M: {};
            Defaults: {};
        }, Readonly<import("vue").ExtractPropTypes<{
            align: {
                type: StringConstructor;
                default: string;
            };
            offset: {
                type: NumberConstructor;
                default: number;
            };
            side: {
                type: StringConstructor;
                default: string;
            };
        }>> & Readonly<{}>, {}, {}, {}, {}, {
            align: string;
            side: string;
            offset: number;
        }>;
        __isFragment?: never;
        __isTeleport?: never;
        __isSuspense?: never;
    } & import("vue").ComponentOptionsBase<Readonly<import("vue").ExtractPropTypes<{
        align: {
            type: StringConstructor;
            default: string;
        };
        offset: {
            type: NumberConstructor;
            default: number;
        };
        side: {
            type: StringConstructor;
            default: string;
        };
    }>> & Readonly<{}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, {
        align: string;
        side: string;
        offset: number;
    }, {}, string, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, import("vue").ComponentProvideOptions> & import("vue").VNodeProps & import("vue").AllowedComponentProps & import("vue").ComponentCustomProps & (new () => {
        $slots: {
            trigger?: (props: {}) => any;
        } & {
            default?: (props: {}) => any;
        };
    });
    DropdownMenu: {
        new (...args: any[]): import("vue").CreateComponentPublicInstanceWithMixins<Readonly<{}> & Readonly<{}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, import("vue").PublicProps, {}, true, {}, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, {}, any, import("vue").ComponentProvideOptions, {
            P: {};
            B: {};
            D: {};
            C: {};
            M: {};
            Defaults: {};
        }, Readonly<{}> & Readonly<{}>, {}, {}, {}, {}, {}>;
        __isFragment?: never;
        __isTeleport?: never;
        __isSuspense?: never;
    } & import("vue").ComponentOptionsBase<Readonly<{}> & Readonly<{}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, {}, {}, string, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, import("vue").ComponentProvideOptions> & import("vue").VNodeProps & import("vue").AllowedComponentProps & import("vue").ComponentCustomProps & (new () => {
        $slots: {
            default?: (props: {}) => any;
        };
    });
    DropdownLabel: {
        new (...args: any[]): import("vue").CreateComponentPublicInstanceWithMixins<Readonly<import("vue").ExtractPropTypes<{
            text: {
                type: StringConstructor;
                default: null;
            };
        }>> & Readonly<{}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, import("vue").PublicProps, {
            text: string;
        }, true, {}, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, {}, any, import("vue").ComponentProvideOptions, {
            P: {};
            B: {};
            D: {};
            C: {};
            M: {};
            Defaults: {};
        }, Readonly<import("vue").ExtractPropTypes<{
            text: {
                type: StringConstructor;
                default: null;
            };
        }>> & Readonly<{}>, {}, {}, {}, {}, {
            text: string;
        }>;
        __isFragment?: never;
        __isTeleport?: never;
        __isSuspense?: never;
    } & import("vue").ComponentOptionsBase<Readonly<import("vue").ExtractPropTypes<{
        text: {
            type: StringConstructor;
            default: null;
        };
    }>> & Readonly<{}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, {
        text: string;
    }, {}, string, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, import("vue").ComponentProvideOptions> & import("vue").VNodeProps & import("vue").AllowedComponentProps & import("vue").ComponentCustomProps & (new () => {
        $slots: {
            default?: (props: {}) => any;
        };
    });
    Radio: {
        new (...args: any[]): import("vue").CreateComponentPublicInstanceWithMixins<Readonly<import("vue").ExtractPropTypes<{
            description: {
                type: StringConstructor;
                default: null;
            };
            disabled: {
                type: BooleanConstructor;
                default: boolean;
            };
            label: {
                type: StringConstructor;
                default: null;
            };
            readOnly: {
                type: BooleanConstructor;
                default: boolean;
            };
            value: {
                type: (NumberConstructor | StringConstructor | BooleanConstructor)[];
                required: true;
            };
        }>> & Readonly<{}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, import("vue").PublicProps, {
            label: string;
            description: string;
            disabled: boolean;
            readOnly: boolean;
        }, true, {}, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, {}, any, import("vue").ComponentProvideOptions, {
            P: {};
            B: {};
            D: {};
            C: {};
            M: {};
            Defaults: {};
        }, Readonly<import("vue").ExtractPropTypes<{
            description: {
                type: StringConstructor;
                default: null;
            };
            disabled: {
                type: BooleanConstructor;
                default: boolean;
            };
            label: {
                type: StringConstructor;
                default: null;
            };
            readOnly: {
                type: BooleanConstructor;
                default: boolean;
            };
            value: {
                type: (NumberConstructor | StringConstructor | BooleanConstructor)[];
                required: true;
            };
        }>> & Readonly<{}>, {}, {}, {}, {}, {
            label: string;
            description: string;
            disabled: boolean;
            readOnly: boolean;
        }>;
        __isFragment?: never;
        __isTeleport?: never;
        __isSuspense?: never;
    } & import("vue").ComponentOptionsBase<Readonly<import("vue").ExtractPropTypes<{
        description: {
            type: StringConstructor;
            default: null;
        };
        disabled: {
            type: BooleanConstructor;
            default: boolean;
        };
        label: {
            type: StringConstructor;
            default: null;
        };
        readOnly: {
            type: BooleanConstructor;
            default: boolean;
        };
        value: {
            type: (NumberConstructor | StringConstructor | BooleanConstructor)[];
            required: true;
        };
    }>> & Readonly<{}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, {
        label: string;
        description: string;
        disabled: boolean;
        readOnly: boolean;
    }, {}, string, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, import("vue").ComponentProvideOptions> & import("vue").VNodeProps & import("vue").AllowedComponentProps & import("vue").ComponentCustomProps & (new () => {
        $slots: {
            default?: (props: {}) => any;
        };
    });
    RadioGroup: {
        new (...args: any[]): import("vue").CreateComponentPublicInstanceWithMixins<Readonly<import("vue").ExtractPropTypes<{
            appearance: {
                type: StringConstructor;
                default: string;
                validator: (value: unknown) => boolean;
            };
            inline: {
                type: BooleanConstructor;
                default: boolean;
            };
            modelValue: {
                type: StringConstructor;
                default: null;
            };
            name: {
                type: StringConstructor;
                default: () => string;
            };
            required: {
                type: BooleanConstructor;
                default: boolean;
            };
        }>> & Readonly<{
            "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
        }>, {
            focus: () => void;
        }, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
            "update:modelValue": (...args: any[]) => void;
        }, import("vue").PublicProps, {
            name: string;
            required: boolean;
            modelValue: string;
            inline: boolean;
            appearance: string;
        }, true, {}, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, {}, any, import("vue").ComponentProvideOptions, {
            P: {};
            B: {};
            D: {};
            C: {};
            M: {};
            Defaults: {};
        }, Readonly<import("vue").ExtractPropTypes<{
            appearance: {
                type: StringConstructor;
                default: string;
                validator: (value: unknown) => boolean;
            };
            inline: {
                type: BooleanConstructor;
                default: boolean;
            };
            modelValue: {
                type: StringConstructor;
                default: null;
            };
            name: {
                type: StringConstructor;
                default: () => string;
            };
            required: {
                type: BooleanConstructor;
                default: boolean;
            };
        }>> & Readonly<{
            "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
        }>, {
            focus: () => void;
        }, {}, {}, {}, {
            name: string;
            required: boolean;
            modelValue: string;
            inline: boolean;
            appearance: string;
        }>;
        __isFragment?: never;
        __isTeleport?: never;
        __isSuspense?: never;
    } & import("vue").ComponentOptionsBase<Readonly<import("vue").ExtractPropTypes<{
        appearance: {
            type: StringConstructor;
            default: string;
            validator: (value: unknown) => boolean;
        };
        inline: {
            type: BooleanConstructor;
            default: boolean;
        };
        modelValue: {
            type: StringConstructor;
            default: null;
        };
        name: {
            type: StringConstructor;
            default: () => string;
        };
        required: {
            type: BooleanConstructor;
            default: boolean;
        };
    }>> & Readonly<{
        "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
    }>, {
        focus: () => void;
    }, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
        "update:modelValue": (...args: any[]) => void;
    }, string, {
        name: string;
        required: boolean;
        modelValue: string;
        inline: boolean;
        appearance: string;
    }, {}, string, {}, import("vue").GlobalComponents, import("vue").GlobalDirectives, string, import("vue").ComponentProvideOptions> & import("vue").VNodeProps & import("vue").AllowedComponentProps & import("vue").ComponentCustomProps & (new () => {
        $slots: {
            default?: (props: {}) => any;
        };
    });
}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
