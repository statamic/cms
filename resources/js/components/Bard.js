import * as core from '@tiptap/core';
import * as vue3 from '@tiptap/vue-3';
import * as state from '@tiptap/pm/state';
import * as model from '@tiptap/pm/model';
import * as view from '@tiptap/pm/view';

class Bard {
    constructor(instance) {
        this.instance = instance;
        this.extensionCallbacks = [];
        this.extensionReplacementCallbacks = [];
        this.buttonCallbacks = [];
    }

    addExtension(callback) {
        this.extensionCallbacks.push(callback);
    }

    replaceExtension(name, callback) {
        this.extensionReplacementCallbacks.push({ name, callback });
    }

    buttons(callback) {
        this.buttonCallbacks.push(callback);
    }

    get tiptap() {
        return {
            core,
            vue2,
            pm: {
                state,
                model,
                view
            }
        };
    }
}

export default Bard;
