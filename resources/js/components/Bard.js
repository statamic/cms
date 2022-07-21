import * as core from 'tiptap';
import * as commands from 'tiptap-commands';
import * as utils from 'tiptap-utils';
import * as extensions from 'tiptap-extensions';
import Link from './fieldtypes/bard/Link';
import Image from './fieldtypes/bard/Image';
import Subscript from './fieldtypes/bard/Subscript';
import Superscript from './fieldtypes/bard/Superscript';

class Bard {
    constructor(instance) {
        this.instance = instance;
        this.extensionCallbacks = [];
        this.extensionReplacementCallbacks = [];
        this.buttonCallbacks = [];
    }

    /** @deprecated */
    extend(callback) {
        this.addExtension(callback);
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
            commands,
            utils,
            extensions: {
                ...extensions,
                Link,
                Image,
                Subscript,
                Superscript,
            },
         };
    }
}

export default Bard;
