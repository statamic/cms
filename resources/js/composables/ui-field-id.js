import { computed, inject, unref, useId } from 'vue';

export const UI_FIELD_ID_KEY = 'uiFieldId';

/**
 * Merge aria-describedby id lists, dropping empties and duplicates.
 */
export function mergeAriaDescribedBy(...parts) {
    const ids = parts
        .flatMap((part) => {
            const value = unref(part);
            if (value == null || value === '') return [];
            return String(value).split(/\s+/);
        })
        .filter(Boolean);

    return ids.length ? [...new Set(ids)].join(' ') : undefined;
}

/**
 * Resolve a control's id and field a11y attrs from a parent ui/Field.
 *
 * Id resolution: explicit prop, then a claimed parent Field id (when claimable),
 * then a generated id. Only the first control claims an auto-generated Field id.
 *
 * `describedBy` and `invalid` are only applied when this control's resolved id
 * matches the Field's id — so composite fieldtypes (Table, List, etc.) don't
 * mark every nested control invalid.
 *
 * Returns `{ id, labelId, describedBy, invalid }`.
 */
export function useUiFieldId(id) {
    const context = inject(UI_FIELD_ID_KEY, null);
    const fallback = useId();

    const explicit = unref(id);
    const hasExplicit = explicit != null && explicit !== '';

    let claimedId = null;
    let claimedLabelId = null;

    const canClaim = context?.claimable !== false;

    if (!hasExplicit && canClaim && context?.id != null && !context.claimed) {
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

    const isFieldControl = computed(() => {
        const fieldId = unref(context?.id);
        if (fieldId == null || fieldId === '') return false;
        return resolvedId.value === fieldId;
    });

    const describedBy = computed(() => {
        if (!isFieldControl.value) return undefined;
        return mergeAriaDescribedBy(context?.describedBy);
    });

    const invalid = computed(() => {
        if (!isFieldControl.value) return false;
        return !!unref(context?.invalid);
    });

    return { id: resolvedId, labelId, describedBy, invalid };
}
