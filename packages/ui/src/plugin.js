import FloatingVue from 'floating-vue';
import { translate, setTranslateFn } from './util/translate';

export default {
    install(app, options = {}) {
        installTranslations(app, options);
        installFloatingVue(app, options);
    }
};

function installTranslations(app, options) {
    if (options.translate) {
        setTranslateFn(options.translate);
    }

    const translateFn = options.translate || translate;
    app.config.globalProperties.__ = translateFn;
    window.__ = translateFn;
}

function installFloatingVue(app, options) {
    const floatingVueOptions = options.floatingVue || {
        disposeTimeout: 30000,
        distance: 10
    };

    app.use(FloatingVue, floatingVueOptions);
}
