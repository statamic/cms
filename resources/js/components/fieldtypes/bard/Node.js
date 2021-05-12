import { Node, Plugin } from 'tiptap';
import { pasteRule, toggleBlockType } from 'tiptap-commands';

export default function (extension) {
    return new class extends Node {
        get name() {
            return extension.name();
        }

        get schema() {
            return extension.schema();
        }

        commands(args) {
            return extension.commands({...args, toggleBlockType });
        }

        pasteRules(args) {
            return extension.pasteRules({...args, pasteRule});
        }

        get plugins() {
            return extension.plugins().map(plugin => new Plugin(plugin));
        }
    }
}
