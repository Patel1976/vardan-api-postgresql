<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT secret key
    |--------------------------------------------------------------------------
    |
    | This key will be used to sign your JWT tokens. Make sure to keep
    | this secret, and don't share it with others.
    |
    */

    'secret' => env('JWT_SECRET', 'LqwH2UZNGoEvk9Nlmc7wZUmOvqIaprHVJgiET9GF7qfKKqmu13d5kZ9GBuxL5Abc'),

    /*
    |--------------------------------------------------------------------------
    | JWT time to live
    |--------------------------------------------------------------------------
    |
    | This value controls the time to live for the JWT token. It represents
    | the number of minutes that the token will be valid after being issued.
    |
    */

    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | JWT time to refresh
    |--------------------------------------------------------------------------
    |
    | This value controls the time to refresh for the JWT token. It represents
    | the number of minutes after which the token can be refreshed.
    |
    */

    'refresh_ttl' => env('JWT_REFRESH_TTL', 60),

];
