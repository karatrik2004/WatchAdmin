<h2>Deal Updated</h2>
<p>A deal has been updated in the system. Here are the updated details:</p>
<ul>
    <li><strong>Deal ID:</strong> {{ $deal->id }}</li>
    <li><strong>Model Number:</strong> {{ $deal->model_number }}</li>
    <li><strong>Serial Number:</strong> {{ $deal->serial_number }}</li>
    <li><strong>Brand:</strong> {{ $deal->watchBrandDetail->name ?? '-' }}</li>
    <li><strong>Reference Number:</strong> {{ $deal->material_watch ?? '-' }}</li>
    <li><strong>Condition:</strong> {{ $deal->condition ?? '-' }}</li>
    <li><strong>Customer Type:</strong> {{ $deal->customer_type }}</li>
    <li><strong>Supplier Name:</strong> {{ trim(($deal->first_name ?? '') . ' ' . ($deal->last_name ?? '')) ?: '-' }}
    </li>
    <li><strong>Dial:</strong> {{ $deal->dial ?? 'N/A' }}</li>
    <li><strong>Purchase Price:</strong> {{ strtoupper($deal->purchase_currency ?? 'AUD') }} {{ $deal->purchase_price }}
    </li>
    <li><strong>Sold Watch Sale Price:</strong>
        {{ strtoupper(optional($deal->dealBuyerDetail)->buyer_currency ?? 'AUD') }}
        {{ $deal->dealBuyerDetail->buyer_sale_price }}
    </li>
    <li><strong>Profit/Loss:</strong> {{ $deal->is_loss ? 'Loss' : 'Profit' }}:
        {{ number_format(abs($deal->profit_amount ?? 0), 2) }}
    </li>
    @if($deal->is_loss && optional($deal->dealBuyerDetail)->buyer_loss_remark)
        <li><strong>Loss Remark:</strong> {{ $deal->dealBuyerDetail->buyer_loss_remark }}</li>
    @endif
    <li><strong>GST Inclusive:</strong>
        {{ config('constants.GST_TYPE.' . optional($deal->dealBuyerDetail)->gst_type) ?? '-' }}</li>
    <li><strong>Status:</strong> {{ $deal->deal_status }}</li>
    @if($deal->dealBuyerDetail)
        <li><strong>Buyer Name:</strong> {{ $deal->dealBuyerDetail->buyer_name }}</li>
        <li><strong>Buyer email:</strong> {{ $deal->dealBuyerDetail->buyer_email ?: '-' }}</li>

        <li><strong>Buyer Country:</strong> {{ $deal->dealBuyerDetail->buyer_country }}</li>
        <li><strong>Buyer State:</strong> {{ $deal->dealBuyerDetail->buyer_state }}</li>
        <li><strong>Buyer City:</strong> {{ $deal->dealBuyerDetail->buyer_city }}</li>
        <li><strong>Buyer Address:</strong> {{ $deal->dealBuyerDetail->buyer_address }}</li>
        <li><strong>Buyer Zipcode:</strong> {{ $deal->dealBuyerDetail->buyer_zipcode }}</li>
        <li><strong>Buyer Sale Price:</strong> {{ $deal->dealBuyerDetail->buyer_sale_price }}</li>
        <li><strong>Invoice Number:</strong> {{ $deal->dealBuyerDetail->invoice_number }}</li>
        <li><strong>Invoice Date:</strong> {{ $deal->dealBuyerDetail->invoice_date }}</li>
    @endif
</ul>
<p>Login to the admin panel for more details.</p>