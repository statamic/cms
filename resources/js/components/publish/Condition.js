class Condition {

    constructor(id, rule) {
        this.id = id;
        this.rule = rule;
        this.passes = false;
        this.validate = this._validate();
    }

    isJavascript() {
        return typeof this.rule === 'string';
    }

    exists() {
        if (! Statamic.conditions) return false;

        return Statamic.conditions.hasOwnProperty(this.rule);
    }

    _validate() {
        if (this.isJavascript() && ! this.exists()) {
            console.error(`Statamic.conditions.${this.rule} hasn't been implemented.`);
            return () => false;
        }

        if (this.isJavascript() && this.exists()) {
            return Statamic.conditions[this.rule];
        }

        return function (data) {
            let passes = [];

            const ors = Object.keys(this.rule).filter((key) => {
                return key.startsWith('or_');
            });

            for (let field in this.rule) {
                if (ors.includes(field)) {
                    const trimmed = field.substr(3);

                    if (data[trimmed] === this.rule[field]) {
                        return true;
                    }
                }

                if (! ors.includes(field) && Array.isArray(this.rule[field])) {
                    passes.push(this.rule[field].includes(data[field]));
                } else {
                    if (this.rule[field] === 'not null') {
                        passes.push(data[field] !== null);
                    } else {
                        passes.push(data[field] === this.rule[field]);
                    }
                }

            }

            return ! passes.includes(false);
        }
    }

}

export default Condition;
