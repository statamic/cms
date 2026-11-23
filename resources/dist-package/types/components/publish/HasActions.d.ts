declare namespace _default {
    namespace props {
        namespace initialItemActions {
            export let type: ArrayConstructor;
            function _default(): never[];
            export { _default as default };
        }
        let itemActionUrl: StringConstructor;
    }
    function data(): any;
    namespace computed {
        function hasItemActions(): any;
    }
    namespace methods {
        function actionStarted(): void;
        function actionCompleted(successful: null | undefined, response: any): void;
        function afterActionSuccessfullyCompleted(response: any): void;
    }
}
export default _default;
