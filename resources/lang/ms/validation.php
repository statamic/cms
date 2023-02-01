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

    'accepted'             => 'Mesti diterima.',
    'active_url'           => 'Ini bukan URL yang sah.',
    'after'                => 'Mesti tarikh selepas :date.',
    'after_or_equal'       => 'Mestilah tarikh selepas atau sama dengan :date.',
    'alpha'                => 'Hanya boleh mengandungi huruf.',
    'alpha_dash'           => 'Hanya boleh mengandungi huruf, nombor, sempang dan garis bawah.',
    'alpha_num'            => 'Hanya boleh mengandungi huruf dan nombor.',
    'array'                => 'Mestilah array.',
    'before'               => 'Mesti tarikh sebelum :date.',
    'before_or_equal'      => 'Mestilah tarikh sebelum atau sama dengan :date.',
    'between'              => [
        'numeric' => 'Mesti antara :min dan :max.',
        'file'    => 'Mesti antara :min dan :max kilobait.',
        'string'  => 'Mesti antara :min dan :max aksara.',
        'array'   => 'Mesti ada antara :min dan :max item.',
    ],
    'boolean'              => 'Mesti betul atau salah.',
    'confirmed'            => 'Pengesahan tidak sepadan dengan e-mel',
    'current_password'     => 'Katalaluan anda adalah salah.',
    'date'                 => 'Bukan tarikh yang sah.',
    'date_format'          => 'Tidak sepadan dengan format :format.',
    'different'            => 'Bidang ini dan :other mestilah berbeza.',
    'digits'               => 'Mestilah :digits digit.',
    'digits_between'       => 'Mesti antara :min dan :max digit.',
    'dimensions'           => 'Dimensi imej tidak sah.',
    'distinct'             => 'Bidang ini mempunyai nilai pendua.',
    'email'                => 'Mesti alamat e-mel yang sah.',
    'exists'               => 'Ini tidak sah.',
    'file'                 => 'Mestilah fail.',
    'filled'               => 'Mesti ada nilai.',
    'gt'                   => [
        'numeric' => 'Mesti lebih besar daripada :value.',
        'file'    => 'Mesti lebih besar daripada :value kilobait.',
        'string'  => 'Mesti lebih besar daripada aksara :value.',
        'array'   => 'Mesti mempunyai lebih daripada :value item.',
    ],
    'gte'                  => [
        'numeric' => 'Mesti lebih besar daripada atau sama dengan :value.',
        'file'    => 'Mesti lebih besar daripada atau sama dengan :value kilobait.',
        'string'  => 'Mesti lebih besar daripada atau sama dengan aksara :value.',
        'array'   => 'Mesti mempunyai :value item atau lebih.',
    ],
    'image'                => 'Mestilah gambar.',
    'in'                   => 'Ini tidak sah.',
    'in_array'             => 'Bidang ini tidak wujud dalam :other.',
    'integer'              => 'Mestilah integer.',
    'ip'                   => 'Mestilah alamat IP yang sah.',
    'ipv4'                 => 'Mestilah alamat IPv4 yang sah.',
    'ipv6'                 => 'Mestilah alamat IPv6 yang sah.',
    'json'                 => 'Mestilah rentetan JSON yang sah.',
    'lt'                   => [
        'numeric' => 'Mesti kurang daripada :value.',
        'file'    => 'Mesti kurang daripada :value kilobait.',
        'string'  => 'Mesti kurang daripada :value aksara.',
        'array'   => 'Mesti mempunyai kurang daripada :value item.',
    ],
    'lte'                  => [
        'numeric' => 'Mesti kurang daripada atau sama dengan :value.',
        'file'    => 'Mesti kurang daripada atau sama :value kilobait.',
        'string'  => 'Mesti kurang daripada atau sama dengan aksara :value.',
        'array'   => 'Tidak boleh mempunyai lebih daripada :value item.',
    ],
    'max'                  => [
        'numeric' => 'Tidak boleh lebih besar daripada :max.',
        'file'    => 'Tidak boleh lebih besar daripada :max kilobait.',
        'string'  => 'Tidak boleh lebih besar daripada :max aksara.',
        'array'   => 'Tidak boleh mempunyai lebih daripada :max item.',
    ],
    'mimes'                => 'Mestilah fail jenis: :values.',
    'mimetypes'            => 'Mestilah fail jenis: :values.',
    'min'                  => [
        'numeric' => 'Mesti sekurang-kurangnya :min.',
        'file'    => 'Mesti sekurang-kurangnya :min kilobait.',
        'string'  => 'Mesti sekurang-kurangnya :min aksara.',
        'array'   => 'Mesti mempunyai sekurang-kurangnya :min item.',
    ],
    'not_in'               => 'Ini tidak sah.',
    'not_regex'            => 'Format tidak sah.',
    'numeric'              => 'Mesti nombor.',
    'present'              => 'Mesti wujud.',
    'regex'                => 'Format tidak sah.',
    'required'             => 'Bidang ini diperlukan.',
    'required_if'          => 'Bidang ini diperlukan apabila :other ialah :value.',
    'required_unless'      => 'Bidang ini diperlukan melainkan :other berada dalam :values.',
    'required_with'        => 'Bidang ini diperlukan apabila :values ​​wujud.',
    'required_with_all'    => 'Bidang ini diperlukan apabila :values ​​wujud.',
    'required_without'     => 'Bidang ini diperlukan apabila :values ​​tidak ada.',
    'required_without_all' => 'Bidang ini diperlukan apabila tiada :values ​​wujud.',
    'same'                 => 'Bidang ini dan :other mesti sepadan.',
    'size'                 => [
        'numeric' => 'Mestilah :size.',
        'file'    => 'Mestilah :size kilobait.',
        'string'  => 'Mestilah :size aksara.',
        'array'   => 'Mesti mengandungi :size item.',
    ],
    'string'               => 'Mesti rentetan.',
    'timezone'             => 'Mesti zon yang sah.',
    'unique'               => 'Nilai ini telah pun diambil.',
    'uploaded'             => 'Gagal memuat naik.',
    'url'                  => 'Format tidak sah.',

    /*
    |--------------------------------------------------------------------------
    | Custom Statamic Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may validation messages for the custom rules provided by Statamic.
    |
    */

    'unique_entry_value' => 'Nilai ini telah pun diambil.',
    'unique_term_value' => 'Nilai ini telah pun diambil.',
    'unique_user_value' => 'Nilai ini telah pun diambil.',
    'duplicate_field_handle' => 'Bidang dengan pemegang :handle tidak boleh digunakan lebih daripada sekali.',
    'one_site_without_origin' => 'Sekurang-kurangnya satu tapak mestilah tidak mempunyai asal.',
    'origin_cannot_be_disabled' => 'Tidak boleh memilih asal yang dilumpuhkan.',
    'unique_uri' => 'URI ini telah pun diambil.',
    'duplicate_uri' => 'URI pendua :value',
    'reserved' => 'Ini adalah perkataan terpelihara.',

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
