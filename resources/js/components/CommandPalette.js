import { ref } from 'vue';
import uniqid from 'uniqid';
import { CATEGORY } from './command-palette/Constants.js';

const commands = ref({});

class Command {
    constructor(command) {
        this.key = uniqid();
        this.category = command.category ?? 'Miscellaneous';
        this.icon = command.icon ?? 'wand';
        this.when = command.when ?? (() => true);
        this.text = command.text;
        this.url = command.url;
        this.openNewTab = command.openNewTab ?? false;
        this.action = command.action;
        this.prioritize = command.prioritize ?? false;
        this.type = command.type ?? (command.action ? 'action' : 'link');
        this.trackRecent = command.trackRecent ?? false;

        this.#validate();
    }

    remove() {
        delete commands.value[this.key];
    }

    #validate() {
        if (! (typeof this.text === 'string' || Array.isArray(this.text))) {
            console.error('You must provide a `text:` string in your command object');
        }

        if (typeof this.url !== 'string' && typeof this.action !== 'function') {
            console.error('You must provide a `url` string or `action` function to be run with your `'+this.text+'` command');
        }
    }
}

export default class CommandPalette {
    get category() {
        return CATEGORY;
    }

    add(command) {
        command = new Command(command);

        commands.value[command.key] = command;

        return command;
    }

    actions() {
        return Object.values(commands.value).filter(command => command.category === 'Actions');
    }

    misc() {
        return Object.values(commands.value).filter(command => command.category === 'Miscellaneous');
    }
}
