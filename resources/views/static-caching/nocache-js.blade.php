{{--
Hydrates `nocache` regions in full-measure statically cached pages by fetching
their rendered contents. Injected inline by default, or served from a dedicated
route when static_caching.script_delivery is "external". Blade data: $nocacheUrl
--}}
(function() {
    function createMap() {
        var map = {};
        var els = document.getElementsByClassName('nocache');
        for (var i = 0; i < els.length; i++) {
            var section = els[i].getAttribute('data-nocache');
            map[section] = els[i];
        }
        return map;
    }

    function replaceElement(el, html) {
        const tmp = document.createElement('div');
        const fragment = document.createDocumentFragment();

        tmp.setHTMLUnsafe(html);

        while (tmp.firstChild) {
            fragment.appendChild(tmp.firstChild);
        }

        el.replaceWith(fragment);
    }

    var map = createMap();

    fetch('{{ $nocacheUrl }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            url: window.location.href.split('#')[0],
            sections: Object.keys(map)
        })
    })
    .then((response) => response.json())
    .then((data) => {
        map = createMap();

        const regions = data.regions;
        for (var key in regions) {
            if (map[key]) replaceElement(map[key], regions[key]);
        }

        document.dispatchEvent(new CustomEvent('statamic:nocache.replaced', { detail: data }));
    });
})();
