import FloatingVue from 'floating-vue';

export default {
    install(app, options = {}) {
        installTranslations(app, options);
        installFloatingVue(app, options);
    }
};

function installTranslations(app, options) {
    app.config.globalProperties.__ = options.translate || fallbackTranslate;
}

function fallbackTranslate(key, replacements) {
    let message = key;

    for (let replace in replacements) {
        message = message.split(':' + replace).join(replacements[replace]);
    }

    return message;
}

function installFloatingVue(app, options) {
    const floatingVueOptions = options.floatingVue || {
        disposeTimeout: 30000,
        distance: 10
    };

    app.use(FloatingVue, floatingVueOptions);
}
