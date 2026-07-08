declare namespace _default {
    function data(): {
        preferencesPrefix: null;
    };
    namespace computed {
        function hasPreferences(): any;
    }
    namespace methods {
        function preferencesKey(type: any): string;
        function getPreference(type: any): any;
        function setPreference(type: any, value: any): any;
        function removePreference(type: any, value?: null): any;
    }
}
export default _default;
