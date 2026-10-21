import "./vue.esm-bundler-DZ9hA_XK.js";
import { o as usePage } from "./index.esm-D7PRwS1l.js";
// resources/js/composables/page-props.js
function useStatamicPageProps() {
	const page = usePage();
	return new Proxy({}, { get(target, prop) {
		return page.props._statamic?.[prop];
	} });
}
export { useStatamicPageProps as t };
