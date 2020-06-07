<?php

return [
    'array' => [
        'config' => [
            'mode' => 'Der dynamische Modus gibt der Benutzer*in die Kontrolle über die Daten, während der geschlossene Modus dies nicht tut.',
            'keys' => 'Arrayschlüssel (Variablen) und optionale Beschriftungen festlegen.',
        ],
    ],
    'assets' => [
        'config' => [
            'allow_uploads' => 'Das Hochladen neuer Dateien zulassen.',
            'container' => 'Datei-Container für dieses Feld auswählen.',
            'folder' => 'In diesem Ordner soll das Durchsuchen der Dateien beginnen.',
            'max_files' => 'Die maximale Anzahl der auswählbaren Dateien.',
            'mode' => 'Bevorzugten Layoutstil auswählen.',
            'restrict' => 'Verhindern, dass Benutzer*innen zu anderen Ordnern navigieren können.',
        ],
    ],
    'bard' => [
        'config' => [
            'allow_source' => 'Option zum Anzeigen des HTML-Quellcodes während des Schreibens aktivieren.',
            'buttons' => 'Auswählen, welche Buttons in der Symbolleiste angezeigt werden sollen.',
            'container' => 'Datei-Container für dieses Feld auswählen.',
            'fullscreen' => 'Option zum Umschalten in den Vollbildmodus aktivieren.',
            'link_noopener' => 'Sämtliche Links mit `rel="noopener"` ausstatten.',
            'link_noreferrer' => 'Sämtliche Links mit `rel="noreferrer` ausstatten.',
            'reading_time' => 'Geschätzte Lesezeit am unteren Rand des Feldes anzeigen.',
            'save_html' => 'HTML anstelle von strukturierten Daten speichern. Dies vereinfacht zwar die Kontrolle über dein Template Markup, schränkt dieses aber ein.',
            'sets' => 'Sets sind anpassbare Blöcke mit Feldern, die an beliebiger Stelle in deinen Bard-Inhalt eingefügt werden können.',
            'target_blank' => 'Sämtliche Links mit `target="_blank` ausstatten.',
            'toolbar_mode' => 'Bevorzugten Stil der Symbolleiste auswählen.',
        ],
    ],
    'checkboxes' => [
        'config' => [
            'inline' => 'Checkboxen in einer Reihe anzeigen.',
            'options' => 'Arrayschlüssel und deren optionale Beschriftungen festlegen.',
        ],
    ],
    'code' => [
        'config' => [
            'indent_size' => 'Bevorzugte Einzugsgrösse (in Leerzeichen) festlegen.',
            'indent_type' => 'Bevorzugte Einzugsart festlegen.',
            'key_map' => 'Bevorzugten Satz der Tastaturkurzbefehle auswählen.',
            'mode' => 'Sprache für die Syntaxhervorhebung auswählen.',
            'theme' => 'Bevorzugtes Design auswählen.',
        ],
    ],
    'color' => [
        'config' => [
            'color_modes' => 'Festlegen, zwischen welchen Farbmodi du wählen kannst.',
            'default_color_mode' => 'Den vorgewählten Farbmodus festlegen.',
            'lock_opacity' => 'Deaktiviert den Alpha-Schieberegler, um Änderungen an der Deckkraft zu verhindern.',
            'swatches' => 'Farbfelder festlegen, die aus einer Liste ausgewählt werden können.',
            'theme' => 'Zur Auswahl stehen der klassische und der (einfachere) Mini-Farbwähler.',
        ],
    ],
    'date' => [
        'config' => [
            'columns' => 'Mehrere Monate gleichzeitig in verschiedenen Spalten anzeigen.',
            'earliest_date' => 'Frühestes auswählbares Datum festlegen.',
            'format' => 'Datumsangabe optional mit [moment.js](https://momentjs.com/docs/#/displaying/format/) formatieren.',
            'full_width' => 'Kalender dehnen, um die volle Breite auszunutzen.',
            'inline' => 'Dropdown Eingabefeld überspringen und den Kalender direkt anzeigen.',
            'mode' => 'Zwischen Einzel- oder Von/Bis-Modus auswählen (der Von/Bis-Modus deaktiviert die Zeiterfassung).',
            'rows' => 'Mehrere Monate gleichzeitig in verschiedenen Zeilen anzeigen.',
            'time_enabled' => 'Aktiviert den Timepicker.',
            'time_required' => 'Benötigt zusätzlich zum Datum eine Zeitangabe.',
        ],
    ],
    'form' => [
        'config' => [
            'max_items' => 'Maximale Anzahl auswählbarer Formulare.',
        ],
    ],
    'grid' => [
        'config' => [
            'add_row' => 'Beschriftung des Buttons «Zeile hinzufügen» festlegen.',
            'fields' => 'Im Tabellenmodus wird jedes Feld zu einer Spalte.',
            'max_rows' => 'Maximale Anzahl möglicher Zeilen festlegen.',
            'min_rows' => 'Minimale Anzahl möglicher Zeilen festlegen.',
            'mode' => 'Bevorzugten Layoutstil auswählen.',
            'reorderable' => 'Option zum Neuanordnen der Zeilen aktivieren.',
        ],
    ],
    'markdown' => [
        'config' => [
            'automatic_line_breaks' => 'Aktiviert automatische Zeilenumbrüche.',
            'automatic_links' => 'Aktiviert die automatische Verlinkung beliebiger URLs.',
            'container' => 'Datei-Container für dieses Feld auswählen.',
            'escape_markup' => 'Entfernt Inline-HTML-Auszeichnung (z.B. `<div>` -> `&lt;div&gt;`).',
            'folder' => 'In diesem Ordner soll das Durchsuchen der Dateien beginnen.',
            'parser' => 'Der Name eines benutzerdefinierten Markdown-Parser. Für die Standardeinstellung Feld leer lassen.',
            'restrict' => 'Verhindern, dass Benutzer*innen zu anderen Ordnern navigieren können.',
            'smartypants' => 'Automatische Konvertierung von geraden in geschweifte Anführungszeichen, Striche in en/em-Dashes und andere ähnliche Texttransformationen.',
        ],
    ],
    'radio' => [
        'config' => [
            'inline' => 'Radio-Buttons in einer Reihe anzeigen.',
            'options' => 'Arrayschlüssel und deren optionale Beschriftungen festlegen.',
        ],
    ],
    'range' => [
        'config' => [
            'append' => 'Text auf der rechten Seite des Schiebereglers hinzufügen.',
            'max' => 'Am weitesten rechts liegender maximaler Wert.',
            'min' => 'Am weitesten links liegender minimaler Wert.',
            'prepend' => 'Text auf der linke Seite des Schiebereglers hinzufügen.',
            'step' => 'Mindestgrösse zwischen den Werten.',
        ],
    ],
    'select' => [
        'config' => [
            'cast_booleans' => 'Optionen mit den Werten *true* und *false* als Booleans speichern.',
            'clearable' => 'Die Möglichkeit zum Abwählen der Auswahl aktivieren.',
            'multiple' => 'Mehrfachauswahl zulassen.',
            'options' => 'Arrayschlüssel und deren optionale Beschriftungen festlegen.',
            'placeholder' => 'Nicht anwählbaren Platzhaltertext festlegen.',
            'push_tags' => 'Neu erstellte Tags zur Optionsliste hinzufügen.',
            'searchable' => 'Die Suche durch verfügbare Optionen aktivieren.',
            'taggable' => 'Das Hinzufügen neuer Optionen zusätzlich zu den vordefinierten zulassen.',
        ],
    ],
    'template' => [
        'config' => [
            'hide_partials' => 'Partials sind selten als Templates vorgesehen.',
        ],
    ],
    'text' => [
        'config' => [
            'append' => 'Zusätzlichen Text rechts vom eigentlichen Text voranstellen.',
            'character_limit' => 'Maximale Anzahl der möglichen Zeichen festlegen.',
            'input_type' => 'HTML5 Inputtyp festlegen.',
            'placeholder' => 'Standard-Platzhaltertext festlegen.',
            'prepend' => 'Zusätzlichen Text links vom eigentlichen Text anhängen.',
        ],
    ],
    'textarea' => [
        'config' => [
            'character_limit' => 'Maximale Anzahl der möglichen Zeichen festlegen.',
        ],
    ],
    'relationship' => [
        'config' => [
            'mode' => 'Bevorzugten UI Stil auswählen.',
        ],
    ],
];
