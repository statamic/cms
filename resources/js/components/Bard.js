import * as core from 'tiptap';
import * as commands from 'tiptap-commands';
import * as utils from 'tiptap-utils';

class Bard {
    constructor(instance) {
        this.instance = instance;
        this.extensionCallbacks = [];
        this.buttonCallbacks = [];
    }

    extend(callback) {
        this.extensionCallbacks.push(callback);
    }

    buttons(callback) {
        this.buttonCallbacks.push(callback);
    }

    get tiptap() {
        return { core, commands, utils };
    }
}

export default Bard;
