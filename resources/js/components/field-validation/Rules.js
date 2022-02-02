export default [
    // {
    //     label: 'Some Rule',
    //     value: 'some-rule',
    //     example: 'some-rule:some-arg,another-arg',
    //     minVersion: '5.8.0', // This is implemented and working, should we need it
    //     maxVersion: '5.8.0', // This is implemented and working, should we need it
    // },
    {
        label: 'Accepted',
        value: 'accepted'
    },
    {
        label: 'Active URL',
        value: 'active_url'
    },
    {
        label: 'After (Date)',
        value: 'after:',
        example: 'after:date'
    },
    {
        label: 'After Or Equal (Date)',
        value: 'after_or_equal:',
        example: 'after_or_equal:date'
    },
    {
        label: 'Alpha',
        value: 'alpha'
    },
    {
        label: 'Alpha Dash',
        value: 'alpha_dash'
    },
    {
        label: 'Alpha Numeric',
        value: 'alpha_num'
    },
    {
        label: 'Array',
        value: 'array'
    },
    {
        label: 'Bail',
        value: 'bail'
    },
    {
        label: 'Before (Date)',
        value: 'before:',
        example: 'before:date'
    },
    {
        label: 'Before Or Equal (Date)',
        value: 'before_or_equal:',
        example: 'before_or_equal:date'
    },
    {
        label: 'Between',
        value: 'between:',
        example: 'between:min,max'
    },
    {
        label: 'Boolean',
        value: 'boolean'
    },
    {
        label: 'Confirmed',
        value: 'confirmed'
    },
    {
        label: 'Date',
        value: 'date'
    },
    {
        label: 'Date Equals',
        value: 'date_equals:',
        example: 'date_equals:date'
    },
    {
        label: 'Date Format',
        value: 'date_format:',
        example: 'date_format:date'
    },
    {
        label: 'Different',
        value: 'different:',
        example: 'different:field'
    },
    {
        label: 'Digits',
        value: 'digits:',
        example: 'digits:value'
    },
    {
        label: 'Digits Between',
        value: 'digits_between:',
        example: 'digits_between:min,max'
    },
    {
        label: 'Dimensions (Image Files)',
        value: 'dimensions:',
        example: 'dimensions:min_width=100,min_height=200'
    },
    {
        label: 'Distinct',
        value: 'distinct'
    },
    {
        label: 'E-Mail',
        value: 'email'
    },
    {
        label: 'Ends With',
        value: 'ends_with:',
        example: 'ends_with:foo,bar,...',
        minVersion: '5.8.17'
    },
    // {
    //     label: 'Exists (Database)',
    //     value: 'exists:',
    //     example: 'exists:table,column'
    // },
    {
        label: 'File',
        value: 'file'
    },
    {
        label: 'Filled',
        value: 'filled'
    },
    {
        label: 'Greater Than',
        value: 'gt:',
        example: 'gt:field'
    },
    {
        label: 'Greater Than Or Equal',
        value: 'gte:',
        example: 'gte:field'
    },
    {
        label: 'Image (File)',
        value: 'image'
    },
    {
        label: 'In',
        value: 'in:',
        example: 'in:foo,bar,...'
    },
    {
        label: 'In Array',
        value: 'in_array:',
        example: 'in_array:anotherfield'
    },
    {
        label: 'Integer',
        value: 'integer'
    },
    {
        label: 'IP Address',
        value: 'ip'
    },
    {
        label: 'IP Address (ipv4)',
        value: 'ipv4'
    },
    {
        label: 'IP Address (ipv6)',
        value: 'ipv6'
    },
    {
        label: 'JSON',
        value: 'json'
    },
    {
        label: 'Less Than',
        value: 'lt:',
        example: 'lt:field'
    },
    {
        label: 'Less Than Or Equal',
        value: 'lte:',
        example: 'lte:field'
    },
    {
        label: 'Max',
        value: 'max:',
        example: 'max:value',
    },
    {
        label: 'Max Filesize (KB)',
        value: 'max_filesize:',
        example: 'max_filesize:value',
    },
    {
        label: 'MIME Types',
        value: 'mimetypes:',
        example: 'mimetypes:text/plain,...'
    },
    {
        label: 'MIME Type By File Extension',
        value: 'mimes:',
        example: 'mimes:foo,bar,...'
    },
    {
        label: 'Min',
        value: 'min:',
        example: 'min:value'
    },
    {
        label: 'Min Filesize (KB)',
        value: 'min_filesize:',
        example: 'min_filesize:value',
    },
    {
        label: 'Not In',
        value: 'not_in:',
        example: 'not_in:foo,bar,...'
    },
    {
        label: 'Not Regular Expression',
        value: 'not_regex:',
        example: 'not_regex:pattern'
    },
    {
        label: 'Nullable',
        value: 'nullable'
    },
    {
        label: 'Numeric',
        value: 'numeric'
    },
    {
        label: 'Present',
        value: 'present'
    },
    {
        label: 'Regular Expression',
        value: 'regex:',
        example: 'regex:pattern'
    },
    {
        label: 'Required',
        value: 'required'
    },
    {
        label: 'Required If',
        value: 'required_if:',
        example: 'required_if:anotherfield,value,...'
    },
    {
        label: 'Required Unless',
        value: 'required_unless:',
        example: 'required_unless:anotherfield,value,...'
    },
    {
        label: 'Required With',
        value: 'required_with:',
        example: 'required_with:foo,bar,...'
    },
    {
        label: 'Required With All',
        value: 'required_with_all:',
        example: 'required_with_all:foo,bar,...'
    },
    {
        label: 'Required Without',
        value: 'required_without:',
        example: 'required_without:foo,bar,...'
    },
    {
        label: 'Required Without All',
        value: 'required_without_all:',
        example: 'required_without_all:foo,bar,...'
    },
    {
        label: 'Same',
        value: 'same:',
        example: 'same:field'
    },
    {
        label: 'Size',
        value: 'size:',
        example: 'size:value'
    },
    {
        label: 'Sometimes',
        value: 'sometimes',
    },
    {
        label: 'Starts With',
        value: 'starts_with:',
        example: 'starts_with:foo,bar,...',
        minVersion: '5.7.15'
    },
    {
        label: 'String',
        value: 'string'
    },
    {
        label: 'Timezone',
        value: 'timezone'
    },
    // {
    //     label: 'Unique (Database)',
    //     value: 'unique:',
    //     example: 'unique:table,column,except,idColumn'
    // },
    {
        label: 'Unique Entry Value',
        value: 'unique_entry_value:{collection},{id},{site}',
    },
    {
        label: 'URL',
        value: 'url'
    },
    {
        label: 'UUID',
        value: 'uuid',
        minVersion: '5.7.10'
    }
];
