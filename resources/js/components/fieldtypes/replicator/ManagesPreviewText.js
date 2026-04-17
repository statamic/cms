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
            // Delegate to shared utility (non-escaping variant for backward compatibility)
            return formatPreviewValueUtil(value, fieldConfig, { escape: false });
        },
    },
};
