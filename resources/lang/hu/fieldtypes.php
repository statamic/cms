<?php

return [
    'any' => [
        'config' => [
            'antlers' => 'Szeretné-e, hogy ezt a mezőt parsolja az Antlers?',
            'cast_booleans' => 'Az igaz és hamis értékű opciók logikai értékként kerülnek mentésre.',
            'default' => 'Állítsa be az alapértelmezett értéket.',
        ],
    ],
    'array' => [
        'config' => [
            'keys' => 'Állítsa be a tömbkulcsokat (változókat) és az opcionális címkéket.',
            'mode' => 'A dinamikus mód lehetővé teszi a felhasználó számára az adatok kezelését, míg a kulcsos mód nem.',
        ],
    ],
    'assets' => [
        'config' => [
            'allow_uploads' => 'Új fájlok feltöltésének engedélyezése.',
            'container' => 'Válassza ki, hogy melyik Médiatárolót használja ehhez a mezőhöz.',
            'folder' => 'A böngészés megkezdésekor megnyíló mappa.',
            'max_files' => 'A maximálisan kiválasztható fájlok száma.',
            'mode' => 'Válassza ki a kívánt elrendezési módot.',
            'restrict' => 'Megakadályozza, hogy a felhasználók más mappákba navigáljanak.',
            'show_filename' => 'Fájlnév megjelenítése az előnézeti kép mellett.',
        ],
    ],
    'bard' => [
        'config' => [
            'allow_source' => 'Engedélyezze a HTML forráskód megtekintését írás közben.',
            'always_show_set_button' => 'Engedélyezze, hogy mindig megjelenjen a „Szett hozzáadása” gomb.',
            'buttons' => 'Válassza ki, hogy mely gombok jelenjenek meg az eszköztárban.',
            'container' => 'Válassza ki, hogy melyik Médiatároló kerüljön használatra ennél a mezőnél.',
            'enable_input_rules' => 'Engedélyezi a Markdown-stílusú billentyűparancsokat a tartalom begépelésekor.',
            'enable_paste_rules' => 'Engedélyezi a Markdown-stílusú billentyűparancsokat a tartalom beillesztésekor.',
            'fullscreen' => 'Engedélyezze a teljes képernyős módot',
            'link_collections' => 'Az ezekből a gyűjteményekből származó bejegyzések lesznek elérhetőek. Ha ezt üresen hagyja, az összes bejegyzés elérhető lesz.',
            'link_noopener' => 'Állítsa be a `rel="noopener"` értéket az összes linken.',
            'link_noreferrer' => 'Állítsa be a `rel="noreferrer"` értéket az összes linken.',
            'reading_time' => 'A becsült olvasási idő megjelenítése a mező alján.',
            'save_html' => 'Mentse el a HTML-t a strukturált adatok helyett. Ez leegyszerűsíti, de korlátozza a tartalom fölötti kontroll mértékét.',
            'sets' => 'A Szettek konfigurálható mező-blokkok, amelyek bárhová beilleszthetők a Bard-tartalomba.',
            'target_blank' => 'Állítsa be a `target="_blank"` értéket az összes linken.',
            'toolbar_mode' => 'Válassza ki, melyik eszköztár-stílust részesíti előnyben.',
        ],
    ],
    'checkboxes' => [
        'config' => [
            'inline' => 'A jelölőnégyzetek megjelenítése egy sorban.',
            'options' => 'Állítsa be a tömb kulcsait és opcionális címkéit.',
        ],
    ],
    'code' => [
        'config' => [
            'indent_size' => 'Állítsa be a behúzás méretét (szóközökben).',
            'indent_type' => 'Állítsa be a behúzás típusát.',
            'key_map' => 'Válassza ki a kívánt billentyűparancs-készletet.',
            'mode' => 'Válasszon nyelvet a szintaxis kiemeléséhez.',
            'theme' => 'Válassza ki a kívánt témát.',
        ],
    ],
    'color' => [
        'config' => [
            'color_modes' => 'Válassza ki, hogy melyik színmódokat szeretne választási lehetőségként felkínálni.',
            'default_color_mode' => 'Állítsa be az előre kiválasztott színmódot.',
            'lock_opacity' => 'Letiltja az Alfa csúszkát, megakadályozva az átlátszóság (opacity) módosítását.',
            'swatches' => 'A listából kiválasztható színek előre definiálása.',
            'theme' => 'Válasszon a klasszikus és a mini (egyszerűbb) színválasztó közül.',
        ],
    ],
    'date' => [
        'config' => [
            'columns' => 'Egyszerre több hónap megjelenítése, sorokban és oszlopokban',
            'earliest_date' => 'Állítsa be a legkorábbi kiválasztható dátumot.',
            'format' => 'A dátum tárolásának formátuma, [PHP date format](https://www.php.net/manual/en/datetime.format.php) használatával.',
            'full_width' => 'Nyújtsa ki a naptárat, hogy a teljes szélességét felhasználja.',
            'inline' => 'Hagyja ki a legördülő beviteli mezőt, és egyből jelenítse meg a naptárat.',
            'mode' => 'Válasszon „Egyetlen” vagy „Tartomány” mód között (a „Tartomány” letiltja az időválasztót).',
            'rows' => 'Egyszerre több hónap megjelenítése, sorokban és oszlopokban',
            'time_enabled' => 'Az időpontválasztó (óra:perc) engedélyezése.',
            'time_required' => 'Pontos időpont (óra:perc) megadása szükséges _a dátum mellett_.',
        ],
    ],
    'entries' => [
        'config' => [
            'create' => 'Új bejegyzések létrehozásának engedélyezése.',
        ],
    ],
    'form' => [
        'config' => [
            'max_items' => 'A maximálisan kiválasztható űrlapok száma.',
        ],
    ],
    'grid' => [
        'config' => [
            'add_row' => 'Állítsa be a „Sor hozzáadása” gomb címkéjét.',
            'fields' => 'Minden mező a Rács oszlopává válik.',
            'max_rows' => 'Állítsa be a maximálisan létrehozható sorok számát.',
            'min_rows' => 'Állítsa be a minimálisan létrehozható sorok számát.',
            'mode' => 'Válassza ki a kívánt elrendezési stílust.',
            'reorderable' => 'Engedélyezze a sorok átrendezhetőségét.',
        ],
    ],
    'link' => [
        'config' => [
            'collections' => 'Ezekből a gyűjteményekből lesznek elérhetőek a bejegyzések. Ha ezt üresen hagyja, akkor elérhetővé válnak az irányítható gyűjtemények _(routable collections)_ bejegyzései.',
        ],
    ],
    'markdown' => [
        'config' => [
            'automatic_line_breaks' => 'Engedélyezi az automatikus sortörést.',
            'automatic_links' => 'Lehetővé teszi az URL-ek automatikus linkké alakítását.',
            'container' => 'Válassza ki, hogy melyik Médiatárolót használja ehhez a mezőhöz.',
            'escape_markup' => 'Escape-eli a HTML kódokat (pl. a `<div>` kódból `&lt;div&gt;` lesz).',
            'folder' => 'A böngészés megkezdésekor megnyíló mappa.',
            'parser' => 'Egyedi Markdown parser használata. Hagyja üresen az alapértelmezett Markdown parser használatához.',
            'restrict' => 'Megakadályozza, hogy a felhasználók más mappákba navigáljanak.',
            'smartypants' => 'Írásjelek automatikus formázása [SmartyPants](https://daringfireball.net/projects/smartypants/) segítségével. Automatikusan átalakítja az idézőjeleket, kötőjeleket stb.',
        ],
    ],
    'radio' => [
        'config' => [
            'inline' => 'A rádiógombok megjelenítése egy sorban.',
            'options' => 'Állítsa be a tömb kulcsait és opcionális címkéit.',
        ],
    ],
    'range' => [
        'config' => [
            'append' => 'Szöveg hozzáadása a csúszka végéhez (jobb oldalához).',
            'max' => 'A maximális, jobb szélső érték.',
            'min' => 'A minimális, bal szélső érték.',
            'prepend' => 'Szöveg hozzáadása a csúszka elejéhez (bal oldalához).',
            'step' => 'Az értékek közötti minimális érték.',
        ],
    ],
    'relationship' => [
        'config' => [
            'mode' => 'Válassza ki a kívánt felhasználói felület stílust.',
        ],
    ],
    'replicator' => [
        'config' => [
            'collapse' => [
                'accordion' => 'Egyszerre csak egy szett legyen nyitva',
                'disabled' => 'Alapértelmezés szerint az összes szett legyen kinyitva',
                'enabled' => 'Alapértelmezés szerint az összes szett legyen összecsukva',
            ],
            'max_sets' => 'A szettek maximális száma.',
        ],
    ],
    'select' => [
        'config' => [
            'clearable' => 'Engedélyezze az opciók kiválasztásának törlését.',
            'multiple' => 'Több opció kiválasztásának engedélyezése.',
            'options' => 'Állítsa be a kulcsokat és az opcionális címkéket.',
            'placeholder' => 'Placeholder szöveg beállítása.',
            'push_tags' => 'Újonnan létrehozott címkék hozzáadása az opciók listájához.',
            'searchable' => 'Engedélyezze a lehetséges opciók közötti keresést.',
            'taggable' => 'Új opciók hozzáadásának engedélyezése, az előre meghatározott opciókon felül',
        ],
    ],
    'template' => [
        'config' => [
            'hide_partials' => 'A részsablonok (partials) ritkán alkalmasak sablonként való használatra.',
        ],
    ],
    'terms' => [
        'config' => [
            'create' => 'Új kifejezések létrehozásának engedélyezése.',
        ],
    ],
    'text' => [
        'config' => [
            'append' => 'Szöveg hozzáadása a szövegbeviteli mező után (jobbra).',
            'character_limit' => 'Adja meg a maximálisan beírható karakterek számát.',
            'input_type' => 'Adja meg a HTML5 beviteli mező típusát _(input type)_.',
            'placeholder' => 'Placeholder szöveg beállítása.',
            'prepend' => 'Szöveg hozzáadása a szövegbeviteli mező elé (balra).',
        ],
    ],
];
