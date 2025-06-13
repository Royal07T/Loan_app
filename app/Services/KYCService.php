<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use App\Notifications\KYCStatusNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KYCService
{
    protected $provider;
    protected $config;

    public function __construct()
    {
        $this->provider = config('kyc.default_provider', 'shuftipro');
        $this->config = config("kyc.providers.{$this->provider}");
    }

    /**
     * Initialize KYC verification for a user
     */
    public function initializeVerification(User $user, array $data): array
    {
        try {
            switch ($this->provider) {
                case 'shuftipro':
                    return $this->shuftiProVerification($user, $data);
                case 'smile_identity':
                    return $this->smileIdentityVerification($user, $data);
                case 'jumio':
                    return $this->jumioVerification($user, $data);
                case 'onfido':
                    return $this->onfidoVerification($user, $data);
                default:
                    throw new \Exception("Unsupported KYC provider: {$this->provider}");
            }
        } catch (\Exception $e) {
            Log::error('KYC verification initialization failed', [
                'user_id' => $user->id,
                'provider' => $this->provider,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'KYC verification initialization failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ShuftiPro KYC Integration
     */
    protected function shuftiProVerification(User $user, array $data): array
    {
        $payload = [
            'reference' => $this->generateReference($user),
            'callback_url' => route('kyc.callback', ['provider' => 'shuftipro']),
            'redirect_url' => $data['redirect_url'] ?? route('kyc.redirect'),
            'email' => $user->email,
            'country' => $data['country'] ?? 'NG',
            'language' => $data['language'] ?? 'EN',
            'verification_mode' => $data['verification_mode'] ?? 'any',
            'document' => [
                'proof' => $data['document_proof'] ?? '',
                'additional_proof' => $data['additional_proof'] ?? '',
                'supported_types' => $data['supported_types'] ?? ['id_card', 'passport', 'driving_license'],
            ],
            'face' => [
                'proof' => $data['face_proof'] ?? '',
            ],
            'background_checks' => [
                'proof' => $data['background_proof'] ?? '',
            ],
            'document_verification' => [
                'proof' => $data['document_verification_proof'] ?? '',
                'additional_proof' => $data['additional_verification_proof'] ?? '',
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->config['client_id'] . ':' . $this->config['secret_key']),
            'Content-Type' => 'application/json',
        ])->post($this->config['base_url'] . '/api/verification', $payload);

        if ($response->successful()) {
            $result = $response->json();
            
            // Store verification request
            $this->storeVerificationRequest($user, $result['reference'], $payload);

            return [
                'success' => true,
                'reference' => $result['reference'],
                'verification_url' => $result['verification_url'] ?? null,
                'message' => 'KYC verification initiated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to initialize KYC verification',
            'error' => $response->json()['message'] ?? 'Unknown error'
        ];
    }

    /**
     * Smile Identity KYC Integration
     */
    protected function smileIdentityVerification(User $user, array $data): array
    {
        $jobId = $this->generateReference($user);
        
        $payload = [
            'partner_id' => $this->config['partner_id'],
            'user_id' => $user->id,
            'job_id' => $jobId,
            'job_type' => $data['job_type'] ?? 1, // 1 = Basic KYC
            'callback_url' => route('kyc.callback', ['provider' => 'smile_identity']),
            'return_job_status' => true,
            'return_history' => true,
            'return_images' => false,
            'source_sdk' => 'web',
            'source_sdk_version' => '1.0.0',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->config['api_key'] . ':' . $this->config['secret_key']),
            'Content-Type' => 'application/json',
        ])->post($this->config['base_url'] . '/v1/upload', $payload);

        if ($response->successful()) {
            $result = $response->json();
            
            // Store verification request
            $this->storeVerificationRequest($user, $jobId, $payload);

            return [
                'success' => true,
                'reference' => $jobId,
                'verification_url' => $result['upload_url'] ?? null,
                'message' => 'KYC verification initiated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to initialize KYC verification',
            'error' => $response->json()['message'] ?? 'Unknown error'
        ];
    }

    /**
     * Jumio KYC Integration
     */
    protected function jumioVerification(User $user, array $data): array
    {
        $payload = [
            'customerId' => $user->id,
            'authorizationToken' => $this->generateReference($user),
            'successUrl' => route('kyc.redirect', ['status' => 'success']),
            'errorUrl' => route('kyc.redirect', ['status' => 'error']),
            'callbackUrl' => route('kyc.callback', ['provider' => 'jumio']),
            'locale' => $data['locale'] ?? 'en',
            'country' => $data['country'] ?? 'NG',
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->config['api_token'] . ':' . $this->config['api_secret']),
            'Content-Type' => 'application/json',
            'User-Agent' => 'Laravel-KYC-Integration/1.0',
        ])->post($this->config['base_url'] . '/api/netverify/v2/performNetverify', $payload);

        if ($response->successful()) {
            $result = $response->json();
            
            // Store verification request
            $this->storeVerificationRequest($user, $result['transactionReference'], $payload);

            return [
                'success' => true,
                'reference' => $result['transactionReference'],
                'verification_url' => $result['redirectUrl'] ?? null,
                'message' => 'KYC verification initiated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to initialize KYC verification',
            'error' => $response->json()['message'] ?? 'Unknown error'
        ];
    }

    /**
     * Onfido KYC Integration
     */
    protected function onfidoVerification(User $user, array $data): array
    {
        // First create applicant
        $applicantPayload = [
            'first_name' => $user->first_name ?? $user->name,
            'last_name' => $user->last_name ?? '',
            'email' => $user->email,
            'dob' => $data['date_of_birth'] ?? null,
            'address' => [
                'building_number' => $data['building_number'] ?? '',
                'street' => $data['street'] ?? '',
                'city' => $data['city'] ?? '',
                'state' => $data['state'] ?? '',
                'postcode' => $data['postcode'] ?? '',
                'country' => $data['country'] ?? 'NGA',
            ],
        ];

        $applicantResponse = Http::withHeaders([
            'Authorization' => 'Token token=' . $this->config['api_token'],
            'Content-Type' => 'application/json',
        ])->post($this->config['base_url'] . '/v3.4/applicants', $applicantPayload);

        if (!$applicantResponse->successful()) {
            return [
                'success' => false,
                'message' => 'Failed to create applicant',
                'error' => $applicantResponse->json()['message'] ?? 'Unknown error'
            ];
        }

        $applicant = $applicantResponse->json();
        
        // Create check
        $checkPayload = [
            'applicant_id' => $applicant['id'],
            'report_names' => ['document', 'facial_similarity_photo'],
            'document_ids' => $data['document_ids'] ?? [],
        ];

        $checkResponse = Http::withHeaders([
            'Authorization' => 'Token token=' . $this->config['api_token'],
            'Content-Type' => 'application/json',
        ])->post($this->config['base_url'] . '/v3.4/checks', $checkPayload);

        if ($checkResponse->successful()) {
            $check = $checkResponse->json();
            
            // Store verification request
            $this->storeVerificationRequest($user, $check['id'], [
                'applicant_id' => $applicant['id'],
                'check_id' => $check['id'],
            ]);

            return [
                'success' => true,
                'reference' => $check['id'],
                'applicant_id' => $applicant['id'],
                'message' => 'KYC verification initiated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create verification check',
            'error' => $checkResponse->json()['message'] ?? 'Unknown error'
        ];
    }

    /**
     * Check verification status
     */
    public function checkVerificationStatus(string $reference): array
    {
        try {
            switch ($this->provider) {
                case 'shuftipro':
                    return $this->shuftiProStatus($reference);
                case 'smile_identity':
                    return $this->smileIdentityStatus($reference);
                case 'jumio':
                    return $this->jumioStatus($reference);
                case 'onfido':
                    return $this->onfidoStatus($reference);
                default:
                    throw new \Exception("Unsupported KYC provider: {$this->provider}");
            }
        } catch (\Exception $e) {
            Log::error('KYC status check failed', [
                'reference' => $reference,
                'provider' => $this->provider,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to check verification status',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ShuftiPro status check
     */
    protected function shuftiProStatus(string $reference): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->config['client_id'] . ':' . $this->config['secret_key']),
        ])->get($this->config['base_url'] . '/api/verification/' . $reference);

        if ($response->successful()) {
            $result = $response->json();
            
            return [
                'success' => true,
                'status' => $result['verification_status'] ?? 'pending',
                'data' => $result,
                'message' => 'Verification status retrieved successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to get verification status',
            'error' => $response->json()['message'] ?? 'Unknown error'
        ];
    }

    /**
     * Smile Identity status check
     */
    protected function smileIdentityStatus(string $reference): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->config['api_key'] . ':' . $this->config['secret_key']),
        ])->get($this->config['base_url'] . '/v1/jobs/' . $reference);

        if ($response->successful()) {
            $result = $response->json();
            
            return [
                'success' => true,
                'status' => $result['job_status'] ?? 'pending',
                'data' => $result,
                'message' => 'Verification status retrieved successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to get verification status',
            'error' => $response->json()['message'] ?? 'Unknown error'
        ];
    }

    /**
     * Jumio status check
     */
    protected function jumioStatus(string $reference): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->config['api_token'] . ':' . $this->config['api_secret']),
        ])->get($this->config['base_url'] . '/api/netverify/v2/scans/' . $reference);

        if ($response->successful()) {
            $result = $response->json();
            
            return [
                'success' => true,
                'status' => $result['status'] ?? 'pending',
                'data' => $result,
                'message' => 'Verification status retrieved successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to get verification status',
            'error' => $response->json()['message'] ?? 'Unknown error'
        ];
    }

    /**
     * Onfido status check
     */
    protected function onfidoStatus(string $reference): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token token=' . $this->config['api_token'],
        ])->get($this->config['base_url'] . '/v3.4/checks/' . $reference);

        if ($response->successful()) {
            $result = $response->json();
            
            return [
                'success' => true,
                'status' => $result['status'] ?? 'pending',
                'data' => $result,
                'message' => 'Verification status retrieved successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to get verification status',
            'error' => $response->json()['message'] ?? 'Unknown error'
        ];
    }

    /**
     * Process KYC callback/webhook
     */
    public function processCallback(array $data, string $provider = null): array
    {
        $provider = $provider ?? $this->provider;
        
        try {
            switch ($provider) {
                case 'shuftipro':
                    return $this->processShuftiProCallback($data);
                case 'smile_identity':
                    return $this->processSmileIdentityCallback($data);
                case 'jumio':
                    return $this->processJumioCallback($data);
                case 'onfido':
                    return $this->processOnfidoCallback($data);
                default:
                    throw new \Exception("Unsupported KYC provider: {$provider}");
            }
        } catch (\Exception $e) {
            Log::error('KYC callback processing failed', [
                'provider' => $provider,
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Callback processing failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process ShuftiPro callback
     */
    protected function processShuftiProCallback(array $data): array
    {
        $reference = $data['reference'] ?? null;
        $status = $data['verification_status'] ?? 'pending';
        
        if (!$reference) {
            return ['success' => false, 'message' => 'No reference provided'];
        }

        // Update user KYC status
        $this->updateUserKYCStatus($reference, $status, $data);

        return [
            'success' => true,
            'reference' => $reference,
            'status' => $status,
            'message' => 'Callback processed successfully'
        ];
    }

    /**
     * Process Smile Identity callback
     */
    protected function processSmileIdentityCallback(array $data): array
    {
        $jobId = $data['job_id'] ?? null;
        $status = $data['job_status'] ?? 'pending';
        
        if (!$jobId) {
            return ['success' => false, 'message' => 'No job ID provided'];
        }

        // Update user KYC status
        $this->updateUserKYCStatus($jobId, $status, $data);

        return [
            'success' => true,
            'reference' => $jobId,
            'status' => $status,
            'message' => 'Callback processed successfully'
        ];
    }

    /**
     * Process Jumio callback
     */
    protected function processJumioCallback(array $data): array
    {
        $reference = $data['transactionReference'] ?? null;
        $status = $data['status'] ?? 'pending';
        
        if (!$reference) {
            return ['success' => false, 'message' => 'No reference provided'];
        }

        // Update user KYC status
        $this->updateUserKYCStatus($reference, $status, $data);

        return [
            'success' => true,
            'reference' => $reference,
            'status' => $status,
            'message' => 'Callback processed successfully'
        ];
    }

    /**
     * Process Onfido callback
     */
    protected function processOnfidoCallback(array $data): array
    {
        $checkId = $data['resource_id'] ?? null;
        $status = $data['status'] ?? 'pending';
        
        if (!$checkId) {
            return ['success' => false, 'message' => 'No check ID provided'];
        }

        // Update user KYC status
        $this->updateUserKYCStatus($checkId, $status, $data);

        return [
            'success' => true,
            'reference' => $checkId,
            'status' => $status,
            'message' => 'Callback processed successfully'
        ];
    }

    /**
     * Store verification request
     */
    protected function storeVerificationRequest(User $user, string $reference, array $data): void
    {
        // Store in database or cache
        \Cache::put("kyc_request_{$reference}", [
            'user_id' => $user->id,
            'provider' => $this->provider,
            'data' => $data,
            'created_at' => now(),
        ], now()->addDays(30));
    }

    /**
     * Update user KYC status
     */
    protected function updateUserKYCStatus(string $reference, string $status, array $data): void
    {
        $requestData = \Cache::get("kyc_request_{$reference}");
        
        if ($requestData) {
            $user = User::find($requestData['user_id']);
            
            if ($user) {
                $oldStatus = $user->kyc_status;
                $kycStatus = $this->mapKYCStatus($status);
                
                $user->update([
                    'kyc_status' => $kycStatus,
                    'kyc_verified_at' => $kycStatus === 'verified' ? now() : null,
                    'kyc_data' => $data,
                ]);

                // Send notification if status changed
                if ($oldStatus !== $kycStatus) {
                    $this->sendKYCNotification($user, $kycStatus, $data);
                }

                // Log KYC status change
                Log::info('User KYC status updated', [
                    'user_id' => $user->id,
                    'reference' => $reference,
                    'old_status' => $oldStatus,
                    'new_status' => $kycStatus,
                    'provider_status' => $status
                ]);
            }
        }
    }

    /**
     * Send KYC status notification
     */
    protected function sendKYCNotification(User $user, string $status, array $data): void
    {
        try {
            $reason = $data['reason'] ?? $data['rejection_reason'] ?? null;
            $notes = $data['notes'] ?? $data['admin_notes'] ?? null;
            
            $user->notify(new KYCStatusNotification($status, $reason, $notes));
            
            Log::info('KYC notification sent', [
                'user_id' => $user->id,
                'status' => $status,
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send KYC notification', [
                'user_id' => $user->id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Map provider status to internal KYC status
     */
    protected function mapKYCStatus(string $providerStatus): string
    {
        $statusMap = [
            'approved' => 'verified',
            'verified' => 'verified',
            'success' => 'verified',
            'rejected' => 'rejected',
            'failed' => 'rejected',
            'declined' => 'rejected',
            'pending' => 'pending',
            'in_progress' => 'pending',
            'processing' => 'pending',
        ];

        return $statusMap[strtolower($providerStatus)] ?? 'pending';
    }

    /**
     * Generate unique reference
     */
    protected function generateReference(User $user): string
    {
        return 'KYC_' . $user->id . '_' . time() . '_' . substr(md5(uniqid()), 0, 8);
    }

    /**
     * Get supported providers
     */
    public function getSupportedProviders(): array
    {
        return [
            'shuftipro' => 'ShuftiPro',
            'smile_identity' => 'Smile Identity',
            'jumio' => 'Jumio',
            'onfido' => 'Onfido',
            'sumsub' => 'Sumsub',
            'veriff' => 'Veriff',
        ];
    }

    /**
     * Get provider configuration
     */
    public function getProviderConfig(string $provider = null): array
    {
        $provider = $provider ?? $this->provider;
        return config("kyc.providers.{$provider}", []);
    }
} 