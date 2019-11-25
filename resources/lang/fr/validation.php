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

    'accepted' => 'Doit être accepté.',
    'active_url' => "Ce n'est pas une URL valide.",
    'after' => 'Doit être une date après :date.',
    'after_or_equal' => 'Doit être une date après ou égale à :date.',
    'alpha' => 'Peut uniquement contenir des lettres.',
    'alpha_dash' => 'Peut uniquement contenir des lettres, des chiffres, des tirets et des traits de soulignement.',
    'alpha_num' => 'Peut uniquement contenir des lettres et des chiffres.',
    'array' => 'Doit être un tableau.',
    'before' => 'Doit être une date avant :date.',
    'before_or_equal' => 'Doit être une date antérieure ou égale à :date.',
    'between' => [
        'numeric' => 'Doit être compris entre :min et :max .',
        'file' => 'Doit être compris entre :min et :max kilo-octets.',
        'string' => 'Doit être compris entre :min et :max caractères.',
        'array' => 'Doit avoir entre :min et :max éléments.',
    ],
    'boolean' => 'Doit être vrai ou faux.',
    'confirmed' => 'La confirmation ne correspond pas.',
    'date' => 'Date non valide',
    'date_format' => 'Ne correspond pas au format :format.',
    'different' => 'Ce champ et :other doivent être différents.',
    'digits' => 'Doit faire :digits chiffres.',
    'digits_between' => 'Doit être compris entre :min et :max chiffres.',
    'dimensions' => "Dimensions de l'image non valides.",
    'distinct' => 'Ce champ a une valeur en double.',
    'email' => 'Doit être une adresse e-mail valide.',
    'exists' => 'Ceci est invalide.',
    'file' => 'Doit être un fichier.',
    'filled' => 'Doit avoir une valeur.',
    'gt' => [
        'numeric' => 'Doit être supérieur à :value .',
        'file' => 'Doit être supérieur à :value kilo-octets.',
        'string' => 'Doit faire plus de :value caractères.',
        'array' => 'Doit avoir plus de :value éléments.',
    ],
    'gte' => [
        'numeric' => 'Doit être supérieur ou égal à :value.',
        'file' => 'Doit être supérieur ou égal à :value kilo-octets.',
        'string' => 'Doit être supérieur ou égal à :value caractères.',
        'array' => 'Doit avoir :value éléments ou plus.',
    ],
    'image' => 'Doit être une image.',
    'in' => 'Ceci est invalide.',
    'in_array' => "Ce champ n'existe pas dans :other.",
    'integer' => 'Doit être un entier.',
    'ip' => 'Doit être une adresse IP valide.',
    'ipv4' => 'Doit être une adresse IPv4 valide.',
    'ipv6' => 'Doit être une adresse IPv6 valide.',
    'json' => 'Doit être une chaîne JSON valide.',
    'lt' => [
        'numeric' => 'Doit être inférieur à :value.',
        'file' => 'Doit être inférieur à :value kilo-octets.',
        'string' => 'Doit faire moins de :value caractères.',
        'array' => 'Doit avoir moins de :value éléments.',
    ],
    'lte' => [
        'numeric' => 'Doit être inférieur ou égal à :value.',
        'file' => 'Doit être inférieur ou égal à :value kilo-octets.',
        'string' => 'Doit faire :value caractères ou moins.',
        'array' => 'Ne doit pas avoir plus de :value éléments.',
    ],
    'max' => [
        'numeric' => 'Ne peut pas être supérieur à :max.',
        'file' => 'Ne peut pas être supérieur à :max kilo-octets.',
        'string' => 'Ne peut pas être supérieur à :max caractères.',
        'array' => 'Ne peut pas avoir plus de :max éléments.',
    ],
    'mimes' => 'Doit être un fichier de type :values.',
    'mimetypes' => 'Doit être un fichier de type :values.',
    'min' => [
        'numeric' => 'Doit faire au moins :min.',
        'file' => 'Doit faire au moins :min kilo-octets.',
        'string' => 'Doit faire au moins :min caractères.',
        'array' => 'Doit avoir au moins :min éléments.',
    ],
    'not_in' => 'Ceci est invalide.',
    'not_regex' => 'Le format est invalide.',
    'numeric' => 'Doit être un nombre.',
    'present' => 'Doit être présent.',
    'regex' => 'Le format est invalide.',
    'required' => 'Ce champ est obligatoire.',
    'required_if' => 'Ce champ est obligatoire lorsque :other est :value.',
    'required_unless' => 'Ce champ est obligatoire sauf si :other est dans :values.',
    'required_with' => 'Ce champ est requis lorsque :values est présent.',
    'required_with_all' => 'Ce champ est requis lorsque :values est présent.',
    'required_without' => "Ce champ est obligatoire lorsque :values n'est pas présente.",
    'required_without_all' => "Ce champ est obligatoire lorsqu'aucune des :values n'est présente.",
    'same' => 'Ce champ et :other doivent correspondre.',
    'size' => [
        'numeric' => 'Doit faire :size .',
        'file' => 'Doit faire :size kilo-octets.',
        'string' => 'Doit faire :size caractères.',
        'array' => 'Doit contenir :size éléments.',
    ],
    'string' => 'Doit être une chaîne.',
    'timezone' => 'Doit être une zone valide.',
    'unique' => 'Cette valeur a déjà été prise.',
    'uploaded' => 'Échec du téléchargement.',
    'url' => 'Le format est invalide.',

    /*
    |--------------------------------------------------------------------------
    | Custom Statamic Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may validation messages for the custom rules provided by Statamic.
    |
    */

    'unique_entry_value' => 'Cette valeur a déjà été prise.',
    'unique_user_value' => 'Cette valeur a déjà été prise.',

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

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'message personnalisé',
        ],
    ],

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
