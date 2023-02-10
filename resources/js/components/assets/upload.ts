import { IncomingMessage } from 'http';
import FormDataNode, { SubmitOptions } from 'form-data';

export interface UploadOptions {
  form: Record<string, string | Blob> | FormData | FormDataNode;
  url: string;
  headers?: Record<string, string>;
  withCredentials?: boolean;
}

export interface UploadResponse {
  data?: string | ArrayBuffer | Blob;
  xhr?: XMLHttpRequest;
  status?: Number;
  headers?: Record<string, string | string[] | undefined>;
}

export type UploadState =
  | 'new'
  | 'started'
  | 'failed'
  | 'successful'
  | 'aborted';

export type UploadStateChangeEventListener = (
  this: Upload,
  state: UploadState
) => void;
export type UploadProgressEventListener = (
  this: Upload,
  progress: number
) => void;
export type UploadErrorEventListener = (this: Upload) => void;

type UploadEventListenerUnion =
  | UploadStateChangeEventListener
  | UploadErrorEventListener
  | UploadProgressEventListener;

interface UploadEvents {
  state: Set<UploadStateChangeEventListener>;
  error: Set<UploadErrorEventListener>;
  progress: Set<UploadProgressEventListener>;
}

export class Upload {
  private events: UploadEvents = {
    state: new Set(),
    error: new Set(),
    progress: new Set(),
  };

  private form: Record<string, string | Blob> | FormData | FormDataNode;
  private url: string;
  private headers?: Record<string, string>;
  private xhr?: XMLHttpRequest;
  private withCredentials?: boolean = false;

  private _uploadedBytes = 0;
  private _totalBytes = 0;
  private _state: UploadState = 'new';

  constructor(options: UploadOptions) {
    if (!options) {
      throw new Error('Options are required.');
    }

    if (!options.url || typeof options.url !== 'string') {
      throw new Error('Destination URL is missing or invalid.');
    }

    this.form = options.form;
    this.url = options.url;
    this.headers = options.headers;
    this.withCredentials = options.withCredentials;
  }

  /**
   * POSTs the form.
   */
  upload(): Promise<UploadResponse> {
    return new Promise<UploadResponse>((resolve, reject) => {
      // Check if we're running in a browser.
      if (
        typeof window !== 'undefined' &&
        typeof XMLHttpRequest !== 'undefined'
      ) {
        this.xhr = new XMLHttpRequest();

        if (this.withCredentials) {
          this.xhr.withCredentials = true;
        }

        this.xhr.open('POST', this.url, true);

        if (typeof this.headers === 'object') {
          for (const headerName of Object.keys(this.headers)) {
            this.xhr.setRequestHeader(headerName, this.headers[headerName]);
          }
        }

        this.xhr.addEventListener('loadstart', () => {
          this.setState('started');
        });

        if (this.xhr.upload) {
          this.xhr.upload.addEventListener('progress', e => {
            if (this._totalBytes !== e.total) {
              this.setTotalBytes(e.total);
            }
            this.setUploadedBytes(e.loaded);
          });
        }

        this.xhr.addEventListener('load', () => {
          if (this.xhr) {
            this.setUploadedBytes(this.totalBytes);
            this.setState('successful');

            const response: UploadResponse = {};
            const lines = this.xhr
              .getAllResponseHeaders()
              .replace(/\r/g, '')
              .split('\n');
            const headers: Record<string, string> = {};
            for (const line of lines) {
              const split = line.split(':');
              if (split.length != 2) {
                continue;
              }
              headers[split[0].trim()] = split[1].trim();
            }
            response.headers = headers;
            response.status = this.xhr.status;
            response.xhr = this.xhr;

            switch (this.xhr.responseType) {
              case 'json':
                response.data = JSON.stringify(this.xhr.response);
                break;
              default:
                response.data = this.xhr.response;
            }

            resolve(response);
          }
        });

        this.xhr.addEventListener('error', () => {
          this.setState('failed');
          this.emit('error');
          reject();
        });

        this.xhr.addEventListener('abort', () => {
          this.setState('aborted');
        });

        if (this.form instanceof FormData) {
          this.xhr.send(this.form);
        } else {
          const form = this.form as Record<string, string | Blob>;
          const formData = new FormData();
          for (const key of Object.keys(this.form)) {
            formData.set(key, form[key]);
          }
          this.xhr.send(formData);
        }
      } else {
        const callback = (error: Error | null, res: IncomingMessage) => {
          if (error) {
            this.setState('failed');
            this.emit('error');

            reject();
          } else {
            this.setUploadedBytes(this.totalBytes);
            this.setState('successful');

            let body = '';
            res.on('readable', () => {
              const chunk = res.read();
              if (chunk) {
                body += chunk;
              }
            });
            res.on('end', () => {
              const response: UploadResponse = {};
              response.data = body;
              response.headers = res.headers;
              resolve(response);
            });
          }
        };

        const url = new URL(this.url);
        const options: SubmitOptions = {
          hostname: url.hostname,
          port: url.port,
          path: url.pathname,
          method: 'POST',
          headers: this.headers,
        };

        let formData: FormDataNode;

        if (this.form instanceof FormDataNode) {
          formData = this.form;
        } else {
          const form = this.form as Record<string, string | Blob>;
          formData = new FormDataNode();
          for (const key of Object.keys(this.form)) {
            formData.append(key, form[key]);
          }
        }

        formData.getLength((error: Error | null, length: number) => {
          this.setTotalBytes(length);
        });

        formData.on('data', chunk => {
          if (this.state === 'new') {
            this.setState('started');
          }

          if (chunk.hasOwnProperty('length')) {
            this.increaseUploadedBytes(chunk.length as number);
          }
        });

        formData.submit(options, callback);
      }
    });
  }

