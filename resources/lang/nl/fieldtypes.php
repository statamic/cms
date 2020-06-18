<?php

return [
    'array' => [
        'config' => [
            'keys' => 'Stel de array keys (variabelen) en optionele labels in.',
            'mode' => 'Dynamische modus geeft de gebruiker controle over de data terwijl keyed modus dat niet doet.',
        ],
    ],
    'assets' => [
        'config' => [
            'allow_uploads' => 'Nieuwe file-uploads toestaan',
            'container' => 'Kies welke asset-container je voor dit veld wilt gebruiken.',
            'folder' => 'De map waarin de gebruiker begint met bladeren.',
            'max_files' => 'Het maximaal aantal selecteerbare assets.',
            'mode' => 'Kies je voorkeur voor layoutstijl.',
            'restrict' => 'Voorkom dat gebruikers naar andere mapen kunnen bladeren.',
        ],
    ],
    'bard' => [
        'config' => [
            'allow_source' => 'Schakel de optie in dat gebruikers de HTML-broncode kunnen bekijken tijdens het schrijven.',
            'buttons' => 'Kies welke knoppen er in de toolbar zitten.',
            'container' => 'Kies welke asset-container er voor dit veld gebruikt moet worden.',
            'fullscreen' => 'Schakel de optie in dat mensen voleldige schermweergave kunnen gebruiken.',
            'link_noopener' => 'Gebruik `rel="noopener"` op alle links.',
            'link_noreferrer' => 'Gebruik `rel="norefferer"` op alle links.',
            'reading_time' => 'Toon de geschatte leestijd onderaan het veld.',
            'save_html' => 'Bewaar HTML in plaats van gestructureerde gegevens. Dit vereenvoudigt maar beperkt de controle over je template markup.',
            'sets' => 'Sets zijn configureerbare blokken met velden die overal in je Bard-inhoud kunnen worden toegevoegd.',
            'target_blank' => 'Stel `target="_blank"` in op alle links.',
            'toolbar_mode' => 'Kies welke toolbarstijl je voorkeur heeft.',
        ],
    ],
    'checkboxes' => [
        'config' => [
            'inline' => 'Toon de checkboxes in een rij.',
            'options' => 'Stel de array keys en hun optionele labels in',
        ],
    ],
    'code' => [
        'config' => [
            'indent_size' => 'Stel in hoevel indentatie je wilt (in spaties).',
            'indent_type' => 'Stel in welke indentatietype je voorkeur heeft.',
            'key_map' => 'Kies een voorkeursset met toetsenbord sneltoetsen.',
            'mode' => 'Kies de taal voor syntax highlighting.',
            'theme' => 'Kies je voorkeursthema.',
        ],
    ],
    'color' => [
        'config' => [
            'color_modes' => 'Kies uit welke kleurmodi je wilt kunnen kiezen.',
            'default_color_mode' => 'Stel de aanvankelijke geselecteerde kleurmodus in.',
            'lock_opacity' => 'Schakelt de transparantieslider uit zodat de ondoorzichtbaarheid niet kan worden aangepast.',
            'swatches' => 'Voorgedefinieerde kleuren die uit een lijst geselecteerd kunnen worden.',
            'theme' => 'Kies uit de klassieke of mini (simpele) kleurpicker.',
        ],
    ],
    'date' => [
        'config' => [
            'columns' => 'Toon meerdere maanden tegelijk, in rijen en kolommen.',
            'earliest_date' => 'Stel de vroegst selecteerbare datum in.',
            'format' => 'Formatteer optioneel de datumtekenreeks met [moment.js] (https://momentjs.com/docs/#/displaying/format/).',
            'full_width' => 'Maak de kalender zo groot als de beschikbare breedte.',
            'inline' => 'Sla de dropdown over en toon de kalender altijd.',
            'mode' => 'Kies tussen enkele of range modus (range schakelt de tijdpicker uit).',
            'rows' => 'Toon meerdere maanden per keer, in rijen en kolommen',
            'time_enabled' => 'Gebruik de tijdpicker.',
            'time_required' => 'Vereis tijd _samen met_ de datum.',
        ],
    ],
    'form' => [
        'config' => [
            'max_items' => 'Het maximum aantal selecteerbare formulieren.',
        ],
    ],
    'grid' => [
        'config' => [
            'add_row' => 'Het label van de "Rij toevoegen" knop.',
            'fields' => 'Elk veld wordt een kolom in de gridtabel.',
            'max_rows' => 'Stel een maximum in voor het aantal rijen dat kan worden aangemaakt.',
            'min_rows' => 'Stel een minimum in voor het aantal rijen dat moet worden aangemaakt.',
            'mode' => 'Kies je voorkeurslayoutstijl.',
            'reorderable' => 'Schakel in om het herordenen van rijen toe te staan.',
        ],
    ],
    'markdown' => [
        'config' => [
            'automatic_line_breaks' => 'Schakel in voor automatische regeleinden.',
            'automatic_links' => 'Schakel in voor het automatisch linken van URL\'s.',
            'container' => 'Kies welke asset-container voor dit veld gebruikt moet worden.',
            'escape_markup' => 'Escape inline HTML-markup (bijv: `<div>` wordt `&lt;div&gt;`).',
            'folder' => 'De map waarin je begint met bladeren.',
            'parser' => 'De naam van een custom Markdown-parser. Laat leeg voor de standaard parser.',
            'restrict' => 'Voorkom dat gebruikers naar andere mappen kunnen navigeren.',
            'smartypants' => 'Converteer rechte quotes naar curly quotes, dashes naar en/em-dashes en andere soorgelijke tekstransformaties.',
        ],
    ],
    'radio' => [
        'config' => [
            'inline' => 'Toon de radioknoppen in een rij.',
            'options' => 'Stel de array keys en hun optionele labels in.',
        ],
    ],
    'range' => [
        'config' => [
            'append' => 'Voeg tekst toe aan de het einde (rechterkant) van de slider.',
            'max' => 'Het maximum, de meest rechter waarde.',
            'min' => 'Het minimum, de meest linker waarde.',
            'prepend' => 'Voeg tekst toe aan het begin (linkerkant) van de slider.',
            'step' => 'De minimumafstand tussen waardes.',
        ],
    ],
    'select' => [
        'config' => [
            'cast_booleans' => 'Opties met de waarde true en false worden ogeslagen als booleans.',
            'clearable' => 'Schakel in om het deselecteren van een optie toe te staan.',
            'multiple' => 'Sta meerdere selecties toe',
            'options' => 'Stel de keys hun optionele labels in.',
            'placeholder' => 'Stel de standaard, niet selecteerbare, placeholdertekst in.',
            'push_tags' => 'Voeg nieuw aangemaakte tags toe aan de optie-lijst.',
            'searchable' => 'Sta zoeken door mogelijke opties toe.',
            'taggable' => 'Sta het toevoegen van nieuwe opties toe.',
        ],
    ],
    'template' => [
        'config' => [
            'hide_partials' => 'Partials zijn zelden bedoeld om als templates te worden gebruikt.',
        ],
    ],
    'text' => [
        'config' => [
            'append' => 'Voeg tekst toe aan het einde (rechterkant) van de tekstinput.',
            'character_limit' => 'Stel het maximaal aantal karakters in dat ingevoerd kan worden.',
            'input_type' => 'Stel de HTML5 input type in.',
            'placeholder' => 'Stel de standaard placeholdertekst in.',
            'prepend' => 'Voeg tekst toe aan het begin (linkerkant) van de tekstinput.',
        ],
    ],
];
