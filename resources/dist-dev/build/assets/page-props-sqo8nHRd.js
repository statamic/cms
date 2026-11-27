import "./vue.esm-bundler-T63OhLKP.js";
import { o as usePage } from "./index.esm-CUcrycdV.js";
// resources/js/composables/page-props.js
function useStatamicPageProps() {
	const page = usePage();
	return new Proxy({}, { get(target, prop) {
		return page.props._statamic?.[prop];
	} });
}
export { useStatamicPageProps as t };
