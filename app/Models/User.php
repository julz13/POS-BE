<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password_hash',
        'pin',
        'role',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'pin',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'timestamp',
            'password_hash' => 'hashed',
        ];
    }

    // Store relationships for multi-store support
    public function stores()
    {
        // Owners have many stores
        return $this->hasMany(Store::class, 'owner_id');
    }

    public function assignedStore()
    {
        // Managers/cashiers can be assigned to a store via assignment table
        // For now, returns first active store they have access to
        return $this->belongsToMany(Store::class, 'user_store_assignments', 'user_id', 'store_id')
            ->withTimestamps();
    }

    // Relationships
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'cashier_id');
    }

    public function purchaseOrdersCreated()
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    public function purchaseOrdersApproved()
    {
        return $this->hasMany(PurchaseOrder::class, 'approved_by');
    }

    public function returnsCreated()
    {
        return $this->hasMany(Return_::class, 'created_by');
    }

    public function returnsApproved()
    {
        return $this->hasMany(Return_::class, 'approved_by');
    }

    public function returnsCompleted()
    {
        return $this->hasMany(Return_::class, 'completed_by');
    }

    public function returnsRejected()
    {
        return $this->hasMany(Return_::class, 'rejected_by');
    }

    public function vouchersIssued()
    {
        return $this->hasMany(Voucher::class, 'issued_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function discountAuditsApplied()
    {
        return $this->hasMany(DiscountAudit::class, 'applied_by');
    }

    public function discountAuditsOverridden()
    {
        return $this->hasMany(DiscountAudit::class, 'overridden_by');
    }

    public function productsCreated()
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function inventoryLedgerEntries()
    {
        return $this->hasMany(InventoryLedger::class, 'user_id');
    }

    public function stockTakesCreated()
    {
        return $this->hasMany(StockTake::class, 'created_by');
    }

    public function stockTakesPosted()
    {
        return $this->hasMany(StockTake::class, 'posted_by');
    }

    public function quotesCreated()
    {
        return $this->hasMany(Quote::class, 'created_by');
    }

    public function salesOrdersCreated()
    {
        return $this->hasMany(SalesOrder::class, 'created_by');
    }

    public function goodsReceivedNotesReceived()
    {
        return $this->hasMany(GoodsReceivedNote::class, 'received_by');
    }

    public function transactionVoids()
    {
        return $this->hasMany(Transaction::class, 'voided_by');
    }

    public function paymentVoids()
    {
        return $this->hasMany(Payment::class, 'voided_by');
    }

    public function transactionItemPriceOverrides()
    {
        return $this->hasMany(TransactionItem::class, 'price_override_approved_by');
    }

    public function voucherUsagesRedeemed()
    {
        return $this->hasMany(VoucherUsage::class, 'redeemed_by');
    }

    public function shiftMovements()
    {
        return $this->hasMany(ShiftMovement::class, 'recorded_by');
    }
}
