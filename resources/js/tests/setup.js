import { config } from '@vue/test-utils';

config.global.directives = {
    tooltip: () => {},
};

config.global.mocks = {
    __: (key) => key,
};
