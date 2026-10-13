/**
 * When a page is too large for the browser's history, Firefox throws NS_ERROR_ILLEGAL_VALUE rather than a
 * QuotaExceededError. Inertia only recovers from the latter (with a full page load), so we translate it.
 */
export default function catchFirefoxHistoryLimitErrors() {
    const pushState = window.history.pushState;

    window.history.pushState = function (...args) {
        try {
            return pushState.apply(this, args);
        } catch (error) {
            if (error?.name === 'NS_ERROR_ILLEGAL_VALUE') {
                throw new DOMException(error.message, 'QuotaExceededError');
            }

            throw error;
        }
    };
}
