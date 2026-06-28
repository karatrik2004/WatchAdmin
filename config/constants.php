<?php

return [
    'GST_CODE' => [
        'WOS' => 'WOS'
    ],
    'DEAL_SUPPLIER_STATUS' => [
        'Waiting for Funding' => 'Waiting for Funding',
        'Funded' => 'Funded',
        'Supplier Paid' => 'Supplier Paid',
    ],
    'GST_TYPE' => [
        'GST_FREE' => 'GST Free on Income (0%)',
        'GST_ON_SALES' => 'GST on Sales (10%)',
    ],
    'GST_TYPE_PERCENT' => [
        'GST_FREE' => 0,
        'GST_ON_SALES' => 10,
    ],
    'DS' => '/',
    'MEDIA_URL' => 'uploads/',
    'PROFILE_URL' => 'uploads/users/',
    'STATUS' => ['1' => 'Active', '0' => 'Inactive'],
    'FULL_STATUS' => ['' => 'Select Full set or not', '1' => 'Yes', '0' => 'No'],
    'SEARCH_STATUS' => ['' => 'Select Status', '1' => 'Active', '0' => 'Deactive'],

    'SHOW_RECORD' => ['10' => '10', '20' => '20', '50' => '50', '100' => '100', '18446744073709551615' => 'All'],
    'CURRENCY_ICON' => ['GBP' => '£', 'USD' => '$', 'EUR' => '€', 'AUD' => 'A$'],
    // Currency options used in forms (lowercase keys expected in form inputs)
    'CURRENCIES' => [
        'aud' => 'AUD',
        'usd' => 'USD',
    ],
    'DEFAULT_CURRENCY' => 'aud',
    'OS_MADE' => ['1' => 'Within Australia', '2' => 'Overseas'],
    'DEAL_STATUS' => [
        '1' => 'Unpaid',
        '2' => 'Partly Paid',
        '3' => 'Sold',
        '4' => 'Paid',
    ],
    // Added for DashboardController
    'SEARCH_DEAL_STATUS' => [
        '1' => 'Unpaid',
        '2' => 'Partly Paid',
        '3' => 'Sold',
        '4' => 'Paid',
    ],
    'SEARCH_DEAL_STATUS_STYLE' => [
        '1' => 'bg-warning',
        '2' => 'bg-info',
        '3' => 'bg-success',
        '4' => 'bg-primary',
    ],
    'REVIEW_STATUS' => [
        'under_review' => 'Under Review',
        'reviewed' => 'Reviewed',
    ],
    'REVIEW_STATUS_STYLE' => [
        'under_review' => 'bg-warning',
        'reviewed' => 'bg-success',
    ],
    
];

?>