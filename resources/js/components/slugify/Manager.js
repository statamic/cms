import Slugify from './Slugify';

class Manager {

    make() {
        return new Slugify;
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
