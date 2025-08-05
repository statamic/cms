import { uniqueId } from 'lodash-es';
import { ref } from 'vue';
import uniqid from 'uniqid';

const commands = ref({});

class Command {
    constructor(command) {
        this.key = uniqid();
        this.category = command.category ?? 'Actions';
        this.type = command.type ?? 'action'; // TODO: misc default?
        this.icon = command.icon ?? 'wand';
        this.when = command.when ?? (() => true);
        this.text = command.text;
        this.url = command.url;
        this.action = command.action;
        this.prioritize = command.prioritize ?? false;
        this.openNewTab = command.openNewTab ?? false;

        this.#validate();
    }

    remove() {
        delete commands.value[this.key];
    }

    #validate() {
        if (['string', 'array'].includes(typeof this.text)) {
            console.error('You must provide a `text:` string in your command object');
        }

        if (typeof this.url !== 'string' && typeof this.action !== 'function') {
            console.error('You must provide a `url` string or `action` function to be run with your `'+this.text+'` command');
        }
    }
}

export default class CommandPalette {
    categories() {
        return Statamic.$config.get('commandPaletteCategories');
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
        return Object.values(commands.value).filter(command => command.category !== 'Actions');
    }
}
