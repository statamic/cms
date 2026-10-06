export function useIframeManager(iframeContentContainer: any): {
    previousUrl: import("vue").Ref<null, null>;
    updateIframeContents: (url: any, target: any, payload: any, setIframeAttributes: any) => Promise<void>;
};
