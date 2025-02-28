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
}

export default Bard;
