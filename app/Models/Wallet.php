<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

/**
 * App\Models\Wallet
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $wallet_address
 * @property float $crypto_balance
 * @property float $fiat_balance
 * @property float $balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'wallet_address',
        'wallet_type', // 'metamask', 'hardware', 'external'
        'balance',
        'fiat_balance',
        'is_active'
    ];

    protected $hidden = [
        // Remove private_key from fillable and hidden
    ];

    protected $casts = [
        'balance' => 'decimal:18',
        'fiat_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns this wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions related to this wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Generate a new wallet address (for external wallet services)
     */
    public function generateWalletAddress(): string
    {
        // This should be replaced with actual wallet generation logic
        // For now, we'll use a placeholder
        return '0x' . bin2hex(random_bytes(20));
    }

    /**
     * Update wallet balance from external source
     */
    public function updateBalance(float $newBalance): void
    {
        $this->update(['balance' => $newBalance]);
    }

    /**
     * Check if wallet is connected to external service
     */
    public function isConnected(): bool
    {
        return !empty($this->wallet_address) && $this->is_active;
    }

    /**
     * Get wallet connection status
     */
    public function getConnectionStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        
        if (empty($this->wallet_address)) {
            return 'not_connected';
        }
        
        return 'connected';
    }
}