  abort(): void {
    this.xhr?.abort();
  }

  get uploadedBytes(): number {
    return this._uploadedBytes;
  }

  private setUploadedBytes(value: number) {
    this._uploadedBytes = value;
    this.emit('progress', this.progress);
  }

  private increaseUploadedBytes(value: number) {
    this._uploadedBytes += value;
    this.emit('progress', this.progress);
  }

  get totalBytes(): number {
    return this._totalBytes;
  }

  private setTotalBytes(value: number) {
    this._totalBytes = value;
    this.emit('progress', this.progress);
  }

  /**
   * Current upload progress. A float between 0 and 1.
   */
  get progress(): number {
    return this._totalBytes === 0 ? 0 : this._uploadedBytes / this._totalBytes;
  }

  get state(): UploadState {
    return this._state;
  }

  private setState(value: UploadState) {
    const oldState = this._state;
    this._state = value;
    if (oldState !== this._state) {
      this.emit('state', this._state);
    }
  }

  /**
   * Adds a listener for a progress event.
   * @param eventType Event type. (progress)
   * @param listener Listener function.
   */
  on(eventType: 'progress', listener: UploadProgressEventListener): void;

  /**
   * Adds a listener for an error event.
   * @param eventType Event type. (error)
   * @param listener Listener function.
   */
  on(eventType: 'error', listener: UploadErrorEventListener): void;

  /**
   * Adds a listener for a state change event.
   * @param eventType Event type. (state)
   * @param listener Listener function.
   */
  on(eventType: 'state', listener: UploadStateChangeEventListener): void;

  /**
   * Adds a listener for a given event.
   * @param eventType Event type.
   * @param listener Listener function.
   */
  on(eventType: keyof UploadEvents, listener: UploadEventListenerUnion): void {
    this.events[eventType].add(listener as any);
  }

  /**
   * Removes a listener for a progress event.
   * @param eventType Event type. (progress)
   * @param listener Listener function.
   */
  off(eventType: 'progress', listener: UploadProgressEventListener): void;

  /**
   * Removes a listener for an error event.
   * @param eventType Event type. (error)
   * @param listener Listener function.
   */
  off(eventType: 'error', listener: UploadErrorEventListener): void;

  /**
   * Removes a listener for a state change event.
   * @param eventType Event type. (state)
   * @param listener Listener function.
   */
  off(eventType: 'state', listener: UploadStateChangeEventListener): void;

  /**
   * Removes a listener for a given event.
   * @param eventType Event type.
   * @param listener Listener function.
   */
  off(eventType: keyof UploadEvents, listener: UploadEventListenerUnion): void {
    this.events[eventType].delete(listener as any);
  }

  private emit(eventType: keyof UploadEvents, ...args: any[]) {
    for (const listener of this.events[eventType]) {
      (listener as any).apply(this, args);
    }
  }
}
