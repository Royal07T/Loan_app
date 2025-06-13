<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\KYCStatusNotification;
use App\Services\KYCService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class KYCAdminController extends Controller
{
    protected $kycService;

    public function __construct(KYCService $kycService)
    {
        $this->kycService = $kycService;
        $this->middleware('admin');
    }

    /**
     * Display KYC dashboard
     */
    public function dashboard()
    {
        $stats = $this->getKYCStats();
        $recentVerifications = $this->getRecentVerifications();
        $pendingVerifications = $this->getPendingVerifications();

        return view('admin.kyc.dashboard', compact('stats', 'recentVerifications', 'pendingVerifications'));
    }

    /**
     * List all KYC verifications with filters
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('kyc_status', $request->status);
        }

        if ($request->filled('provider')) {
            $query->where('kyc_provider', $request->provider);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('kyc_reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $verifications = $query->paginate(20);

        // Get filter options
        $statuses = ['not_started', 'pending', 'verified', 'rejected', 'expired', 'cancelled'];
        $providers = $this->kycService->getSupportedProviders();

        return view('admin.kyc.index', compact('verifications', 'statuses', 'providers'));
    }

    /**
     * Show KYC verification details
     */
    public function show(User $user)
    {
        $kycData = $user->kyc_data ?? [];
        $kycStatus = $user->getKYCStatusWithExpiry();
        
        // Get verification details from provider if available
        $providerDetails = null;
        if ($user->kyc_reference && $user->kyc_provider) {
            try {
                $result = $this->kycService->checkVerificationStatus($user->kyc_reference);
                if ($result['success']) {
                    $providerDetails = $result['data'];
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch provider details', [
                    'user_id' => $user->id,
                    'reference' => $user->kyc_reference,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('admin.kyc.show', compact('user', 'kycData', 'kycStatus', 'providerDetails'));
    }

    /**
     * Approve KYC verification
     */
    public function approve(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldStatus = $user->kyc_status;
            
            $user->update([
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
                'kyc_data' => array_merge($user->kyc_data ?? [], [
                    'admin_approval' => [
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'notes' => $request->notes,
                    ]
                ])
            ]);

            // Send notification if status changed
            if ($oldStatus !== 'verified') {
                $user->notify(new KYCStatusNotification('verified', null, $request->notes));
            }

            // Log the approval
            Log::info('KYC verification approved by admin', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => 'verified',
                'notes' => $request->notes
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'KYC verification approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC approval failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve KYC verification'
            ], 500);
        }
    }

    /**
     * Reject KYC verification
     */
    public function reject(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldStatus = $user->kyc_status;
            
            $user->update([
                'kyc_status' => 'rejected',
                'kyc_data' => array_merge($user->kyc_data ?? [], [
                    'admin_rejection' => [
                        'rejected_by' => auth()->id(),
                        'rejected_at' => now(),
                        'reason' => $request->reason,
                        'notes' => $request->notes,
                    ]
                ])
            ]);

            // Send notification if status changed
            if ($oldStatus !== 'rejected') {
                $user->notify(new KYCStatusNotification('rejected', $request->reason, $request->notes));
            }

            // Log the rejection
            Log::info('KYC verification rejected by admin', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => 'rejected',
                'reason' => $request->reason,
                'notes' => $request->notes
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'KYC verification rejected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC rejection failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject KYC verification'
            ], 500);
        }
    }

    /**
     * Reset KYC verification
     */
    public function reset(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user->update([
                'kyc_status' => 'not_started',
                'kyc_reference' => null,
                'kyc_verified_at' => null,
                'kyc_attempts' => 0,
                'kyc_data' => array_merge($user->kyc_data ?? [], [
                    'admin_reset' => [
                        'reset_by' => auth()->id(),
                        'reset_at' => now(),
                        'reason' => $request->reason,
                    ]
                ])
            ]);

            // Log the reset
            Log::info('KYC verification reset by admin', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'reason' => $request->reason
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'KYC verification reset successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KYC reset failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset KYC verification'
            ], 500);
        }
    }

    /**
     * Bulk actions on KYC verifications
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject,reset',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'reason' => 'required_if:action,reject,reset|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $users = User::whereIn('id', $request->user_ids)->get();
        $successCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            try {
                switch ($request->action) {
                    case 'approve':
                        $user->update([
                            'kyc_status' => 'verified',
                            'kyc_verified_at' => now(),
                            'kyc_data' => array_merge($user->kyc_data ?? [], [
                                'admin_approval' => [
                                    'approved_by' => auth()->id(),
                                    'approved_at' => now(),
                                    'notes' => $request->notes,
                                ]
                            ])
                        ]);
                        break;

                    case 'reject':
                        $user->update([
                            'kyc_status' => 'rejected',
                            'kyc_data' => array_merge($user->kyc_data ?? [], [
                                'admin_rejection' => [
                                    'rejected_by' => auth()->id(),
                                    'rejected_at' => now(),
                                    'reason' => $request->reason,
                                    'notes' => $request->notes,
                                ]
                            ])
                        ]);
                        break;

                    case 'reset':
                        $user->update([
                            'kyc_status' => 'not_started',
                            'kyc_reference' => null,
                            'kyc_verified_at' => null,
                            'kyc_attempts' => 0,
                            'kyc_data' => array_merge($user->kyc_data ?? [], [
                                'admin_reset' => [
                                    'reset_by' => auth()->id(),
                                    'reset_at' => now(),
                                    'reason' => $request->reason,
                                ]
                            ])
                        ]);
                        break;
                }
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Bulk KYC action failed', [
                    'user_id' => $user->id,
                    'action' => $request->action,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk action completed. Success: {$successCount}, Failed: {$failedCount}"
        ]);
    }

    /**
     * Export KYC data
     */
    public function export(Request $request)
    {
        $query = User::query();

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('kyc_status', $request->status);
        }

        if ($request->filled('provider')) {
            $query->where('kyc_provider', $request->provider);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('kyc_reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->get();

        $filename = 'kyc_verifications_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'User ID',
                'Name',
                'Email',
                'KYC Status',
                'KYC Provider',
                'KYC Reference',
                'KYC Verified At',
                'KYC Attempts',
                'Created At',
                'Updated At'
            ]);

            // Add data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->kyc_status,
                    $user->kyc_provider,
                    $user->kyc_reference,
                    $user->kyc_verified_at,
                    $user->kyc_attempts,
                    $user->created_at,
                    $user->updated_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get KYC statistics
     */
    protected function getKYCStats(): array
    {
        $totalUsers = User::count();
        $verifiedUsers = User::where('kyc_status', 'verified')->count();
        $pendingUsers = User::where('kyc_status', 'pending')->count();
        $rejectedUsers = User::where('kyc_status', 'rejected')->count();
        $notStartedUsers = User::where('kyc_status', 'not_started')->count();

        $todayVerifications = User::whereDate('created_at', today())
            ->whereNotNull('kyc_reference')
            ->count();

        $thisWeekVerifications = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNotNull('kyc_reference')
            ->count();

        $thisMonthVerifications = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereNotNull('kyc_reference')
            ->count();

        return [
            'total_users' => $totalUsers,
            'verified_users' => $verifiedUsers,
            'pending_users' => $pendingUsers,
            'rejected_users' => $rejectedUsers,
            'not_started_users' => $notStartedUsers,
            'verification_rate' => $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 2) : 0,
            'today_verifications' => $todayVerifications,
            'this_week_verifications' => $thisWeekVerifications,
            'this_month_verifications' => $thisMonthVerifications,
        ];
    }

    /**
     * Get recent KYC verifications
     */
    protected function getRecentVerifications()
    {
        return User::whereNotNull('kyc_reference')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get pending KYC verifications
     */
    protected function getPendingVerifications()
    {
        return User::where('kyc_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();
    }
} 