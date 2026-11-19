import { markRaw } from 'vue';
import FieldAction from './FieldAction.js';

export default function toFieldActions(binding, payload, extraActions = []) {
    return [...Statamic.$fieldActions.get(binding), ...extraActions]
        .map((action) => markRaw(new FieldAction(action, payload)))
        .filter((action) => action.visible);
}
