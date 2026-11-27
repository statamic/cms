export function tooltipGapRect(trigger: any, popper: any): {
    left: number;
    right: number;
    top: any;
    bottom: any;
} | {
    left: any;
    right: any;
    top: number;
    bottom: number;
} | null;
export function isInTooltipHoverRegion(x: any, y: any, trigger: any, popper: any, edge?: number): boolean;
export function useTooltip(): {
    isVisible: Readonly<import("vue").Ref<boolean, boolean>>;
    content: Readonly<import("vue").Ref<string, string>>;
    html: Readonly<import("vue").Ref<boolean, boolean>>;
    copyable: Readonly<import("vue").Ref<boolean, boolean>>;
    targetEl: Readonly<import("vue").Ref<null, null>>;
    show: typeof show;
    hide: typeof hide;
    dismissFor: typeof dismissFor;
    registerContentEl: typeof registerContentEl;
};
declare function show(el: any, options: any): void;
declare function hide(): void;
declare function dismissFor(el: any, event: any): void;
declare function registerContentEl(el: any): void;
export {};
