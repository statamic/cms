import { buildPreviewText } from '@/util/buildPreviewText';
import formatPreviewValueUtil from '@/util/formatPreviewValue';

export default {
    computed: {
        previewText() {
            return buildPreviewText({
                previews: this.previews,
                config: this.config,
                values: this.values,
                showFieldPreviews: this.showFieldPreviews,
                separator: ' / ',
            });
        },
    },

    methods: {
        formatPreviewValue(value, fieldConfig) {
            return formatPreviewValueUtil(value, fieldConfig, { escape: false });
        },
    },
};
