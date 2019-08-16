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
}

export default Bard;
