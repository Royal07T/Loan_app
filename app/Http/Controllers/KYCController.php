<?php

namespace App\Http\Controllers;

use App\Services\KYCService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class KYCController extends Controller
{
    protected $kycService;

    public function __construct(KYCService $kycService)
    {
        $this->kycService = $kycService;
    }

    /**
     * Initialize KYC verification
     */
    public function initialize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => 'required|string|size:2',
            'language' => 'nullable|string|size:2',
            'verification_mode' => 'nullable|string|in:any,document,face,background',
            'redirect_url' => 'nullable|url',
            'supported_types' => 'nullable|array',
            'supported_types.*' => 'string|in:id_card,passport,driving_license,utility_bill,bank_statement',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check if user already has pending KYC
        if ($user->kyc_status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending KYC verification'
            ], 400);
        }

        // Check if user has exceeded max attempts
        $attempts = $user->kyc_attempts ?? 0;
        $maxAttempts = config('kyc.settings.max_attempts', 3);
        
        if ($attempts >= $maxAttempts) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum KYC verification attempts exceeded'
            ], 400);
        }

        try {
            $result = $this->kycService->initializeVerification($user, $request->all());

            if ($result['success']) {
                // Update user KYC status and increment attempts
                $user->update([
                    'kyc_status' => 'pending',
                    'kyc_attempts' => $attempts + 1,
                    'kyc_reference' => $result['reference'],
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $result,
                    'message' => 'KYC verification initiated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to initialize KYC verification'
            ], 400);

        } catch (\Exception $e) {
            Log::error('KYC initialization error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'KYC verification initialization failed'
            ], 500);
        }
    }

    /**
     * Check KYC verification status
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->kyc_reference) {
            return response()->json([
                'success' => false,
                'message' => 'No KYC verification found'
            ], 404);
        }

        try {
            $result = $this->kycService->checkVerificationStatus($user->kyc_reference);

            if ($result['success']) {
                // Update user KYC status if changed
                $newStatus = $this->kycService->mapKYCStatus($result['status']);
                if ($newStatus !== $user->kyc_status) {
                    $user->update([
                        'kyc_status' => $newStatus,
                        'kyc_verified_at' => $newStatus === 'verified' ? now() : null,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => $newStatus,
                        'provider_status' => $result['status'],
                        'data' => $result['data'] ?? null,
                        'last_checked' => now(),
                    ],
                    'message' => 'KYC status retrieved successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to check KYC status'
            ], 400);

        } catch (\Exception $e) {
            Log::error('KYC status check error', [
                'user_id' => $user->id,
                'reference' => $user->kyc_reference,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check KYC status'
            ], 500);
        }
    }

    /**
     * Get user's KYC information
     */
    public function info()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'kyc_status' => $user->kyc_status ?? 'not_started',
                'kyc_attempts' => $user->kyc_attempts ?? 0,
                'kyc_verified_at' => $user->kyc_verified_at,
                'kyc_reference' => $user->kyc_reference,
                'max_attempts' => config('kyc.settings.max_attempts', 3),
                'can_retry' => ($user->kyc_attempts ?? 0) < config('kyc.settings.max_attempts', 3),
            ],
            'message' => 'KYC information retrieved successfully'
        ]);
    }

    /**
     * Get supported KYC providers
     */
    public function providers()
    {
        $providers = $this->kycService->getSupportedProviders();
        $enabledProviders = [];

        foreach ($providers as $key => $name) {
            $config = $this->kycService->getProviderConfig($key);
            if ($config['enabled'] ?? false) {
                $enabledProviders[$key] = [
                    'name' => $name,
                    'features' => $this->getProviderFeatures($key),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current_provider' => config('kyc.default_provider'),
                'providers' => $enabledProviders,
            ],
            'message' => 'Supported KYC providers retrieved successfully'
        ]);
    }

    /**
     * KYC callback/webhook handler
     */
    public function callback(Request $request, string $provider)
    {
        Log::info('KYC callback received', [
            'provider' => $provider,
            'data' => $request->all()
        ]);

        try {
            // Verify webhook signature if configured
            if (!$this->verifyWebhookSignature($request, $provider)) {
                Log::warning('KYC webhook signature verification failed', [
                    'provider' => $provider,
                    'ip' => $request->ip(),
                ]);

                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $result = $this->kycService->processCallback($request->all(), $provider);

            if ($result['success']) {
                Log::info('KYC callback processed successfully', [
                    'provider' => $provider,
                    'reference' => $result['reference'],
                    'status' => $result['status'],
                ]);

                return response()->json(['status' => 'success']);
            }

            Log::error('KYC callback processing failed', [
                'provider' => $provider,
                'error' => $result['message'] ?? 'Unknown error'
            ]);

            return response()->json(['status' => 'failed'], 400);

        } catch (\Exception $e) {
            Log::error('KYC callback error', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Callback processing failed'], 500);
        }
    }

    /**
     * KYC redirect handler
     */
    public function redirect(Request $request)
    {
        $status = $request->get('status', 'unknown');
        $reference = $request->get('reference');

        Log::info('KYC redirect received', [
            'status' => $status,
            'reference' => $reference,
        ]);

        // Redirect to appropriate page based on status
        switch ($status) {
            case 'success':
                return redirect()->route('kyc.success')->with('message', 'KYC verification completed successfully');
            case 'error':
                return redirect()->route('kyc.error')->with('error', 'KYC verification failed');
            case 'cancelled':
                return redirect()->route('kyc.cancelled')->with('warning', 'KYC verification was cancelled');
            default:
                return redirect()->route('kyc.pending')->with('info', 'KYC verification is being processed');
        }
    }

    /**
     * Resubmit KYC verification
     */
    public function resubmit(Request $request)
    {
        $user = Auth::user();

        // Check if user can resubmit
        $attempts = $user->kyc_attempts ?? 0;
        $maxAttempts = config('kyc.settings.max_attempts', 3);
        
        if ($attempts >= $maxAttempts) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum KYC verification attempts exceeded'
            ], 400);
        }

        // Reset KYC status
        $user->update([
            'kyc_status' => null,
            'kyc_reference' => null,
            'kyc_verified_at' => null,
        ]);

        // Initialize new verification
        return $this->initialize($request);
    }

    /**
     * Get provider features
     */
    protected function getProviderFeatures(string $provider): array
    {
        $features = [
            'shuftipro' => [
                'document_verification' => true,
                'face_verification' => true,
                'background_checks' => true,
                'liveness_detection' => true,
                'global_coverage' => true,
            ],
            'smile_identity' => [
                'document_verification' => true,
                'face_verification' => true,
                'background_checks' => true,
                'liveness_detection' => true,
                'africa_focused' => true,
            ],
            'jumio' => [
                'document_verification' => true,
                'face_verification' => true,
                'liveness_detection' => true,
                'global_coverage' => true,
                'enterprise_grade' => true,
            ],
            'onfido' => [
                'document_verification' => true,
                'face_verification' => true,
                'background_checks' => true,
                'liveness_detection' => true,
                'global_coverage' => true,
            ],
            'sumsub' => [
                'document_verification' => true,
                'face_verification' => true,
                'background_checks' => true,
                'liveness_detection' => true,
                'compliance_tools' => true,
            ],
            'veriff' => [
                'document_verification' => true,
                'face_verification' => true,
                'liveness_detection' => true,
                'real_time_verification' => true,
                'anti_fraud' => true,
            ],
        ];

        return $features[$provider] ?? [];
    }

    /**
     * Verify webhook signature
     */
    protected function verifyWebhookSignature(Request $request, string $provider): bool
    {
        $config = $this->kycService->getProviderConfig($provider);
        $webhookSecret = $config['webhook_secret'] ?? null;

        if (!$webhookSecret) {
            // If no webhook secret configured, allow the request
            return true;
        }

        $signature = $request->header('X-Signature') ?? 
                    $request->header('X-Webhook-Signature') ?? 
                    $request->header('Signature');

        if (!$signature) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }
} 