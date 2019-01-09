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

    'accepted' => __('The :attribute must be accepted.', PMLD_TD),
    'active_url' => __('The :attribute is not a valid URL.', PMLD_TD),
    'alpha' => __('The :attribute may only contain letters.', PMLD_TD),
    'alpha_num' => __('The :attribute may only contain letters and numbers.', PMLD_TD),
    'array' => __('The :attribute must be an array.', PMLD_TD),
    'boolean' => __('The :attribute field must be true or false.', PMLD_TD),
    'string' => __('The :attribute must be a string.', PMLD_TD),
    'email' => __('The :attribute must be a valid email address.', PMLD_TD),
    'file' => __('The :attribute must be a file.', PMLD_TD),
    'image' => __('The :attribute must be an image.', PMLD_TD),
    'in_array' => __('The :attribute field does not exist in :other.', PMLD_TD),
    'integer' => __('The :attribute must be an integer.', PMLD_TD),
    'ip' => __('The :attribute must be a valid IP address.', PMLD_TD),
    'ipv4' => __('The :attribute must be a valid IPv4 address.', PMLD_TD),
    'ipv6' => __('The :attribute must be a valid IPv6 address.', PMLD_TD),
    'json' => __('The :attribute must be a valid JSON string.', PMLD_TD),
    'password' => __('The :attribute must contain at least :other.', PMLD_TD),
    'max' => [
        'numeric' => __('The :attribute may not be greater than :max.', PMLD_TD),
        'file' => __('The :attribute may not be greater than :max kilobytes.', PMLD_TD),
        'string' => __('The :attribute may not be greater than :max characters.', PMLD_TD),
        'array' => __('The :attribute may not have more than :max items.', PMLD_TD),
    ],
    'mimes' => __('The :attribute must be a file of type: :values.', PMLD_TD),
    'min' => [
        'numeric' => __('The :attribute must be at least :min.', PMLD_TD),
        'file' => __('The :attribute must be at least :min kilobytes.', PMLD_TD),
        'string' => __('The :attribute must be at least :min characters.', PMLD_TD),
        'array' => __('The :attribute must have at least :min items.', PMLD_TD),
    ],
    'numeric' => __('The :attribute must be a number.', PMLD_TD),
    'required' => __('The :attribute field is required.', PMLD_TD),
    'size' => [
        'numeric' => __('The :attribute must be :size.', PMLD_TD),
        'file' => __('The :attribute must be :size kilobytes.', PMLD_TD),
        'string' => __('The :attribute must be :size characters.', PMLD_TD),
        'array' => __('The :attribute must contain :size items.', PMLD_TD),
    ],
    'uploaded' => __('The :attribute failed to upload.', PMLD_TD),
    'url' => __('The :attribute format is invalid.', PMLD_TD),

];
