import vue from '@vitejs/plugin-vue';
import externals from './externals.js';

export default function (options = {}) {
    return [
        externals(),
        vue(options.vue || {}),
    ];
}
