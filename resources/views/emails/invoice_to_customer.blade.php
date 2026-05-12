<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333; line-height: 1.5;">
    @php
        $customerName = optional($deal->dealBuyerDetail)->buyer_name
            ?? ($deal->customer_type === 'individual'
                ? trim(($deal->first_name ?? '') . ' ' . ($deal->last_name ?? ''))
                : optional($deal->dealCustomerTypeDetail)->company_name);
    @endphp

    <p>Dear {{ $customerName ?: 'Customer' }},</p>

    <p>Thank you for your purchase.</p>

    <p>Please find your invoice attached to this email for your records.</p>

    <p>Please kindly arrange the payment at your earliest convenience.</p>

    <p>If you have any questions in the meantime, or require any further assistance, please do not hesitate to contact
        us via <a href="mailto:shared_finance@bcig.com.au">shared_finance@bcig.com.au</a>.</p>

    <p>Kind regards,<br>Atarah Group Pty Ltd</p>

</body>

</html>