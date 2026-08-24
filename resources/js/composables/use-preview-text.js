import { computed } from 'vue';
import { buildPreviewText } from '@/util/buildPreviewText';
import { data_get } from '@/bootstrap/globals.js';

/**
 * Composable for generating preview text in replicator sets.
 *
 * @param {Object} options - Configuration options
 * @param {Object} options.config - The set configuration with fields array
 * @param {Object} options.values - The current field values
 * @param {Object} options.previews - The preview data from mounted fieldtype components
 * @param {string} options.fieldPathPrefix - The field path prefix for looking up previews
 * @param {boolean} options.showFieldPreviews - Whether to show field previews by default
 * @returns {Object} - Object containing the previewText computed property
 */
export default function usePreviewText(options) {
    const {
        config,
        values,
        previews,
        fieldPathPrefix,
        showFieldPreviews,
    } = options;

    const previewText = computed(() => {
        const previewData = data_get(previews.value, fieldPathPrefix.value) || {};

        return buildPreviewText({
            previews: previewData,
            config: config.value,
            values: values.value,
            showFieldPreviews: showFieldPreviews.value,
            separator: ' <span class="text-gray-400 dark:text-gray-600">/</span> ',
        });
    });

    return {
        previewText,
    };
}
