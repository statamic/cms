import Slug from './Slug';
import { markRaw } from 'vue';

class Manager {
    make() {
        return markRaw(new Slug());
    }

    create(string) {
        return this.make().create(string);
    }

    separatedBy(separator) {
        return this.make().separatedBy(separator);
    }

    in(language) {
        return this.make().in(language);
    }

    async() {
        return this.make().async();
    }
}

export default Manager;
