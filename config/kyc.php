<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KYC Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for various KYC (Know Your Customer)
    | providers that can be used for identity verification.
    |
    */

    'default_provider' => env('KYC_DEFAULT_PROVIDER', 'shuftipro'),

    'providers' => [
        'shuftipro' => [
            'enabled' => env('SHUFTIPRO_ENABLED', false),
            'base_url' => env('SHUFTIPRO_BASE_URL', 'https://api.shuftipro.com'),
            'client_id' => env('SHUFTIPRO_CLIENT_ID'),
            'secret_key' => env('SHUFTIPRO_SECRET_KEY'),
            'webhook_secret' => env('SHUFTIPRO_WEBHOOK_SECRET'),
        ],

        'smile_identity' => [
            'enabled' => env('SMILE_IDENTITY_ENABLED', false),
            'base_url' => env('SMILE_IDENTITY_BASE_URL', 'https://api.smileidentity.com'),
            'partner_id' => env('SMILE_IDENTITY_PARTNER_ID'),
            'api_key' => env('SMILE_IDENTITY_API_KEY'),
            'secret_key' => env('SMILE_IDENTITY_SECRET_KEY'),
            'webhook_secret' => env('SMILE_IDENTITY_WEBHOOK_SECRET'),
        ],

        'jumio' => [
            'enabled' => env('JUMIO_ENABLED', false),
            'base_url' => env('JUMIO_BASE_URL', 'https://netverify.com'),
            'api_token' => env('JUMIO_API_TOKEN'),
            'api_secret' => env('JUMIO_API_SECRET'),
            'webhook_secret' => env('JUMIO_WEBHOOK_SECRET'),
        ],

        'onfido' => [
            'enabled' => env('ONFIDO_ENABLED', false),
            'base_url' => env('ONFIDO_BASE_URL', 'https://api.onfido.com'),
            'api_token' => env('ONFIDO_API_TOKEN'),
            'webhook_secret' => env('ONFIDO_WEBHOOK_SECRET'),
        ],

        'sumsub' => [
            'enabled' => env('SUMSUB_ENABLED', false),
            'base_url' => env('SUMSUB_BASE_URL', 'https://test-api.sumsub.com'),
            'app_token' => env('SUMSUB_APP_TOKEN'),
            'secret_key' => env('SUMSUB_SECRET_KEY'),
            'webhook_secret' => env('SUMSUB_WEBHOOK_SECRET'),
        ],

        'veriff' => [
            'enabled' => env('VERIFF_ENABLED', false),
            'base_url' => env('VERIFF_BASE_URL', 'https://api.veriff.me'),
            'public_key' => env('VERIFF_PUBLIC_KEY'),
            'private_key' => env('VERIFF_PRIVATE_KEY'),
            'webhook_secret' => env('VERIFF_WEBHOOK_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | KYC Settings
    |--------------------------------------------------------------------------
    */

    'settings' => [
        // Maximum number of KYC attempts per user
        'max_attempts' => env('KYC_MAX_ATTEMPTS', 3),

        // KYC verification timeout (in minutes)
        'timeout_minutes' => env('KYC_TIMEOUT_MINUTES', 30),

        // Auto-approve KYC after successful verification
        'auto_approve' => env('KYC_AUTO_APPROVE', false),

        // Required document types
        'required_documents' => [
            'identity_document' => true,
            'proof_of_address' => true,
            'selfie' => true,
        ],

        // Supported countries (ISO 3166-1 alpha-2 codes)
        'supported_countries' => [
            'NG', 'GH', 'KE', 'ZA', 'EG', 'MA', 'TN', 'DZ', 'LY', 'SD',
            'US', 'CA', 'GB', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'CH',
            'AU', 'NZ', 'JP', 'KR', 'SG', 'MY', 'TH', 'VN', 'ID', 'PH',
        ],

        // Document verification settings
        'document_verification' => [
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'allowed_formats' => ['jpg', 'jpeg', 'png', 'pdf'],
            'face_matching_threshold' => 0.8,
            'liveness_detection' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | KYC Status Mapping
    |--------------------------------------------------------------------------
    */

    'status_mapping' => [
        'pending' => 'pending',
        'in_progress' => 'pending',
        'processing' => 'pending',
        'approved' => 'verified',
        'verified' => 'verified',
        'success' => 'verified',
        'rejected' => 'rejected',
        'failed' => 'rejected',
        'declined' => 'rejected',
        'expired' => 'expired',
        'cancelled' => 'cancelled',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */

    'webhooks' => [
        'timeout' => 30, // seconds
        'retry_attempts' => 3,
        'retry_delay' => 60, // seconds
    ],
]; 