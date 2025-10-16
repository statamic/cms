import { inject, provide } from 'vue';

export default function createContext(name) {
    const injectionKey = `${name}Context`;

    const provideContext = (context) => {
        provide(injectionKey, context);
    };

    const injectContext = () => {
        return inject(injectionKey, null);
    };

    return [injectContext, provideContext, injectionKey];
}
