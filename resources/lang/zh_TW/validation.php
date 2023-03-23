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

    'accepted'             => '必須接受。',
    'active_url'           => '不是有效的 URL。',
    'after'                => '必須為 :date 之後的日期。',
    'after_or_equal'       => '必須為 :date 或其之後的日期。',
    'alpha'                => '只能包含字母。',
    'alpha_dash'           => '只能包含字母、數字、破折號、或底線。',
    'alpha_num'            => '只能包含字母與數字。',
    'array'                => '只能為陣列。',
    'before'               => '必須為 :date 之後的日期。',
    'before_or_equal'      => '必須為 :date 或其之後的日期。',
    'between'              => [
        'numeric' => '必須介於 :min 與 :max 之間。',
        'file'    => '必須介於 :min KB 至 :max KB。',
        'string'  => '必須介於 :min 個字元與 :max 個字元之間。',
        'array'   => '必須介於 :min 至 :max 個項目。',
    ],
    'boolean'              => '必須為 true 或 false。',
    'confirmed'            => '確認欄位不相符。',
    'current_password'     => '密码错误。',
    'date'                 => '不是有效的日期。',
    'date_format'          => '不符合格式 :format。',
    'different'            => '此欄位必須與 :other 不同',
    'digits'               => '必須為 :digits 位數。',
    'digits_between'       => '必須介於 :min 位數至 :max 位數.',
    'dimensions'           => '無效的圖片長寬。',
    'distinct'             => '此欄位有重複的值。',
    'email'                => '必須為有效的電子郵件位址。',
    'exists'               => '該值無效。',
    'file'                 => '必須為檔案。',
    'filled'               => '必須有值。',
    'gt'                   => [
        'numeric' => '必須大於 :value。',
        'file'    => '必須大於 :value KB。',
        'string'  => '必須大於 :value 個字元。',
        'array'   => '必須有多於 :value 個項目。',
    ],
    'gte'                  => [
        'numeric' => '必須大於或等於 :value。',
        'file'    => '必須大於或等於 :value KB。',
        'string'  => '必須大於或等於 :value 個字元。',
        'array'   => '必須有至少有 :value 個或更多項目。',
    ],
    'image'                => '必須為圖片。',
    'in'                   => '該值無效。',
    'in_array'             => '該欄位不包含在 :other 中。',
    'integer'              => '必須為整數。',
    'ip'                   => '必須為有效的 IP 位址。',
    'ipv4'                 => '必須為有效的 IPv4 位址。',
    'ipv6'                 => '必須為有效的 IPv6 位址。',
    'json'                 => '必須為有效的 JSON 字串。',
    'lt'                   => [
        'numeric' => '必須小於 :value。',
        'file'    => '必須小於 :value KB。',
        'string'  => '必須少於 :value 個字元。',
        'array'   => '必須少於 :value 個項目。',
    ],
    'lte'                  => [
        'numeric' => '必須小於或等於 :value。',
        'file'    => '必須小於或等於 :value KB。',
        'string'  => '必須少於或等於 :value 個字元。',
        'array'   => '最多只可有 :value 個或更少的項目。',
    ],
    'max'                  => [
        'numeric' => '不可大於 :max。',
        'file'    => '不可大於 :max KB。',
        'string'  => '不可大於 :max 個字元。',
        'array'   => '不可有多於 :max 個項目。',
    ],
    'mimes'                => '必須為檔案類型：:values。',
    'mimetypes'            => '必須為檔案類型：:values。',
    'min'                  => [
        'numeric' => '最少必須為 :min。',
        'file'    => '最少必須有 :min KB。',
        'string'  => '最少必須有 :min 個字元。',
        'array'   => '最少必須有 :min 個項目。',
    ],
    'not_in'               => '該值無效。',
    'not_regex'            => '格式無效。',
    'numeric'              => '必須為數字。',
    'present'              => '必須有該欄位。',
    'regex'                => '格式無效',
    'required'             => '該欄位為必填。',
    'required_if'          => '當 :other 為 :value 時，該欄位為必填。',
    'required_unless'      => '除非 :other 為 :value，否則該欄位為必填。',
    'required_with'        => '當有 :values 時，該欄位為必填。',
    'required_with_all'    => '當有 :values 時，該欄位為必填。',
    'required_without'     => '當沒有 :values 時，該欄位為必填。',
    'required_without_all' => '當都沒有 :values 時，該欄位為必填。',
    'same'                 => '該欄位必須與 :other 相同。',
    'size'                 => [
        'numeric' => '必須為 :size。',
        'file'    => '必須為 :size KB。',
        'string'  => '必須為 :size 個字元。',
        'array'   => '必須包含 :size 個項目。',
    ],
    'string'               => '必須為字串。',
    'timezone'             => '必須為有效的時區。',
    'unique'               => '該值已被使用。',
    'uploaded'             => '無法上傳。',
    'url'                  => '格式無效。',

    /*
    |--------------------------------------------------------------------------
    | Custom Statamic Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may validation messages for the custom rules provided by Statamic.
    |
    */

    'unique_entry_value' => '該值已被使用。',
    'unique_term_value' => '該值已被使用。',
    'unique_user_value' => '該值已被使用。',
    'duplicate_field_handle' => '控點為 :handle 的欄位無法被使用超過一次。',
    'one_site_without_origin' => '至少必須有一個無來源的網站。',
    'origin_cannot_be_disabled' => '無法選擇一個已禁用的來源。',
    'unique_uri' => '該 URI 已被使用。',
    'duplicate_uri' => '重複的 URI :value',
    'reserved' => '輸入值為保留字。',

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
            'rule-name' => 'custom-message',
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
