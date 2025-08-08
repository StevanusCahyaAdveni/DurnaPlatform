<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount',
        'withdrawal_type',
        'bank_code',
        'account_number',
        'account_holder_name',
        'ewallet_type',
        'phone_number',
        'status',
        'xendit_disbursement_id',
        'external_id',
        'processed_at',
        'admin_fee',
        'total_amount',
        'notes'
    ];

    protected $casts = [
        'user_id' => 'string',
        'processed_at' => 'datetime',
        'deleted_at' => 'datetime',
        'amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function getStatusBadgeColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format((float)$this->amount, 0, 0, '.');
    }

    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format((float)$this->total_amount, 0, 0, '.');
    }
}
