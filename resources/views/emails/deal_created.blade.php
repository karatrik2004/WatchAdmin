@php
    $purchasePrice = (float) ($deal->purchase_price ?? 0);
    $estimatedSalePrice = (float) ($deal->sale_price ?? 0);
    $profitLossAmount = $estimatedSalePrice - $purchasePrice;
    $currency = strtoupper($deal->purchase_currency ?? 'AUD');
    $isLoss = $profitLossAmount < 0;
@endphp

<h2>New Deal Created</h2>
<p>A new deal has been created in the system. Here are the details:</p>
<ul>
    <li><strong>Watch ID:</strong> {{ $deal->watch_id ?? $deal->id }}</li>
    <li><strong>Brand:</strong> {{ $deal->watchBrandDetail->name ?? 'N/A' }}</li>
    <li><strong>Model:</strong> {{ $deal->model_number ?? 'N/A' }}</li>
    <li><strong>Serial Number:</strong> {{ $deal->serial_number ?? 'N/A' }}</li>
    <li><strong>Reference Number:</strong> {{ $deal->material_watch ?? 'N/A' }}</li>
    <li><strong>Condition:</strong> {{ $deal->condition ?? 'N/A' }}</li>
    <li><strong>Dial:</strong> {{ $deal->dial ?? 'N/A' }}</li>
    <li><strong>Year:</strong> {{ $deal->year ?? 'N/A' }}</li>
    <li><strong>Purchase Date:</strong> {{ !empty($deal->created_at) ? \Carbon\Carbon::parse($deal->created_at)->format('d-m-Y') : 'N/A' }}</li>
    <li><strong>Supplier Name:</strong> {{ $deal->customer_type === 'company' ? (optional($deal->dealCustomerTypeDetail)->company_name ?? 'N/A') : trim(($deal->first_name ?? '') . ' ' . ($deal->last_name ?? '')) ?: 'N/A' }}</li>
    <li><strong>Purchase Invoice Number:</strong> {{ $deal->purchase_invoice_number ?? 'N/A' }}</li>
    <li><strong>Purchase Price:</strong> {{ $currency }} {{ number_format($purchasePrice, 2) }}</li>
    <li><strong>Estimated Sale Price:</strong> {{ $currency }} {{ number_format($estimatedSalePrice, 2) }}</li>
    <li><strong>Estimated Profit/Loss:</strong>
        @if($deal->sale_price !== null && $deal->purchase_price !== null)
            <span style="color: {{ $isLoss ? 'red' : 'green' }};">
                {{ $isLoss ? 'Loss' : 'Profit' }}: {{ $currency }} {{ number_format(abs($profitLossAmount), 2) }}
            </span>
        @else
            N/A
        @endif
    </li>
</ul>