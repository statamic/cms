import { Mark, Plugin } from 'tiptap';
import { pasteRule } from 'tiptap-commands';

export default function (extension) {
    return new class extends Mark {
        get name() {
            return extension.name();
        }

        get schema() {
            return extension.schema();
        }

        pasteRules(args) {
            return extension.pasteRules({...args, pasteRule});
        }

        get plugins() {
            return extension.plugins().map(plugin => new Plugin(plugin));
        }
    }
}