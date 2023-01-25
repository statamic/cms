<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Måste accepteras.',
    'active_url' => 'Detta är inte en giltig URL.',
    'after' => 'Måste vara ett datum efter :date .',
    'after_or_equal' => 'Måste vara ett datum efter eller lika med :date .',
    'alpha' => 'Får endast innehålla bokstäver.',
    'alpha_dash' => 'Får endast innehålla bokstäver, siffror, bindestreck och understreck.',
    'alpha_num' => 'Får endast innehålla bokstäver och siffror.',
    'array' => 'Måste vara en array.',
    'before' => 'Måste vara ett datum före :date .',
    'before_or_equal' => 'Måste vara ett datum före eller lika med :date .',
    'between.numeric' => 'Måste vara mellan :min och :max .',
    'between.file' => 'Måste vara mellan :min och :max kilobyte.',
    'between.string' => 'Måste vara mellan :min och :max tecken.',
    'between.array' => 'Måste ha mellan :min och :max artiklar.',
    'boolean' => 'Måste vara sant eller falskt.',
    'confirmed' => 'Bekräftelsen stämmer inte.',
    'current_password' => 'Lösenordet är felaktigt.',
    'date' => 'Inte ett giltigt datum.',
    'date_format' => 'Matchar inte formatet :format .',
    'different' => 'Detta fält och :other måste vara olika.',
    'digits' => 'Måste vara :digits siffror.',
    'digits_between' => 'Måste vara mellan :min och :max siffror.',
    'dimensions' => 'Ogiltiga bildmått.',
    'distinct' => 'Det här fältet har ett dubblettvärde.',
    'email' => 'Måste vara en giltig e-postadress.',
    'exists' => 'Detta är ogiltigt.',
    'file' => 'Måste vara en fil.',
    'filled' => 'Måste ha ett värde.',
    'gt.numeric' => 'Måste vara större än :value .',
    'gt.file' => 'Måste vara större än :value kilobyte.',
    'gt.string' => 'Måste vara större än :value tecken.',
    'gt.array' => 'Måste ha mer än :value .',
    'gte.numeric' => 'Måste vara större än eller lika med :value .',
    'gte.file' => 'Måste vara större än eller lika med :value kilobyte.',
    'gte.string' => 'Måste vara större än eller lika med :value tecken.',
    'gte.array' => 'Måste ha :value eller mer.',
    'image' => 'Måste vara en bild.',
    'in' => 'Detta är ogiltigt.',
    'in_array' => 'Det här fältet finns inte i :other .',
    'integer' => 'Måste vara ett heltal.',
    'ip' => 'Måste vara en giltig IP-adress.',
    'ipv4' => 'Måste vara en giltig IPv4-adress.',
    'ipv6' => 'Måste vara en giltig IPv6-adress.',
    'json' => 'Måste vara en giltig JSON-sträng.',
    'lt.numeric' => 'Måste vara mindre än :value .',
    'lt.file' => 'Måste vara mindre än :value kilobyte.',
    'lt.string' => 'Måste vara mindre än :value tecken.',
    'lt.array' => 'Måste ha mindre än :value objekt.',
    'lte.numeric' => 'Måste vara mindre än eller lika med :value .',
    'lte.file' => 'Måste vara mindre än eller lika med :value kilobyte.',
    'lte.string' => 'Måste vara mindre än eller lika med :value tecken.',
    'lte.array' => 'Får inte ha mer än :value objekt.',
    'max.numeric' => 'Får inte vara större än :max .',
    'max.file' => 'Får inte vara större än :max kilobyte.',
    'max.string' => 'Får inte vara större än :max tecken.',
    'max.array' => 'Får inte ha fler än :max artiklar.',
    'mimes' => 'Måste vara en fil av typen: :values .',
    'mimetypes' => 'Måste vara en fil av typen: :values .',
    'min.numeric' => 'Måste vara minst :min .',
    'min.file' => 'Måste vara minst :min kilobyte.',
    'min.string' => 'Måste vara minst :min tecken.',
    'min.array' => 'Måste ha minst :min objekt.',
    'not_in' => 'Detta är ogiltigt.',
    'not_regex' => 'Formatet är ogiltigt.',
    'numeric' => 'Måste vara en siffra.',
    'present' => 'Måste vara närvarande.',
    'regex' => 'Formatet är ogiltigt.',
    'required' => 'Detta fält är obligatoriskt.',
    'required_if' => 'Detta fält är obligatoriskt när :other är :value .',
    'required_unless' => 'Detta fält är obligatoriskt om inte :other finns i :values .',
    'required_with' => 'Detta fält är obligatoriskt när :values finns.',
    'required_with_all' => 'Detta fält är obligatoriskt när :values finns.',
    'required_without' => 'Detta fält är obligatoriskt när :values inte finns.',
    'required_without_all' => 'Detta fält är obligatoriskt när inget av :values finns.',
    'same' => 'Detta fält och :other måste matcha.',
    'size.numeric' => 'Måste vara :size .',
    'size.file' => 'Måste vara :size kilobyte.',
    'size.string' => 'Måste vara :size tecken.',
    'size.array' => 'Måste innehålla :size objekt.',
    'string' => 'Måste vara ett snöre.',
    'timezone' => 'Måste vara en giltig zon.',
    'unique' => 'Detta värde har redan tagits.',
    'uploaded' => 'Det gick inte att ladda upp.',
    'url' => 'Formatet är ogiltigt.',

    /*
    |--------------------------------------------------------------------------
    | Custom Statamic Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may validation messages for the custom rules provided by Statamic.
    |
    */

    'unique_entry_value' => 'Detta värde har redan tagits.',
    'unique_term_value' => 'Detta värde har redan tagits.',
    'unique_user_value' => 'Detta värde har redan tagits.',
    'duplicate_field_handle' => 'Fält med ett handtag på :handle kan inte användas mer än en gång.',
    'one_site_without_origin' => 'Minst en webbplats får inte ha ett ursprung.',
    'origin_cannot_be_disabled' => 'Det går inte att välja ett inaktiverat ursprung.',
    'unique_uri' => 'Denna URI har redan tagits.',
    'duplicate_uri' => 'Dubblett URI :value',
    'reserved' => 'Detta är ett reserverat ord.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom.attribute-name.rule-name' => 'anpassat meddelande',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],
];
