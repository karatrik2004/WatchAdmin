<h2>Deal Reviewed - Ready for Funding</h2>
<p>The following deal has been reviewed and is ready for funding.</p>
<ul>
    <li><strong>Deal ID:</strong> {{ $deal->id }}</li>
    <li><strong>Brand:</strong> {{ $deal->watchBrandDetail->name ?? 'N/A' }}</li>
    <li><strong>Model:</strong> {{ $deal->model_number }}</li>
    <li><strong>Serial Number:</strong> {{ $deal->serial_number }}</li>
    <li><strong>Reference Number:</strong> {{ $deal->material_watch ?? 'N/A' }}</li>
    <li><strong>Dial:</strong> {{ $deal->dial ?? 'N/A' }}</li>
    <li><strong>Condition:</strong> {{ $deal->condition ?? 'N/A' }}</li>
    <li><strong>Year:</strong> {{ $deal->year ?? 'N/A' }}</li>
    <li><strong>Purchase Price:</strong> {{ strtoupper($deal->purchase_currency ?? 'AUD') }} {{ number_format((float) $deal->purchase_price, 2) }}</li>
    <li><strong>Review Status:</strong> Reviewed</li>
</ul>
<p>Please proceed with funding action for this deal.</p>
