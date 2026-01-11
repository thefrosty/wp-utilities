<?php

declare(strict_types=1);

use TheFrosty\WpUtilities\Api\Validator\Rules;

return [
    'rules' => [
        'in' => Rules\In::class,
        'nullable' => Rules\Nullable::class,
        'required' => Rules\Required::class,
    ],
];
