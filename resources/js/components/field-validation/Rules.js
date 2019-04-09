export default [
    // {
    //     text: 'Some Rule',
    //     value: 'some-rule',
    //     example: 'some-rule:some-arg,another-arg',
    //     minVersion: '5.8.0', // This is implemented and working, should we need it
    //     maxVersion: '5.8.0', // This is implemented and working, should we need it
    // },
    {
        text: 'Accepted',
        value: 'accepted'
    },
    {
        text: 'Active URL',
        value: 'active_url'
    },
    {
        text: 'After (Date)',
        value: 'after:',
        example: 'after:date'
    },
    {
        text: 'After Or Equal (Date)',
        value: 'after_or_equal:',
        example: 'after_or_equal:date'
    },
    {
        text: 'Alpha',
        value: 'alpha'
    },
    {
        text: 'Alpha Dash',
        value: 'alpha_dash'
    },
    {
        text: 'Alpha Numeric',
        value: 'alpha_num'
    },
    {
        text: 'Array',
        value: 'array'
    },
    {
        text: 'Bail',
        value: 'bail'
    },
    {
        text: 'Before (Date)',
        value: 'before:',
        example: 'before:date'
    },
    {
        text: 'Before Or Equal (Date)',
        value: 'before_or_equal:',
        example: 'before_or_equal:date'
    },
    {
        text: 'Between',
        value: 'between:',
        example: 'between:min,max'
    },
    {
        text: 'Boolean',
        value: 'boolean'
    },
    {
        text: 'Confirmed',
        value: 'confirmed'
    },
    {
        text: 'Date',
        value: 'date'
    },
    {
        text: 'Date Equals',
        value: 'date_equals:',
        example: 'date_equals:date'
    },
    {
        text: 'Date Format',
        value: 'date_format:',
        example: 'date_format:date'
    },
    {
        text: 'Different',
        value: 'different:',
        example: 'different:field'
    },
    {
        text: 'Digits',
        value: 'digits:',
        example: 'digits:value'
    },
    {
        text: 'Digits Between',
        value: 'digits_between:',
        example: 'digits_between:min,max'
    },
    {
        text: 'Dimensions (Image Files)',
        value: 'dimensions:',
        example: 'dimensions:min_width=100,min_height=200'
    },
    {
        text: 'Distinct',
        value: 'distinct'
    },
    {
        text: 'E-Mail',
        value: 'email'
    },
    {
        text: 'Exists (Database)',
        value: 'exists:',
        example: 'exists:table,column'
    },
    {
        text: 'File',
        value: 'file'
    },
    {
        text: 'Filled',
        value: 'filled'
    },
    {
        text: 'Greater Than',
        value: 'gt:',
        example: 'gt:field'
    },
    {
        text: 'Greater Than Or Equal',
        value: 'gte:',
        example: 'gte:field'
    },
    {
        text: 'Image (File)',
        value: 'image'
    },
    {
        text: 'In',
        value: 'in:',
        example: 'in:foo,bar,...'
    },
    {
        text: 'In Array',
        value: 'in_array:',
        example: 'in_array:anotherfield'
    },
    {
        text: 'Integer',
        value: 'integer'
    },
    {
        text: 'IP Address',
        value: 'ip'
    },
    {
        text: 'IP Address (ipv4)',
        value: 'ipv4'
    },
    {
        text: 'IP Address (ipv6)',
        value: 'ipv6'
    },
    {
        text: 'JSON',
        value: 'json'
    },
    {
        text: 'Less Than',
        value: 'lt:',
        example: 'lt:field'
    },
    {
        text: 'Less Than Or Equal',
        value: 'lte:',
        example: 'lte:field'
    },
    {
        text: 'Max',
        value: 'max:',
        example: 'max:value',
    },
    {
        text: 'MIME Types',
        value: 'mimetypes:',
        example: 'mimetypes:text/plain,...'
    },
    {
        text: 'MIME Type By File Extension',
        value: 'mimes:',
        example: 'mimes:foo,bar,...'
    },
    {
        text: 'Min',
        value: 'min:',
        example: 'min:value'
    },
    {
        text: 'Not In',
        value: 'not_in:',
        example: 'not_in:foo,bar,...'
    },
    {
        text: 'Not Regex',
        value: 'not_regex:',
        example: 'not_regex:pattern'
    },
    {
        text: 'Nullable',
        value: 'nullable'
    },
    {
        text: 'Numeric',
        value: 'numeric'
    },
    {
        text: 'Present',
        value: 'present'
    },
    {
        text: 'Regular Expression',
        value: 'regex:',
        example: 'regex:pattern'
    },
    {
        text: 'Required',
        value: 'required'
    },
    {
        text: 'Required If',
        value: 'required_if:',
        example: 'required_if:anotherfield,value,...'
    },
    {
        text: 'Required Unless',
        value: 'required_unless:',
        example: 'required_unless:anotherfield,value,...'
    },
    {
        text: 'Required With',
        value: 'required_with:',
        example: 'required_with:foo,bar,...'
    },
    {
        text: 'Required With All',
        value: 'required_with_all:',
        example: 'required_with_all:foo,bar,...'
    },
    {
        text: 'Required Without',
        value: 'required_without:',
        example: 'required_without:foo,bar,...'
    },
    {
        text: 'Required Without All',
        value: 'required_without_all:',
        example: 'required_without_all:foo,bar,...'
    },
    {
        text: 'Same',
        value: 'same:',
        example: 'same:field'
    },
    {
        text: 'Size',
        value: 'size:',
        example: 'size:value'
    },
    {
        text: 'String',
        value: 'string'
    },
    {
        text: 'Timezone',
        value: 'timezone'
    },
    {
        text: 'Unique (Database)',
        value: 'unique:',
        example: 'unique:table,column,except,idColumn'
    },
    {
        text: 'URL',
        value: 'url'
    }
];
