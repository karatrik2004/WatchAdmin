@php
    $saleBuyerDetail = $deal->dealBuyerDetail;
    $saleCurrency = strtoupper(optional($saleBuyerDetail)->buyer_currency ?? $deal->purchase_currency ?? 'AUD');
    $purchaseCurrency = strtoupper($deal->purchase_currency ?? 'AUD');
    $convertedSaleAmount = $deal->sale_in_purchase_currency ?? null;
    $showConvertedAmount = $convertedSaleAmount !== null && strtolower($saleCurrency) !== strtolower($purchaseCurrency);
@endphp

<h2>Deal Updated</h2>
<p>A deal has been updated in the system. Here are the updated details:</p>
<ul>
    <li><strong>Watch ID:</strong> {{ $deal->watch_id ?? '' }}</li>
    <li><strong>Brand:</strong> {{ $deal->watchBrandDetail->name ?? '-' }}</li>
    <li><strong>Model:</strong> {{ $deal->model_number ?? '-' }}</li>
    <li><strong>Serial Number:</strong> {{ $deal->serial_number ?? '-' }}</li>
    <li><strong>Reference Number:</strong> {{ $deal->material_watch ?? '-' }}</li>
    <li><strong>Condition:</strong> {{ $deal->condition ?? '-' }}</li>
    <li><strong>Dial:</strong> {{ $deal->dial ?? 'N/A' }}</li>
    <li><strong>Year:</strong> {{ $deal->year ?? 'N/A' }}</li>

     @if($deal->dealBuyerDetail)
        <li><strong>Sales Date:</strong> {{ $deal->dealBuyerDetail->invoice_date }}</li>
        <li><strong>Customer Name:</strong> {{ $deal->dealBuyerDetail->buyer_name }}</li>
        <li><strong>Sales Invoice Number:</strong> {{ $deal->dealBuyerDetail->invoice_number }}</li>
        <li><strong>Purchase Price:</strong> {{ $purchaseCurrency }} {{ number_format((float) $deal->purchase_price, 2) }}</li>
        <li><strong>Sales Price:</strong>
            {{ $saleCurrency }} {{ number_format((float) optional($deal->dealBuyerDetail)->buyer_sale_price, 2) }}
        </li>
        @if($showConvertedAmount)
            <li><strong>Sales Price Converted to {{ $purchaseCurrency }}:</strong> {{ $purchaseCurrency }} {{ number_format((float) $convertedSaleAmount, 2) }}</li>
        @endif
        <li><strong>Profit/Loss:</strong> {{ $deal->is_loss ? 'Loss' : 'Profit' }}:
            {{ $purchaseCurrency }} {{ number_format(abs((float) ($deal->profit_amount ?? 0)), 2) }}
        </li>

        @if($deal->is_loss && optional($deal->dealBuyerDetail)->buyer_loss_remark)
            <li><strong>Loss Remark:</strong> {{ $deal->dealBuyerDetail->buyer_loss_remark }}</li>
        @endif

        {{-- <li><strong>Buyer email:</strong> {{ $deal->dealBuyerDetail->buyer_email ?: '-' }}</li>
        <li><strong>Buyer Country:</strong> {{ $deal->dealBuyerDetail->buyer_country }}</li>
        <li><strong>Buyer State:</strong> {{ $deal->dealBuyerDetail->buyer_state }}</li>
        <li><strong>Buyer City:</strong> {{ $deal->dealBuyerDetail->buyer_city }}</li>
        <li><strong>Buyer Address:</strong> {{ $deal->dealBuyerDetail->buyer_address }}</li>
        <li><strong>Buyer Zipcode:</strong> {{ $deal->dealBuyerDetail->buyer_zipcode }}</li>
        <li><strong>Buyer Sale Price:</strong> {{ $deal->dealBuyerDetail->buyer_sale_price }}</li> --}}
       
        
    @endif    
   
   
</ul>
<p>Login to the admin panel for more details.</p>