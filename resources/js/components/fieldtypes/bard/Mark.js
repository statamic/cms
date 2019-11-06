import { Mark, Plugin } from 'tiptap';
import { updateMark, removeMark, toggleMark, pasteRule } from 'tiptap-commands';

export default function (extension) {
    return new class extends Mark {
        get name() {
            return extension.name();
        }

        get schema() {
            return extension.schema();
        }

        commands(args) {
            return extension.commands({...args, updateMark, removeMark, toggleMark });
        }

        pasteRules(args) {
            return extension.pasteRules({...args, pasteRule});
        }

        get plugins() {
            return extension.plugins().map(plugin => new Plugin(plugin));
        }
    }
}