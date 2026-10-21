export default function useCopy(): {
    copySupported: import("vue").ComputedRef<boolean>;
    copied: import("vue").ComputedRef<boolean>;
    isCopied: (value: any) => boolean;
    copy: (value: any) => Promise<void>;
};
