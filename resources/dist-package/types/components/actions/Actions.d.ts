export default function useActions(): {
    prepareActions: (actions: any, confirmableActions: any) => any;
    runServerAction: ({ url, selections, action, values, onSuccess, onError }: {
        url: any;
        selections: any;
        action: any;
        values: any;
        onSuccess: any;
        onError: any;
    }) => Promise<any>;
};
