<h2>New Deal Created</h2>
<p>A new deal has been created in the system. Here are the details:</p>
<ul>
    <li><strong>Deal ID:</strong> {{ $deal->id }}</li>
    <li><strong>Brand:</strong> {{ $deal->watchBrandDetail->name ?? 'N/A' }}</li>
    <li><strong>Model Number:</strong> {{ $deal->model_number }}</li>
    <li><strong>Serial Number:</strong> {{ $deal->serial_number }}</li>
    <li><strong>Reference Number:</strong> {{ $deal->material_watch ?? 'N/A' }}</li>
    <li><strong>Condition:</strong> {{ $deal->condition ?? 'N/A' }}</li>
    <li><strong>Customer Type:</strong> {{ $deal->customer_type }}</li>
    <li><strong>Supplier Name:</strong> {{ trim(($deal->first_name ?? '') . ' ' . ($deal->last_name ?? '')) ?: 'N/A' }}
    </li>
    <li><strong>Dial:</strong> {{ $deal->dial ?? 'N/A' }}</li>
    <li><strong>Purchase Price:</strong> {{ strtoupper($deal->purchase_currency ?? 'AUD') }}
        {{ number_format((float) $deal->purchase_price, 2) }}
    </li>
    <li><strong>Sale Price:</strong> {{ $deal->sale_price }}</li>
    <li><strong>Profit/Loss:</strong>
        @if(!is_null($deal->profit_amount))
            <span style="color: {{ $deal->is_loss ? 'red' : 'green' }};">
                {{ $deal->is_loss ? 'Loss' : 'Profit' }}: {{ strtoupper($deal->profit_currency ?? 'AUD') }}
                {{ number_format(abs((float) $deal->profit_amount), 2) }}
            </span>
        @else
            N/A
        @endif
    </li>

</ul>