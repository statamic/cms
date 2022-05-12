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

    addExtension(name, callback) {
        this.extensionCallbacks.push({ name, callback });
    }

    replaceExtension(name, callback) {
        this.extensionReplacementCallbacks.push({ name, callback });
    }

    buttons(callback) {
        this.buttonCallbacks.push(callback);
    }

    /** @deprecated */
    /* get tiptap() {
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
    } */
}

export default Bard;
