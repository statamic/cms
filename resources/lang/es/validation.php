<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Líneas de lenguaje de validación
    |--------------------------------------------------------------------------
    |
    | Las siguientes líneas de contienen los mensajes de error predeterminados
    | utilizados por la clase de validador. Algunas de estas reglas tienen
    | varias versiones, como las reglas del tamaño. Puedes modificarlos aquí.
    */

    'accepted'             => 'Debes aceptar esto',
    'active_url'           => 'Este no es un URL válido.',
    'after'                => 'Debe ser una fecha posterior a :date.',
    'after_or_equal'       => 'Debe ser una fecha posterior o igual a :date.',
    'alpha'                => 'Solo puede contener letras.',
    'alpha_dash'           => 'Solo puede contener letras, números y guiones medios o bajos.',
    'alpha_num'            => 'Solo puede contener letras y números.',
    'array'                => 'Debe ser un arreglo.',
    'before'               => 'Debe ser una fecha anterior a :date.',
    'before_or_equal'      => 'Debe ser una fecha anterior o igual a :date.',
    'between'              => [
        'numeric' => 'Debe estar entre :min y :max.',
        'file'    => 'Debe tener entre :min y :max kb.',
        'string'  => 'Debe tener entre :min y :max caracteres.',
        'array'   => 'Debe tener entre :min y :max elementos.',
    ],
    'boolean'              => 'Debe ser verdadero o falso.',
    'confirmed'            => 'La confirmación no coincide.',
    'current_password'     => 'La contraseña es incorrecta.',
    'date'                 => 'No es una fecha válida.',
    'date_format'          => 'No coincide con el formato :format.',
    'different'            => 'Este campo y :other deben ser diferentes.',
    'digits'               => 'Debe tener :digits dígitos.',
    'digits_between'       => 'Debe tener entre :min y :max dígitos.',
    'dimensions'           => 'Dimensiones de imagen inválidas',
    'distinct'             => 'Este campo tiene un valor duplicado.',
    'email'                => 'Debe ser una dirección de email válida.',
    'exists'               => 'Esto no es válido.',
    'file'                 => 'Debe ser un archivo.',
    'filled'               => 'No puede estar vacío.',
    'gt'                   => [
        'numeric' => 'Debe ser mayor a :value.',
        'file'    => 'Debe ser mayor a :value kb.',
        'string'  => 'Debe tener más de :value caracteres.',
        'array'   => 'Debe tener más de :value elementos.',
    ],
    'gte'                  => [
        'numeric' => 'Debe ser mayor o igual a :value.',
        'file'    => 'Debe ser mayor o igual a :value kb.',
        'string'  => 'Debe tener al menos :value catacteres.',
        'array'   => 'Debe tener al menos :value elementos.',
    ],

    'image'                => 'Debe ser una imagen.',
    'in'                   => 'Esto no es valido.',
    'in_array'             => 'Este campo no existe en :other.',
    'integer'              => 'Debe ser un número entero.',
    'ip'                   => 'Debe ser una dirección IP válida.',
    'ipv4'                 => 'Debe ser una dirección IPv4 válida.',
    'ipv6'                 => 'Debe ser una dirección IPv6 válida.',
    'json'                 => 'Debe ser una cadena de JSON válida.',
    'lt'                   => [
        'numeric' => 'Debe ser menor a :value.',
        'file'    => 'Debe ser menor a :value kb.',
        'string'  => 'Debe tener menos de :value caracteres.',
        'array'   => 'Debe tener menos de :value elementos.',
    ],
    'lte'                  => [
        'numeric' => 'Debe ser menor o igual a :value.',
        'file'    => 'Debe ser menor o igual a :value kb.',
        'string'  => 'Debe tener :value caracteres o menos.',
        'array'   => 'Debe tener :value elementos o menos.',
    ],
    'max'                  => [
        'numeric' => 'Debe ser menor a :max.',
        'file'    => 'Debe ser menor a :max kb.',
        'string'  => 'Debe tener menos de :max caracteres.',
        'array'   => 'Debe tener menos de :max elementos.',
    ],
    'mimes'                => 'Debe ser un archivo de tipo :values.',
    'mimetypes'            => 'Debe ser un archivo de tipo :values.',
    'min'                  => [
        'numeric' => 'Debe ser al menos :min.',
        'file'    => 'Debe pesar al menos :min kb.',
        'string'  => 'Debe ser de al menos :min caracteres.',
        'array'   => 'Debe tener al menos :min elementos.',
    ],
    'not_in'               => 'Esto no es valido.',
    'not_regex'            => 'El formato es inválido',
    'numeric'              => 'Tiene que ser un número.',
    'present'              => 'Debe estar presente.',
    'regex'                => 'El formato es inválido',
    'required'             => 'Este campo es obligatorio.',
    'required_if'          => 'Este campo es obligatorio cuando :other es :value.',
    'required_unless'      => 'Este campo es obligatorio a menos que :other esté en :values.',
    'required_with'        => 'Este campo es obligatorio cuando :values está presente.',
    'required_with_all'    => 'Este campo es obligatorio cuando :values está presente.',
    'required_without'     => 'Este campo es obligatorio cuando :values no está presente.',
    'required_without_all' => 'Este campo es obligatorio cuando :values no está presente.',
    'same'                 => 'Este campo y :other deben coincidir.',
    'size'                 => [
        'numeric' => 'Debe ser :size.',
        'file'    => 'Debe pesar :size kb.',
        'string'  => 'Debe tener :size caracteres.',
        'array'   => 'Debe tener :size elementos.',
    ],
    'string'               => 'Debe ser un texto.',
    'timezone'             => 'Debe ser una zona horaria válida.',
    'unique'               => 'Este valor ya ha sido tomado.',
    'uploaded'             => 'Error al cargar',
    'url'                  => 'El formato es inválido',

    /*
    |--------------------------------------------------------------------------
    | Líneas personalizadas de lenguaje para validación de Statamic
    |--------------------------------------------------------------------------
    |
    | Aquí puedes editar los mensajes de las reglas de validación de Statamic.
    |
    */

    'unique_entry_value'        => 'Este valor ya ha sido tomado.',
    'unique_term_value'         => 'Este valor ya ha sido tomado.',
    'unique_user_value'         => 'Este valor ya ha sido tomado.',
    'duplicate_field_handle'    => 'El campo con un identificador de :handle no se puede usar más de una vez.',
    'one_site_without_origin'   => 'Al menos un sitio no debe tener un origen.',
    'origin_cannot_be_disabled' => 'No se puede seleccionar un origen inhabilitado.',
    'unique_uri' => 'Este URI ya se ha tomado.',
    'duplicate_uri' => 'URI duplicada :value',
    'reserved' => 'Esta es una palabra reservada.',

    /*
    |--------------------------------------------------------------------------
    | Líneas de lenguaje de validación personalizadas
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar mensajes personalizados de validación usando
    | la convención "atributo.regla" para nombrar cada fila. Esto facilita
    | especificar una línea específica para alguna regla de un atributo.
    */

    'custom' => [
        'nombre-de-atributo' => [
            'nombre-de-regla' => 'mensaje-personalizado',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Atributos de validación personalizados
    |--------------------------------------------------------------------------
    |
    | Las siguientes líneas son para cambiar los nombres de atributos por
    | otros más legibles, como "Dirección de email" en vez de "email".
    | Esto simplemente hace los mensajes un poco más limpios.
    |
    */

    'attributes' => [],

];
