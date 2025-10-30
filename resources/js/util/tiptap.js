export default async function () {
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
