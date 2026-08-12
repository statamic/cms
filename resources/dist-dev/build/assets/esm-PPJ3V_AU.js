//#region node_modules/@simplewebauthn/browser/esm/helpers/bufferToBase64URLString.js
/**
* Convert the given array buffer into a Base64URL-encoded string. Ideal for converting various
* credential response ArrayBuffers to string for sending back to the server as JSON.
*
* Helper method to compliment `base64URLStringToBuffer`
*/
function bufferToBase64URLString(buffer) {
	const bytes = new Uint8Array(buffer);
	let str = "";
	for (const charCode of bytes) str += String.fromCharCode(charCode);
	return btoa(str).replace(/\+/g, "-").replace(/\//g, "_").replace(/=/g, "");
}
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/helpers/base64URLStringToBuffer.js
/**
* Convert from a Base64URL-encoded string to an Array Buffer. Best used when converting a
* credential ID from a JSON string to an ArrayBuffer, like in allowCredentials or
* excludeCredentials
*
* Helper method to compliment `bufferToBase64URLString`
*/
function base64URLStringToBuffer(base64URLString) {
	const base64 = base64URLString.replace(/-/g, "+").replace(/_/g, "/");
	/**
	* Pad with '=' until it's a multiple of four
	* (4 - (85 % 4 = 1) = 3) % 4 = 3 padding
	* (4 - (86 % 4 = 2) = 2) % 4 = 2 padding
	* (4 - (87 % 4 = 3) = 1) % 4 = 1 padding
	* (4 - (88 % 4 = 0) = 4) % 4 = 0 padding
	*/
	const padLength = (4 - base64.length % 4) % 4;
	const padded = base64.padEnd(base64.length + padLength, "=");
	const binary = atob(padded);
	const buffer = new ArrayBuffer(binary.length);
	const bytes = new Uint8Array(buffer);
	for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
	return buffer;
}
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthn.js
/**
* Determine if the browser is capable of Webauthn
*/
function browserSupportsWebAuthn() {
	return _browserSupportsWebAuthnInternals.stubThis(globalThis?.PublicKeyCredential !== void 0 && typeof globalThis.PublicKeyCredential === "function");
}
/**
* Make it possible to stub the return value during testing
* @ignore Don't include this in docs output
*/
var _browserSupportsWebAuthnInternals = { stubThis: (value) => value };
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/helpers/toPublicKeyCredentialDescriptor.js
function toPublicKeyCredentialDescriptor(descriptor) {
	const { id } = descriptor;
	return {
		...descriptor,
		id: base64URLStringToBuffer(id),
		/**
		* `descriptor.transports` is an array of our `AuthenticatorTransportFuture` that includes newer
		* transports that TypeScript's DOM lib is ignorant of. Convince TS that our list of transports
		* are fine to pass to WebAuthn since browsers will recognize the new value.
		*/
		transports: descriptor.transports
	};
}
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/helpers/isValidDomain.js
/**
* A simple test to determine if a hostname is a properly-formatted domain name
*
* A "valid domain" is defined here: https://url.spec.whatwg.org/#valid-domain
*
* Regex was originally sourced from here, then remixed to add punycode support:
* https://www.oreilly.com/library/view/regular-expressions-cookbook/9781449327453/ch08s15.html
*/
function isValidDomain(hostname) {
	return hostname === "localhost" || /^((xn--[a-z0-9-]+|[a-z0-9]+(-[a-z0-9]+)*)\.)+([a-z]{2,}|xn--[a-z0-9-]+)$/i.test(hostname);
}
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/helpers/webAuthnError.js
/**
* A custom Error used to return a more nuanced error detailing _why_ one of the eight documented
* errors in the spec was raised after calling `navigator.credentials.create()` or
* `navigator.credentials.get()`:
*
* - `AbortError`
* - `ConstraintError`
* - `InvalidStateError`
* - `NotAllowedError`
* - `NotSupportedError`
* - `SecurityError`
* - `TypeError`
* - `UnknownError`
*
* Error messages were determined through investigation of the spec to determine under which
* scenarios a given error would be raised.
*/
var WebAuthnError = class extends Error {
	constructor({ message, code, cause, name }) {
		super(message, { cause });
		Object.defineProperty(this, "code", {
			enumerable: true,
			configurable: true,
			writable: true,
			value: void 0
		});
		this.name = name ?? cause.name;
		this.code = code;
	}
};
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/helpers/identifyRegistrationError.js
/**
* Attempt to intuit _why_ an error was raised after calling `navigator.credentials.create()`
*/
function identifyRegistrationError({ error, options }) {
	const { publicKey } = options;
	if (!publicKey) throw Error("options was missing required publicKey property");
	if (error.name === "AbortError") {
		if (options.signal instanceof AbortSignal) return new WebAuthnError({
			message: "Registration ceremony was sent an abort signal",
			code: "ERROR_CEREMONY_ABORTED",
			cause: error
		});
	} else if (error.name === "ConstraintError") {
		if (publicKey.authenticatorSelection?.requireResidentKey === true) return new WebAuthnError({
			message: "Discoverable credentials were required but no available authenticator supported it",
			code: "ERROR_AUTHENTICATOR_MISSING_DISCOVERABLE_CREDENTIAL_SUPPORT",
			cause: error
		});
		else if (options.mediation === "conditional" && publicKey.authenticatorSelection?.userVerification === "required") return new WebAuthnError({
			message: "User verification was required during automatic registration but it could not be performed",
			code: "ERROR_AUTO_REGISTER_USER_VERIFICATION_FAILURE",
			cause: error
		});
		else if (publicKey.authenticatorSelection?.userVerification === "required") return new WebAuthnError({
			message: "User verification was required but no available authenticator supported it",
			code: "ERROR_AUTHENTICATOR_MISSING_USER_VERIFICATION_SUPPORT",
			cause: error
		});
	} else if (error.name === "InvalidStateError") return new WebAuthnError({
		message: "The authenticator was previously registered",
		code: "ERROR_AUTHENTICATOR_PREVIOUSLY_REGISTERED",
		cause: error
	});
	else if (error.name === "NotAllowedError")
 /**
	* Pass the error directly through. Platforms are overloading this error beyond what the spec
	* defines and we don't want to overwrite potentially useful error messages.
	*/
	return new WebAuthnError({
		message: error.message,
		code: "ERROR_PASSTHROUGH_SEE_CAUSE_PROPERTY",
		cause: error
	});
	else if (error.name === "NotSupportedError") {
		if (publicKey.pubKeyCredParams.filter((param) => param.type === "public-key").length === 0) return new WebAuthnError({
			message: "No entry in pubKeyCredParams was of type \"public-key\"",
			code: "ERROR_MALFORMED_PUBKEYCREDPARAMS",
			cause: error
		});
		return new WebAuthnError({
			message: "No available authenticator supported any of the specified pubKeyCredParams algorithms",
			code: "ERROR_AUTHENTICATOR_NO_SUPPORTED_PUBKEYCREDPARAMS_ALG",
			cause: error
		});
	} else if (error.name === "SecurityError") {
		const effectiveDomain = globalThis.location.hostname;
		if (!isValidDomain(effectiveDomain)) return new WebAuthnError({
			message: `${globalThis.location.hostname} is an invalid domain`,
			code: "ERROR_INVALID_DOMAIN",
			cause: error
		});
		else if (publicKey.rp.id !== effectiveDomain) return new WebAuthnError({
			message: `The RP ID "${publicKey.rp.id}" is invalid for this domain`,
			code: "ERROR_INVALID_RP_ID",
			cause: error
		});
	} else if (error.name === "TypeError") {
		if (publicKey.user.id.byteLength < 1 || publicKey.user.id.byteLength > 64) return new WebAuthnError({
			message: "User ID was not between 1 and 64 characters",
			code: "ERROR_INVALID_USER_ID_LENGTH",
			cause: error
		});
	} else if (error.name === "UnknownError") return new WebAuthnError({
		message: "The authenticator was unable to process the specified options, or could not create a new credential",
		code: "ERROR_AUTHENTICATOR_GENERAL_ERROR",
		cause: error
	});
	return error;
}
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/helpers/webAuthnAbortService.js
var BaseWebAuthnAbortService = class {
	constructor() {
		Object.defineProperty(this, "controller", {
			enumerable: true,
			configurable: true,
			writable: true,
			value: void 0
		});
	}
	createNewAbortSignal() {
		if (this.controller) {
			const abortError = /* @__PURE__ */ new Error("Cancelling existing WebAuthn API call for new one");
			abortError.name = "AbortError";
			this.controller.abort(abortError);
		}
		const newController = new AbortController();
		this.controller = newController;
		return newController.signal;
	}
	cancelCeremony() {
		if (this.controller) {
			const abortError = /* @__PURE__ */ new Error("Manually cancelling existing WebAuthn API call");
			abortError.name = "AbortError";
			this.controller.abort(abortError);
			this.controller = void 0;
		}
	}
};
/**
* A service singleton to help ensure that only a single WebAuthn ceremony is active at a time.
*
* Users of **@simplewebauthn/browser** shouldn't typically need to use this, but it can help e.g.
* developers building projects that use client-side routing to better control the behavior of
* their UX in response to router navigation events.
*/
var WebAuthnAbortService = new BaseWebAuthnAbortService();
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/helpers/toAuthenticatorAttachment.js
var attachments = ["cross-platform", "platform"];
/**
* If possible coerce a `string` value into a known `AuthenticatorAttachment`
*/
function toAuthenticatorAttachment(attachment) {
	if (!attachment) return;
	if (attachments.indexOf(attachment) < 0) return;
	return attachment;
}
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/methods/startRegistration.js
/**
* Begin authenticator "registration" via WebAuthn attestation
*
* @param optionsJSON Output from **@simplewebauthn/server**'s `generateRegistrationOptions()`
* @param useAutoRegister (Optional) Try to silently create a passkey with the password manager that the user just signed in with. Defaults to `false`.
*/
async function startRegistration(options) {
	if (!options.optionsJSON && options.challenge) {
		console.warn("startRegistration() was not called correctly. It will try to continue with the provided options, but this call should be refactored to use the expected call structure instead. See https://simplewebauthn.dev/docs/packages/browser#typeerror-cannot-read-properties-of-undefined-reading-challenge for more information.");
		options = { optionsJSON: options };
	}
	const { optionsJSON, useAutoRegister = false } = options;
	if (!browserSupportsWebAuthn()) throw new Error("WebAuthn is not supported in this browser");
	const publicKey = {
		...optionsJSON,
		challenge: base64URLStringToBuffer(optionsJSON.challenge),
		user: {
			...optionsJSON.user,
			id: base64URLStringToBuffer(optionsJSON.user.id)
		},
		excludeCredentials: optionsJSON.excludeCredentials?.map(toPublicKeyCredentialDescriptor)
	};
	const createOptions = {};
	/**
	* Try to use conditional create to register a passkey for the user with the password manager
	* the user just used to authenticate with. The user won't be shown any prominent UI by the
	* browser.
	*/
	if (useAutoRegister) createOptions.mediation = "conditional";
	createOptions.publicKey = publicKey;
	createOptions.signal = WebAuthnAbortService.createNewAbortSignal();
	let credential;
	try {
		credential = await navigator.credentials.create(createOptions);
	} catch (err) {
		throw identifyRegistrationError({
			error: err,
			options: createOptions
		});
	}
	if (!credential) throw new Error("Registration was not completed");
	const { id, rawId, response, type } = credential;
	let transports = void 0;
	if (typeof response.getTransports === "function") transports = response.getTransports();
	let responsePublicKeyAlgorithm = void 0;
	if (typeof response.getPublicKeyAlgorithm === "function") try {
		responsePublicKeyAlgorithm = response.getPublicKeyAlgorithm();
	} catch (error) {
		warnOnBrokenImplementation("getPublicKeyAlgorithm()", error);
	}
	let responsePublicKey = void 0;
	if (typeof response.getPublicKey === "function") try {
		const _publicKey = response.getPublicKey();
		if (_publicKey !== null) responsePublicKey = bufferToBase64URLString(_publicKey);
	} catch (error) {
		warnOnBrokenImplementation("getPublicKey()", error);
	}
	let responseAuthenticatorData;
	if (typeof response.getAuthenticatorData === "function") try {
		responseAuthenticatorData = bufferToBase64URLString(response.getAuthenticatorData());
	} catch (error) {
		warnOnBrokenImplementation("getAuthenticatorData()", error);
	}
	return {
		id,
		rawId: bufferToBase64URLString(rawId),
		response: {
			attestationObject: bufferToBase64URLString(response.attestationObject),
			clientDataJSON: bufferToBase64URLString(response.clientDataJSON),
			transports,
			publicKeyAlgorithm: responsePublicKeyAlgorithm,
			publicKey: responsePublicKey,
			authenticatorData: responseAuthenticatorData
		},
		type,
		clientExtensionResults: credential.getClientExtensionResults(),
		authenticatorAttachment: toAuthenticatorAttachment(credential.authenticatorAttachment)
	};
}
/**
* Visibly warn when we detect an issue related to a passkey provider intercepting WebAuthn API
* calls
*/
function warnOnBrokenImplementation(methodName, cause) {
	console.warn(`The browser extension that intercepted this WebAuthn API call incorrectly implemented ${methodName}. You should report this error to them.\n`, cause);
}
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/helpers/browserSupportsWebAuthnAutofill.js
/**
* Determine if the browser supports conditional UI, so that WebAuthn credentials can
* be shown to the user in the browser's typical password autofill popup.
*/
function browserSupportsWebAuthnAutofill() {
	if (!browserSupportsWebAuthn()) return _browserSupportsWebAuthnAutofillInternals.stubThis(new Promise((resolve) => resolve(false)));
	/**
	* I don't like the `as unknown` here but there's a `declare var PublicKeyCredential` in
	* TS' DOM lib that's making it difficult for me to just go `as PublicKeyCredentialFuture` as I
	* want. I think I'm fine with this for now since it's _supposed_ to be temporary, until TS types
	* have a chance to catch up.
	*/
	const globalPublicKeyCredential = globalThis.PublicKeyCredential;
	if (globalPublicKeyCredential?.isConditionalMediationAvailable === void 0) return _browserSupportsWebAuthnAutofillInternals.stubThis(new Promise((resolve) => resolve(false)));
	return _browserSupportsWebAuthnAutofillInternals.stubThis(globalPublicKeyCredential.isConditionalMediationAvailable());
}
var _browserSupportsWebAuthnAutofillInternals = { stubThis: (value) => value };
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/helpers/identifyAuthenticationError.js
/**
* Attempt to intuit _why_ an error was raised after calling `navigator.credentials.get()`
*/
function identifyAuthenticationError({ error, options }) {
	const { publicKey } = options;
	if (!publicKey) throw Error("options was missing required publicKey property");
	if (error.name === "AbortError") {
		if (options.signal instanceof AbortSignal) return new WebAuthnError({
			message: "Authentication ceremony was sent an abort signal",
			code: "ERROR_CEREMONY_ABORTED",
			cause: error
		});
	} else if (error.name === "NotAllowedError")
 /**
	* Pass the error directly through. Platforms are overloading this error beyond what the spec
	* defines and we don't want to overwrite potentially useful error messages.
	*/
	return new WebAuthnError({
		message: error.message,
		code: "ERROR_PASSTHROUGH_SEE_CAUSE_PROPERTY",
		cause: error
	});
	else if (error.name === "SecurityError") {
		const effectiveDomain = globalThis.location.hostname;
		if (!isValidDomain(effectiveDomain)) return new WebAuthnError({
			message: `${globalThis.location.hostname} is an invalid domain`,
			code: "ERROR_INVALID_DOMAIN",
			cause: error
		});
		else if (publicKey.rpId !== effectiveDomain) return new WebAuthnError({
			message: `The RP ID "${publicKey.rpId}" is invalid for this domain`,
			code: "ERROR_INVALID_RP_ID",
			cause: error
		});
	} else if (error.name === "UnknownError") return new WebAuthnError({
		message: "The authenticator was unable to process the specified options, or could not create a new assertion signature",
		code: "ERROR_AUTHENTICATOR_GENERAL_ERROR",
		cause: error
	});
	return error;
}
//#endregion
//#region node_modules/@simplewebauthn/browser/esm/methods/startAuthentication.js
/**
* Begin authenticator "login" via WebAuthn assertion
*
* @param optionsJSON Output from **@simplewebauthn/server**'s `generateAuthenticationOptions()`
* @param useBrowserAutofill (Optional) Initialize conditional UI to enable logging in via browser autofill prompts. Defaults to `false`.
* @param verifyBrowserAutofillInput (Optional) Ensure a suitable `<input>` element is present when `useBrowserAutofill` is `true`. Defaults to `true`.
*/
async function startAuthentication(options) {
	if (!options.optionsJSON && options.challenge) {
		console.warn("startAuthentication() was not called correctly. It will try to continue with the provided options, but this call should be refactored to use the expected call structure instead. See https://simplewebauthn.dev/docs/packages/browser#typeerror-cannot-read-properties-of-undefined-reading-challenge for more information.");
		options = { optionsJSON: options };
	}
	const { optionsJSON, useBrowserAutofill = false, verifyBrowserAutofillInput = true } = options;
	if (!browserSupportsWebAuthn()) throw new Error("WebAuthn is not supported in this browser");
	let allowCredentials;
	if (optionsJSON.allowCredentials?.length !== 0) allowCredentials = optionsJSON.allowCredentials?.map(toPublicKeyCredentialDescriptor);
	const publicKey = {
		...optionsJSON,
		challenge: base64URLStringToBuffer(optionsJSON.challenge),
		allowCredentials
	};
	const getOptions = {};
	/**
	* Set up the page to prompt the user to select a credential for authentication via the browser's
	* input autofill mechanism.
	*/
	if (useBrowserAutofill) {
		if (!await browserSupportsWebAuthnAutofill()) throw Error("Browser does not support WebAuthn autofill");
		if (document.querySelectorAll("input[autocomplete$='webauthn']").length < 1 && verifyBrowserAutofillInput) throw Error("No <input> with \"webauthn\" as the only or last value in its `autocomplete` attribute was detected");
		getOptions.mediation = "conditional";
		publicKey.allowCredentials = [];
	}
	getOptions.publicKey = publicKey;
	getOptions.signal = WebAuthnAbortService.createNewAbortSignal();
	let credential;
	try {
		credential = await navigator.credentials.get(getOptions);
	} catch (err) {
		throw identifyAuthenticationError({
			error: err,
			options: getOptions
		});
	}
	if (!credential) throw new Error("Authentication was not completed");
	const { id, rawId, response, type } = credential;
	let userHandle = void 0;
	if (response.userHandle) userHandle = bufferToBase64URLString(response.userHandle);
	return {
		id,
		rawId: bufferToBase64URLString(rawId),
		response: {
			authenticatorData: bufferToBase64URLString(response.authenticatorData),
			clientDataJSON: bufferToBase64URLString(response.clientDataJSON),
			signature: bufferToBase64URLString(response.signature),
			userHandle
		},
		type,
		clientExtensionResults: credential.getClientExtensionResults(),
		authenticatorAttachment: toAuthenticatorAttachment(credential.authenticatorAttachment)
	};
}
//#endregion
export { browserSupportsWebAuthn as i, startRegistration as n, WebAuthnAbortService as r, startAuthentication as t };
