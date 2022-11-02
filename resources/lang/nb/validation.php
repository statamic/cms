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

    'accepted'             => 'Må aksepteres.',
    'active_url'           => 'Dette er ikke en gyldig URL.',
    'after'                => 'Må være en dato etter :date.',
    'after_or_equal'       => 'Må være en dato etter eller lik :date.',
    'alpha'                => 'Kan bare inneholde bokstaver.',
    'alpha_dash'           => 'Kan bare inneholde bokstaver, tall, bindestreker og understrekingstegn.',
    'alpha_num'            => 'Kan bare inneholde bokstaver og tall.',
    'array'                => 'Må være en matrise.',
    'before'               => 'Må være en dato før :date.',
    'before_or_equal'      => 'Må være en dato før eller lik :date.',
    'between'              => [
        'numeric' => 'Må være mellom :min og :max.',
        'file'    => 'Må være mellom :min og :max kilobyte.',
        'string'  => 'Må være mellom :min og :max tegn.',
        'array'   => 'Må ha mellom :min og :max elementer.',
    ],
    'boolean'              => 'Må være sann eller usann.',
    'confirmed'            => 'Bekreftelsen stemmer ikke overens.',
    'date'                 => 'Datoen er ugyldig.',
    'date_format'          => 'Stemmer ikke overens med formatet :format.',
    'different'            => 'Dette feltet og :other må være forskjellige.',
    'digits'               => 'Må være :digits sifre.',
    'digits_between'       => 'Må være mellom :min og :max sifre.',
    'dimensions'           => 'Ugyldige bildemål.',
    'distinct'             => 'Dette feltet har en duplisert verdi.',
    'email'                => 'Må være en gyldig e-postadresse.',
    'exists'               => 'Ugyldig.',
    'file'                 => 'Må være en fil.',
    'filled'               => 'Må ha en verdi.',
    'gt'                   => [
        'numeric' => 'Må være større enn :value.',
        'file'    => 'Må være større enn :value kilobyte.',
        'string'  => 'Må være større enn :value tegn.',
        'array'   => 'Må ha mer enn :value elementer.',
    ],
    'gte'                  => [
        'numeric' => 'Må være større enn eller lik :value.',
        'file'    => 'Må være større enn eller lik :value kilobyte.',
        'string'  => 'Må være over enn eller lik :value tegn.',
        'array'   => 'Må ha :value elementer eller mer.',
    ],
    'image'                => 'Må være et bilde.',
    'in'                   => 'Ugyldig.',
    'in_array'             => 'Dette feltet finnes ikke i :other.',
    'integer'              => 'Må være et heltall.',
    'ip'                   => 'Må være en gyldig IP-adresse.',
    'ipv4'                 => 'Må være en gyldig IPv4-adresse.',
    'ipv6'                 => 'Må være en gyldig IPv6-adresse.',
    'json'                 => 'Må være en gyldig JSON-streng.',
    'lt'                   => [
        'numeric' => 'Må være mindre enn :value.',
        'file'    => 'Må være mindre enn :value kilobyte.',
        'string'  => 'Må være mindre enn :value tegn.',
        'array'   => 'Må ha mindre enn :value elementer.',
    ],
    'lte'                  => [
        'numeric' => 'Må være mindre enn eller lik :value.',
        'file'    => 'Må være mindre enn eller lik :value kilobyte.',
        'string'  => 'Må være mindre enn eller lik :value tegn.',
        'array'   => 'Kan ikke ha mer enn :value elementer.',
    ],
    'max'                  => [
        'numeric' => 'Kan ikke være større enn :max.',
        'file'    => 'Kan ikke være større enn :max kilobyte.',
        'string'  => 'Kan ikke ha mer enn :max tegn.',
        'array'   => 'Kan ikke ha mer enn :max elementer.',
    ],
    'mimes'                => 'Må være en fil av typen: :values.',
    'mimetypes'            => 'Må være en fil av typen: :values.',
    'min'                  => [
        'numeric' => 'Må være minst :min.',
        'file'    => 'Må være minst :min kilobyte.',
        'string'  => 'Må være minst :min tegn.',
        'array'   => 'Må ha minst :min elementer.',
    ],
    'not_in'               => 'Ugyldig.',
    'not_regex'            => 'Formatet er ugyldig.',
    'numeric'              => 'Må være et tall.',
    'present'              => 'Må være til stede.',
    'regex'                => 'Formatet er ugyldig.',
    'required'             => 'Dette feltet er obligatorisk.',
    'required_if'          => 'Dette feltet er obligatorisk når :other er :value.',
    'required_unless'      => 'Dette feltet er obligatorisk med mindre :other er i :values.',
    'required_with'        => 'Dette feltet er obligatorisk når :values er til stede.',
    'required_with_all'    => 'Dette feltet er obligatorisk når :values er til stede.',
    'required_without'     => 'Dette feltet er obligatorisk når :values ikke er til stede.',
    'required_without_all' => 'Dette feltet er obligatorisk når ingen av :values er til stede.',
    'same'                 => 'Dette feltet og :other må være like.',
    'size'                 => [
        'numeric' => 'Må være :size.',
        'file'    => 'Må være :size kilobyte.',
        'string'  => 'Må være :size tegn.',
        'array'   => 'Må inneholde :size elementer.',
    ],
    'string'               => 'Må være en streng.',
    'timezone'             => 'Må være en gyldig sone.',
    'unique'               => 'Denne verdien er allerede brukt.',
    'uploaded'             => 'Opplasting mislyktes.',
    'url'                  => 'Formatet er ugyldig.',

    /*
    |--------------------------------------------------------------------------
    | Custom Statamic Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may validation messages for the custom rules provided by Statamic.
    |
    */

    'unique_entry_value' => 'Denne verdien er allerede brukt.',
    'unique_term_value' => 'Denne verdien er allerede brukt.',
    'unique_user_value' => 'Denne verdien er allerede brukt.',
    'duplicate_field_handle' => 'Felt med et håndtak på :handle kan kun brukes én gang.',
    'one_site_without_origin' => 'Minst ett nettsted må være uten opprinnelse.',
    'origin_cannot_be_disabled' => 'Kan ikke velge en deaktivert opprinnelse.',
    'unique_uri' => 'Denne URI-en er allerede brukt.',
    'duplicate_uri' => 'Duplikat-URI :value',
    'reserved' => 'Dette er et reservert ord.',

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
            'rule-name' => 'brukerdefinert melding',
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
