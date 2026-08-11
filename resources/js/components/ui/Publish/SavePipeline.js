import axios from 'axios';
import resetValuesFromResponse from '@/util/resetValuesFromResponse.js';
import { reveal, perf } from '@api';
import { UPDATE_DEBOUNCE_MS } from '@/components/fieldtypes/constants';

let container = null;
let errors = null;
let saving = null;

export class Pipeline {
    provide(provided) {
        container = provided.container;
        errors = provided.errors;
        saving = provided.saving;
        return this;
    }

    async through(steps) {
        perf.beginSave();

        try {
            // Wait just past the fieldtype update debounce so any in-flight value updates are flushed first.
            perf.start('publish.save.debounceWait');
            const initialPromise = new Promise((resolve) =>
                setTimeout(() => {
                    perf.stop('publish.save.debounceWait');
                    resolve();
                }, UPDATE_DEBOUNCE_MS + 1),
            );

            return await [new Start(), ...steps, new Finish()].reduce(async (promise, step) => {
                const payload = await promise;

                step = typeof step === 'function' ? step(payload) : step;

                try {
                    return await step.handle(payload);
                } catch (error) {
                    if (error instanceof PipelineStopped) {
                        new Stopped().handle(payload);
                        throw error;
                    }
                    throw error;
                }
            }, initialPromise);
        } finally {
            if (perf.getPhase() === 'save') {
                perf.endSave();
            }
        }
    }
}

export class PipelineStopped extends Error {
    constructor(payload) {
        super();
        this.payload = payload;
    }
}

class Step {}

class Start extends Step {
    handle(payload) {
        if (errors) errors.value = {};
        if (saving) saving.value = true;
        if (container) container.value.saving();

        return payload;
    }
}

export class Request extends Step {
    #url;
    #method;
    #extraData;

    constructor(url, method, extraData) {
        super();
        this.#url = url;
        this.#method = method.toLowerCase();
        this.#extraData = extraData;
    }

    handle(payload) {
        return new Promise((resolve, reject) => {
            const data = perf.measure('publish.save.payload', () => ({
                ...container.value.visibleValues,
                ...this.#extraData,
            }));

            perf.start('publish.save.request');

            return axios[this.#method](this.#url, data)
                .then((response) => {
                    perf.stop('publish.save.request');

                    if (container && response.data.data?.hasOwnProperty('values')) {
                        perf.measure('publish.save.applyResponse', () => {
                            container.value.setValues(
                                resetValuesFromResponse(response.data.data.values, container.value),
                            );
                            container.value.setExtraValues(response.data.data.extraValues);
                        });
                    }
                    resolve(response);
                })
                .catch((e) => {
                    perf.stop('publish.save.request');

                    if (e.response && e.response.status === 422) {
                        const { errors: messages, message } = e.response.data;
                        if (errors) errors.value = messages;
                        Statamic.$toast.error(message);
                        reveal.invalid();
                        e = new PipelineStopped();
                    } else if (e.response && e.response.data.message) {
                        Statamic.$toast.error(e.response.data.message);
                        e = new PipelineStopped();
                    } else if (e.response) {
                        Statamic.$toast.error(`Something went wrong`);
                        e = new PipelineStopped();
                    }
                    reject(e);
                });
        });
    }
}

export class BeforeSaveHooks extends Step {
    #prefix;
    #hookPayload;
    constructor(prefix, hookPayload) {
        super();
        this.#prefix = prefix;
        this.#hookPayload = hookPayload;
    }
    handle(payload) {
        return new Promise((resolve, reject) => {
            perf.start('publish.save.beforeHooks');
            return Statamic.$hooks
                .run(`${this.#prefix}.saving`, this.#hookPayload)
                .then(() => {
                    perf.stop('publish.save.beforeHooks');
                    resolve(payload);
                })
                .catch((error) => {
                    perf.stop('publish.save.beforeHooks');
                    reject(error);
                });
        });
    }
}

export class AfterSaveHooks extends Step {
    #prefix;
    #hookPayload;
    constructor(prefix, hookPayload) {
        super();
        this.#prefix = prefix;
        this.#hookPayload = hookPayload;
    }
    handle(response) {
        return new Promise((resolve, reject) => {
            perf.start('publish.save.afterHooks');
            return Statamic.$hooks
                .run(`${this.#prefix}.saved`, {
                    ...this.#hookPayload,
                    response,
                })
                .then(() => {
                    perf.stop('publish.save.afterHooks');
                    resolve(response);
                })
                .catch((error) => {
                    perf.stop('publish.save.afterHooks');
                    reject(error);
                });
        });
    }
}

class Stopped extends Step {
    handle(payload) {
        if (saving) saving.value = false;

        return payload;
    }
}

class Finish extends Step {
    handle(payload) {
        if (saving) saving.value = false;
        if (container) container.value.saved();

        return payload;
    }
}
