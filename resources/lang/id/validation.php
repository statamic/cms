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

    'accepted'             => 'Harus diterima.',
    'active_url'           => 'Ini bukan URL yang valid.',
    'after'                => 'Harus tanggal setelah :date.',
    'after_or_equal'       => 'Harus tanggal setelah atau sama dengan :date.',
    'alpha'                => 'Hanya boleh berisi huruf.',
    'alpha_dash'           => 'Hanya boleh berisi huruf, angka, tanda hubung, dan setrip bawah.',
    'alpha_num'            => 'Hanya boleh berisi huruf dan angka.',
    'array'                => 'Harus berupa array.',
    'before'               => 'Harus tanggal sebelum :date.',
    'before_or_equal'      => 'Harus tanggal sebelum atau sama dengan :date.',
    'between'              => [
        'numeric' => 'Harus diantara :min dan :max.',
        'file'    => 'Harus diantara :min and :max kilobyte.',
        'string'  => 'Harus diantara :min and :max karakter.',
        'array'   => 'Harus memiliki antara :min dan :max item.',
    ],
    'boolean'              => 'Harus benar atau salah.',
    'confirmed'            => 'Konfirmasi tidak cocok.',
    'date'                 => 'Bukan tanggal yang valid.',
    'date_format'          => 'Tidak sesuai dengan format :format.',
    'different'            => 'Bidang ini dan :other harus berbeda.',
    'digits'               => 'Harus :digits digit.',
    'digits_between'       => 'Harus diantara :min dan :max digit.',
    'dimensions'           => 'Dimensi gambar tidak valid.',
    'distinct'             => 'Bidang ini memiliki nilai duplikat.',
    'email'                => 'Harus alamat email yang valid.',
    'exists'               => 'Ini tidak valid.',
    'file'                 => 'Harus berupa file.',
    'filled'               => 'Harus punya nilai.',
    'gt'                   => [
        'numeric' => 'Harus lebih besar dari :value.',
        'file'    => 'Harus lebih besar dari :value kilobyte.',
        'string'  => 'Harus lebih besar dari :value karakter.',
        'array'   => 'Harus memiliki lebih dari :value item.',
    ],
    'gte'                  => [
        'numeric' => 'Harus lebih besar dari atau sama dengan :value.',
        'file'    => 'Harus lebih besar dari atau sama dengan :value kilobyte.',
        'string'  => 'Harus lebih besar dari atau sama dengan :value karakter.',
        'array'   => 'Harus memiliki :value item atau lebih.',
    ],
    'image'                => 'Harus berupa gambar.',
    'in'                   => 'Ini tidak valid.',
    'in_array'             => 'Bidang ini tidak ada di :other.',
    'integer'              => 'Harus berupa integer.',
    'ip'                   => 'Harus alamat IP yang valid.',
    'ipv4'                 => 'Harus alamat IPv4 yang valid.',
    'ipv6'                 => 'Harus alamat IPv6 yang valid.',
    'json'                 => 'Harus berupa string JSON yang valid.',
    'lt'                   => [
        'numeric' => 'Harus lebih kecil dari :value.',
        'file'    => 'Harus lebih kecil dari :value kilobyte.',
        'string'  => 'Harus lebih kecil dari :value karakter.',
        'array'   => 'Harus memiliki kurang dari :value item.',
    ],
    'lte'                  => [
        'numeric' => 'Harus lebih kecil dari atau sama dengan :value.',
        'file'    => 'Harus lebih kecil dari atau sama dengan :value kilobyte.',
        'string'  => 'Harus lebih kecil dari atau sama dengan :value karakter.',
        'array'   => 'Tidak boleh lebih dari :value item.',
    ],
    'max'                  => [
        'numeric' => 'Tidak boleh lebih dari :max.',
        'file'    => 'Tidak boleh lebih dari :max kilobyte.',
        'string'  => 'Tidak boleh lebih dari :max karakter.',
        'array'   => 'Tidak boleh lebih dari :max item.',
    ],
    'mimes'                => 'Harus berupa file dengan tipe: :values.',
    'mimetypes'            => 'Harus berupa file dengan tipe: :values.',
    'min'                  => [
        'numeric' => 'Minimal harus :min.',
        'file'    => 'Minimal harus :min kilobyte.',
        'string'  => 'Minimal harus :min karakter.',
        'array'   => 'Harus memiliki setidaknya :min item.',
    ],
    'not_in'               => 'Ini tidak valid.',
    'not_regex'            => 'Format tidak valid.',
    'numeric'              => 'Harus berupa angka.',
    'present'              => 'Harus ada.',
    'regex'                => 'Format tidak valid.',
    'required'             => 'Bidang ini harus diisi.',
    'required_if'          => 'Bidang ini harus diisi jika :other adalah :value.',
    'required_unless'      => 'Bidang ini harus diisi kecuali :other ada di :values.',
    'required_with'        => 'Bidang ini harus diisi jika :values ada.',
    'required_with_all'    => 'Bidang ini harus diisi jika :values ada.',
    'required_without'     => 'Bidang ini harus diisi jika :values tidak ada.',
    'required_without_all' => 'Bidang ini harus diisi jika tidak ada :values.',
    'same'                 => 'Bidang ini dan :other harus cocok.',
    'size'                 => [
        'numeric' => 'Harus :size.',
        'file'    => 'Harus :size kilobyte.',
        'string'  => 'Harus :size karakter.',
        'array'   => 'Harus berisi :size item.',
    ],
    'string'               => 'Harus berupa string.',
    'timezone'             => 'Harus zona yang valid.',
    'unique'               => 'Nilai ini sudah diambil.',
    'uploaded'             => 'Gagal mengunggah.',
    'url'                  => 'Format tidak valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Statamic Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may validation messages for the custom rules provided by Statamic.
    |
    */

    'unique_entry_value' => 'Nilai ini sudah diambil.',
    'unique_user_value' => 'Nilai ini sudah diambil.',
    'duplicate_field_handle' => 'Bidang dengan handle :handle tidak dapat digunakan lebih dari sekali.',
    'one_site_without_origin' => 'Setidaknya satu situs tidak boleh memiliki asal.',
    'origin_cannot_be_disabled' => 'Tidak dapat memilih asal yang dinonaktifkan.',

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
