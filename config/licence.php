<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RSA Public Key
    |--------------------------------------------------------------------------
    | The public key used to verify licence signatures.
    | The private key NEVER leaves the vendor's infrastructure.
    | In production, replace this with your actual RSA-4096 public key PEM.
    */
    'public_key' => env('LICENCE_PUBLIC_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | Licence Key Path
    |--------------------------------------------------------------------------
    */
    'key_path' => env('LICENCE_KEY_PATH', storage_path('licence/key.txt')),

    /*
    |--------------------------------------------------------------------------
    | Grace Period (days)
    |--------------------------------------------------------------------------
    | Number of days after expiry before hard enforcement kicks in.
    */
    'grace_days' => 7,
];
