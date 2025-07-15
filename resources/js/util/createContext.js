import { inject, provide } from 'vue';

export default function createContext(name) {
    const injectionKey = Symbol(`${name}Context`);

    const provideContext = (context) => {
        provide(injectionKey, context);
    };

    const injectContext = () => {
        return inject(injectionKey);
    };

    return [injectContext, provideContext, injectionKey];
}
