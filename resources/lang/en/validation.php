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

    'accepted' => 'Must be accepted.',
    'accepted_if' => 'Must be accepted when :other is :value.',
    'active_url' => 'This is not a valid URL.',
    'after' => 'Must be a date after :date.',
    'after_or_equal' => 'Must be a date after or equal to :date.',
    'alpha' => 'May only contain letters.',
    'alpha_dash' => 'May only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'May only contain letters and numbers.',
    'array' => 'Must be an array.',
    'ascii' => 'Must only contain single-byte alphanumeric characters and symbols.',
    'before' => 'Must be a date before :date.',
    'before_or_equal' => 'Must be a date before or equal to :date.',
    'between' => [
        'array' => 'Must have between :min and :max items.',
        'file' => 'Must be between :min and :max kilobytes.',
        'numeric' => 'Must be between :min and :max.',
        'string' => 'Must be between :min and :max characters.',
    ],
    'boolean' => 'Must be true or false.',
    'can' => 'Contains an unauthorized value.',
    'confirmed' => 'Confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'Not a valid date.',
    'date_equals' => 'Must be a date equal to :date.',
    'date_format' => 'Does not match the format :format.',
    'decimal' => 'Must have :decimal decimal places.',
    'declined' => 'Must be declined.',
    'declined_if' => 'Must be declined when :other is :value.',
    'different' => 'This field and :other must be different.',
    'digits' => 'Must be :digits digits.',
    'digits_between' => 'Must be between :min and :max digits.',
    'dimensions' => 'Invalid image dimensions.',
    'distinct' => 'This field has a duplicate value.',
    'doesnt_end_with' => 'Must not end with one of the following: :values.',
    'doesnt_start_with' => 'Must not start with one of the following: :values.',
    'email' => 'Must be a valid email address.',
    'ends_with' => 'Must end with :values',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'This is invalid.',
    'file' => 'Must be a file.',
    'filled' => 'Must have a value.',
    'gt' => [
        'array' => 'Must have more than :value items.',
        'file' => 'Must be greater than :value kilobytes.',
        'numeric' => 'Must be greater than :value.',
        'string' => 'Must be greater than :value characters.',
    ],
    'gte' => [
        'numeric' => 'Must be greater than or equal :value.',
        'file' => 'Must be greater than or equal :value kilobytes.',
        'string' => 'Must be greater than or equal :value characters.',
        'array' => 'Must have :value items or more.',
    ],
    'image' => 'Must be an image.',
    'in' => 'This is invalid.',
    'in_array' => 'This field does not exist in :other.',
    'integer' => 'Must be an integer.',
    'ip' => 'Must be a valid IP address.',
    'ipv4' => 'Must be a valid IPv4 address.',
    'ipv6' => 'Must be a valid IPv6 address.',
    'json' => 'Must be a valid JSON string.',
    'lowercase' => 'Must be lowercase.',
    'lt' => [
        'numeric' => 'Must be less than :value.',
        'file' => 'Must be less than :value kilobytes.',
        'string' => 'Must be less than :value characters.',
        'array' => 'Must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'Must be less than or equal :value.',
        'file' => 'Must be less than or equal :value kilobytes.',
        'string' => 'Must be less than or equal :value characters.',
        'array' => 'Must not have more than :value items.',
    ],
    'mac_address' => 'Must be a valid MAC address.',
    'max' => [
        'numeric' => 'May not be greater than :max.',
        'file' => 'May not be greater than :max kilobytes.',
        'string' => 'May not be greater than :max characters.',
        'array' => 'May not have more than :max items.',
    ],
    'max_digits' => 'Must not have more than :max digits.',
    'mimes' => 'Must be a file of type: :values.',
    'mimetypes' => 'Must be a file of type: :values.',
    'min' => [
        'numeric' => 'Must be at least :min.',
        'file' => 'Must be at least :min kilobytes.',
        'string' => 'Must be at least :min characters.',
        'array' => 'Must have at least :min items.',
    ],
    'min_digits' => 'Must have at least :min digits.',
    'missing' => 'Must be missing.',
    'missing_if' => 'Must be missing when :other is :value.',
    'missing_unless' => 'Must be missing unless :other is :value.',
    'missing_with' => 'Must be missing when :values is present.',
    'missing_with_all' => 'Must be missing when :values are present.',
    'multiple_of' => 'Must be a multiple of :value.',
    'not_in' => 'This is invalid.',
    'not_regex' => 'Format is invalid.',
    'numeric' => 'Must be a number.',
    'present' => 'Must be present.',
    'prohibited' => 'Prohibited.',
    'prohibited_if' => 'Prohibited when :other is :value.',
    'prohibited_unless' => 'Prohibited unless :other is in :values.',
    'prohibits' => 'Prohibits :other from being present.',
    'regex' => 'Format is invalid.',
    'required' => 'This field is required.',
    'required_array_keys' => 'Must contain entries for: :values.',
    'required_if' => 'This field is required when :other is :value.',
    'required_if_accepted' => 'This field is required when :other is accepted.',
    'required_unless' => 'This field is required unless :other is in :values.',
    'required_with' => 'This field is required when :values is present.',
    'required_with_all' => 'This field is required when :values is present.',
    'required_without' => 'This field is required when :values is not present.',
    'required_without_all' => 'This field is required when none of :values are present.',
    'same' => 'This field and :other must match.',
    'size' => [
        'array' => 'Must contain :size items.',
        'file' => 'Must be :size kilobytes.',
        'numeric' => 'Must be :size.',
        'string' => 'Must be :size characters.',
    ],
    'starts_with' => 'Must start with :values',
    'string' => 'Must be a string.',
    'timezone' => 'Must be a valid zone.',
    'unique' => 'This value has already been taken.',
    'uploaded' => 'Failed to upload.',
    'uppercase' => 'Must be uppercase.',
    'url' => 'Format is invalid.',
    'ulid' => 'Must be a valid ULID.',
    'uuid' => 'Must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Statamic Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may validation messages for the custom rules provided by Statamic.
    |
    */

    'unique_entry_value' => 'This value has already been taken.',
    'unique_term_value' => 'This value has already been taken.',
    'unique_user_value' => 'This value has already been taken.',
    'unique_form_handle' => 'This value has already been taken.',
    'duplicate_field_handle' => 'Field with a handle of :handle cannot be used more than once.',
    'one_site_without_origin' => 'At least one site must not have an origin.',
    'origin_cannot_be_disabled' => 'Cannot select a disabled origin.',
    'unique_uri' => 'This URI has already been taken.',
    'duplicate_uri' => 'Duplicate URI :value',
    'reserved' => 'This is a reserved word.',
    'reserved_field_handle' => 'Field with a handle of :handle is a reserved word.',
    'parent_causes_root_children' => 'This would cause the root page to have children.',
    'parent_cannot_be_itself' => 'Cannot be its own parent.',
    'time' => 'Not a valid time.',
    'date_fieldtype_date_required' => 'Date is required.',
    'date_fieldtype_time_required' => 'Time is required.',
    'date_fieldtype_start_date_required' => 'Start date is required.',
    'date_fieldtype_start_date_invalid' => 'Not a valid start date.',
    'date_fieldtype_end_date_required' => 'End date is required.',
    'date_fieldtype_end_date_invalid' => 'Not a valid end date.',
    'code_fieldtype_rulers' => 'This is invalid.',
    'options_require_keys' => 'All options must have keys.',

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
