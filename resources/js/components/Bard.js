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

    async tiptap() {
        const [core, vue3, state, model, view] = await Promise.all([
            import('@tiptap/core'),
            import('@tiptap/vue-3'),
            import('@tiptap/pm/state'),
            import('@tiptap/pm/model'),
            import('@tiptap/pm/view'),
        ]);

        return {
            core,
            vue3,
            pm: {
                state,
                model,
                view,
            },
        };
    }
}

export default Bard;
