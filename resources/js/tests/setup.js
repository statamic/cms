import { config } from '@vue/test-utils';

config.global.directives = {
    tooltip: () => {},
};

config.global.mocks = {
    __: (key) => key,
};

// Script setup / composables call `__` as a global, not via component mocks.
globalThis.__ = (key) => key;
