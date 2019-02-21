import Vue from 'vue';

class LivePreview {
    constructor(instance) {
        this.instance = instance;
        this.storeUrl = '/cp/preferences';
    }

    enabled() {
        return this.instance.$store.state.statamic.livePreview.enabled;
    }
}

Object.defineProperties(Vue.prototype, {
    $preview: {
        get() {
            return new LivePreview(this);
        }
    }
});
