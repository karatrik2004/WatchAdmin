<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingInvoiceItem extends Model
{
    protected $table = 'shipping_invoice_items';

    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'amount'     => 'decimal:2',
    ];

    public function shippingInvoice()
    {
        return $this->belongsTo(ShippingInvoice::class, 'shipping_invoice_id');
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    /** Uppercase code from `shipping_invoice_items.currency` (defaults to USD if unset on legacy rows). */
    public function lineCurrencyCode(): string
    {
        $c = $this->currency;

        return $c ? strtoupper((string) $c) : 'USD';
    }
}
