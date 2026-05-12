<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class ShippingInvoice extends Model
{
    use SoftDeletes;

    protected $table = 'shipping_invoices';

    protected $guarded = [];

    protected $casts = [
        'invoice_date'   => 'date',
        'total_amount'   => 'decimal:2', // may be null when line deals use multiple buyer currencies
    ];

    public function items()
    {
        return $this->hasMany(ShippingInvoiceItem::class, 'shipping_invoice_id');
    }

    /**
     * Distinct line-level currency codes (uppercase) from related deals' buyer details.
     * Eager load items.deal.dealBuyerDetail to avoid N+1.
     */
    public function lineCurrencies(): Collection
    {
        return $this->items
            ->map(fn (ShippingInvoiceItem $item) => $item->lineCurrencyCode())
            ->unique()
            ->values();
    }

    public function isMultiCurrency(): bool
    {
        return $this->lineCurrencies()->count() > 1;
    }

    public function displayCurrencyLabel(): string
    {
        return $this->isMultiCurrency() ? 'Multi-currency' : (string) $this->lineCurrencies()->first();
    }

    /** "Multi" or e.g. "USD" for list tables. */
    public function displayCurrencyShort(): string
    {
        return $this->isMultiCurrency() ? 'Multi' : (string) $this->lineCurrencies()->first();
    }

    public function primaryLineCurrencyCode(): string
    {
        return (string) $this->lineCurrencies()->first() ?: 'USD';
    }

    /**
     * Generate the next sequential invoice number in the format SI-YYYY-NNNN
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = 'SI-' . $year . '-';
        $last = self::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
