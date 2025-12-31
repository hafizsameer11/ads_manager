<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'invoiceable_type',
        'invoiceable_id',
        'transaction_id',
        'type',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'invoice_date',
        'due_date',
        'paid_at',
        'notes',
        'invoice_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'date',
        'invoice_data' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            if (empty($invoice->invoice_date)) {
                $invoice->invoice_date = now();
            }
            if (empty($invoice->total_amount)) {
                $invoice->total_amount = $invoice->amount + $invoice->tax_amount;
            }
        });
    }

    /**
     * Get the parent invoiceable model (advertiser or publisher).
     */
    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the transaction associated with this invoice.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Generate unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Format: INV-YYYYMM-XXXXX
        $lastInvoice = self::where('invoice_number', 'like', "INV-{$year}{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }
        
        return "INV-{$year}{$month}-{$newNumber}";
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(): bool
    {
        return $this->update([
            'status' => 'sent',
        ]);
    }

    /**
     * Scope to get paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope to get pending invoices.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'sent']);
    }
}
