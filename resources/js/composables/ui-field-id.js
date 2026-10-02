import { computed, inject, unref, useId } from 'vue';

export const UI_FIELD_ID_KEY = 'uiFieldId';

/**
 * Resolve a control's id: explicit prop, then a claimed parent ui/Field id, then a generated id.
 *
 * Field only exposes an id for injection when it auto-generated one (no explicit `id` prop).
 * The first control to call this claims that id so sibling controls don't share it.
 *
 * Returns `{ id, labelId }`. `labelId` is set when this control claimed the Field association
 * (for non-labelable controls that need `aria-labelledby`).
 */
export function useUiFieldId(id) {
    const context = inject(UI_FIELD_ID_KEY, null);
    const fallback = useId();

    const explicit = unref(id);
    const hasExplicit = explicit != null && explicit !== '';

    let claimedId = null;
    let claimedLabelId = null;

    if (!hasExplicit && context?.id != null && !context.claimed) {
        context.claimed = true;
        claimedId = context.id;
        claimedLabelId = context.labelId ?? null;
    }

    const resolvedId = computed(() => {
        const current = unref(id);
        if (current != null && current !== '') return current;

        if (claimedId != null) {
            const fromField = unref(claimedId);
            if (fromField != null && fromField !== '') return fromField;
        }

        return fallback;
    });

    const labelId = computed(() => {
        if (claimedLabelId == null) return null;
        const value = unref(claimedLabelId);
        return value != null && value !== '' ? value : null;
    });

    return { id: resolvedId, labelId };
}
