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

    'accepted'             => '必须接受。',
    'active_url'           => '这不是有效的URL。',
    'after'                => '必须为 :date 之后的日期。',
    'after_or_equal'       => '必须为 :date 或之后的日期。',
    'alpha'                => '只能包含字母。',
    'alpha_dash'           => '只能包含字母，数字，破折号和下划线。',
    'alpha_num'            => '只能包含字母和数字。',
    'array'                => '必须是一个数组。',
    'before'               => '必须为 :date 之前的日期。',
    'before_or_equal'      => '必须为 :date 或之前的日期。',
    'between'              => [
        'numeric' => '必须介于 :min 和 :max 之间。',
        'file'    => '必须介于 :min 和 :max Kb之间。',
        'string'  => '必须介于 :min 和 :max 个字符之间。',
        'array'   => '必须包含 :min 和 :max 之间的项目。',
    ],
    'boolean'              => '必须为真或假。',
    'confirmed'            => '确认不匹配。',
    'current_password'     => '密码错误。',
    'date'                 => '无效日期',
    'date_format'          => '与格式 :format 不匹配',
    'different'            => '此字段和 :other 必须不同。',
    'digits'               => '必须为 :digits 位数。',
    'digits_between'       => '必须介于 :min 和 :max 位数之间。',
    'dimensions'           => '无效的图像尺寸。',
    'distinct'             => '该字段具有重复值。',
    'email'                => '必须是有效的电子邮件地址。',
    'exists'               => '这是无效的。',
    'file'                 => '必须是一个文件。',
    'filled'               => '必须具有一个值。',
    'gt'                   => [
        'numeric' => '必须大于 :value。',
        'file'    => '必须大于 :value kb。',
        'string'  => '必须大于 :value 个字符。',
        'array'   => '必须包含多于 :value 项。',
    ],
    'gte'                  => [
        'numeric' => '必须大于或等于 :value。',
        'file'    => '必须大于或等于 :valuekb。',
        'string'  => '必须大于或等于 :value 个字符。',
        'array'   => '必须具有 :value 项或更多。',
    ],
    'image'                => '必须是一张图像',
    'in'                   => '这是无效的。',
    'in_array'             => '此字段在 :other 中不存在。',
    'integer'              => '必须为整数。',
    'ip'                   => '必须是有效的 IP 地址。',
    'ipv4'                 => '必须是有效的 IPv4 地址。',
    'ipv6'                 => '必须是有效的 IPv6 地址。',
    'json'                 => '必须是有效的 JSON 字符串。',
    'lt'                   => [
        'numeric' => '必须小于 :value。',
        'file'    => '必须小于 :value kb。',
        'string'  => '必须小于 :value 个字符。',
        'array'   => '必须小于 :value 项。',
    ],
    'lte'                  => [
        'numeric' => '必须小于或等于:value。',
        'file'    => '必须小于或等于:valueKb。',
        'string'  => '必须小于或等于:value个字符。',
        'array'   => '不得超过 :value 项。',
    ],
    'max'                  => [
        'numeric' => '不得大于 :max。',
        'file'    => '不得大于 :max Kb。',
        'string'  => '不得大于 :max 个字符。',
        'array'   => '最多只能包含 :max 项。',
    ],
    'mimes'                => '必须是以下类型的文件：:values。',
    'mimetypes'            => '必须是以下类型的文件：:values。',
    'min'                  => [
        'numeric' => '必须至少为 :min。',
        'file'    => '必须至少为 :minKb。',
        'string'  => '必须至少为 :min 个字符。',
        'array'   => '必须至少包含 :min 项。',
    ],
    'not_in'               => '这是无效的。',
    'not_regex'            => '格式无效。',
    'numeric'              => '必须是数字。',
    'present'              => '必须存在。',
    'regex'                => '格式无效。',
    'required'             => '此字段必填。',
    'required_if'          => '当 :other 为 :value 时此字段必填。',
    'required_unless'      => '除非 :other 在 :values 中，否则此字段必填。',
    'required_with'        => '当 :values 存在时，此字段必填。',
    'required_with_all'    => '当 :values 存在时，此字段必填。',
    'required_without'     => '当 :values 不存在时，此字段必填。',
    'required_without_all' => '当不存在 :values 时，此字段必填。',
    'same'                 => '此字段和 :other 必须匹配。',
    'size'                 => [
        'numeric' => '必须为 :size。',
        'file'    => '必须为 :sizekb。',
        'string'  => '必须为 :size 个字符。',
        'array'   => '必须包含 :size 项。',
    ],
    'string'               => '必须是一个字符串。',
    'timezone'             => '必须为有效区域。',
    'unique'               => '该值已被使用。',
    'uploaded'             => '上传失败。',
    'url'                  => '格式无效。',

    /*
    |--------------------------------------------------------------------------
    | Custom Statamic Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may validation messages for the custom rules provided by Statamic.
    |
    */

    'unique_entry_value' => '该值已被使用。',
    'unique_term_value' => '该值已被使用。',
    'unique_user_value' => '该值已被使用。',
    'duplicate_field_handle' => '句柄为 :handle 的字段不能多次使用。',
    'one_site_without_origin' => '至少必须有一个站点无来源。',
    'origin_cannot_be_disabled' => '无法选择一个禁用的来源。',
    'unique_uri' => '该 URI 已被使用。',
    'duplicate_uri' => '重复的 URI :value',
    'reserved' => '输入值为保留字',

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
