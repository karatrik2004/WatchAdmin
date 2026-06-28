<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>USD Tax Invoice</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 30px;
            box-sizing: border-box;
        }
        h1, h3 {
            margin: 10px 0;
            font-weight: bold;
        }
        h1 {
            font-size: 22px;
            color: #333;
            text-align: center;
        }
        h3 {
            font-size: 12px;
            color: #333;
        }
        .section {
            margin-bottom: 10px;
        }
        .header-table, .info-table, .item-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        .header-table td {
            padding: 8px;
            box-sizing: border-box;
        }
        .header-table td img {
            max-width: 100px; /* Smaller image size */
        }
        .info-table td, .item-table th, .item-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
            box-sizing: border-box;
        }
        .item-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .item-table td {
            vertical-align: top;
        }
        .total-cell {
            font-weight: bold;
        }
        .right-align {
            text-align: right;
        }
        .delivery-info, .payment-instructions {
            padding: 10px;
            margin-bottom: 10px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        .payment-instructions p, .payment-instructions div {
            font-size: 11px;
            color: #555;
            line-height: 1.8;
            margin: 0 0 8px 0;
        }
        .payment-instructions p:last-child {
            margin-bottom: 0;
        }
        .payment-instructions h3 {
            font-size: 12px;
            color: #333;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<!-- Header Section -->
<table class="header-table">
    <tr>
        <td style="width: 25%; vertical-align: top;">
            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('backend/images/invoice_pdf.jpeg'))) }}" alt="Logo">
        </td>
        <td style="width: 75%; text-align: right; font-size: 11px;">
            <strong>{{ $companyDetail->company_name ?? 'ZMAN WATCHES' }}</strong><br>
            @if(!empty($companyDetail->abn))A.B.N. - {{ $companyDetail->abn }}<br>@endif
            {!! nl2br(e($companyDetail->address ?? '')) !!}
        </td>
    </tr>
</table>

<hr style="border-top: 2px solid #000; margin-bottom: 10px;">

<!-- Invoice Title -->
<h1>Tax Invoice</h1>

<!-- Billing and Invoice Info -->
<div class="section">
    <table class="info-table">
        <tr>
            <td style="width: 48%;">
                <strong>Bill To:</strong><br><br>
                {{$deal->customer_name}}<br>
                {{$deal->customer_address}}<br>
                {{$deal->customer_city}} {{$deal->customer_state}} {{$deal->customer_zipcode}}
            </td>
            <td style="width: 48%;">
                <table style="width: 100%; font-size: 11px;">

                    <tr>
                        <td><strong>Invoice No:</strong></td>
                        <td>{{$deal->invoice_no}}</td>
                    </tr>
                    <tr>
                        <td><strong>Date:</strong></td>
                        <td>{{$deal->date}}</td>
                    </tr>
                    <tr>
                        <td><strong>Your Ref:</strong></td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td><strong>Terms:</strong></td>
                        <td>Net 14</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<!-- Item Details -->
<div class="section">
    <table class="item-table">
        <thead>
        <tr>
            <th style="width: 50%;text-align: center;font-size: 12px;font-weight: bold">Details</th>
            <th style="width: 50%;text-align: center;font-size: 12px;font-weight: bold" class="right-align">Total (USD)</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td style="line-height:1.2">
                <br>
                <strong>Brand:</strong> {{$deal->item_details->brand}}<br>
                <strong>Model:</strong> {{$deal->item_details->model}}<br>
                <strong>Reference:</strong> {{$deal->item_details->reference}}<br>
                <strong>Serial:</strong> {{$deal->item_details->serial}}<br>
                <strong>Year:</strong> {{$deal->item_details->year}}<br>
                <strong>Condition:</strong> {{$deal->item_details->condition}}<br>
                <strong>Complete Set:</strong> {{$deal->item_details->complete_set}}<br>
                <strong>Dial:</strong> {{$deal->item_details->dial ?? "N/A"}}
                <br>
            </td>
            <td class="right-align" style="text-align: right">{{$deal->subtotal}}</td>
        </tr>
        </tbody>
    </table>
</div>

<!-- Delivery Info and Totals -->
<div class="section">
    <table class="info-table" style="width: 100%;">
        <tr>
            <!-- Left block: Delivery Information + Address -->
            <td style="width: 60%; vertical-align: top;" class="delivery-info">
                <table style="width: 100%;">
                    <tr>
                        <!-- Delivery Information (30%) -->
                        <td style="width: 50%; vertical-align: top;">
                            <h3>Delivery Information</h3>
                            <p>
                                <strong>Delivery via:</strong>
                                {{$deal->delivery_method ?? 'N/A'}}<br>
                                <strong>Delivery Date:</strong>
                                {{$deal->delivery_date ?? 'N/A'}}<br>
                                <strong>Salesperson:</strong>
                                {{$deal->salesperson ?? 'N/A'}}<br>
                            </p>
                        </td>

                        <!-- Delivery Address (30%) -->
                        <td style="width: 50%; vertical-align: top;">
                            <h3>Delivery Address</h3>
                            <p>
                                {{$deal->customer_address}}<br>
                                {{$deal->customer_city}} {{$deal->customer_state}} {{$deal->customer_zipcode}}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>


            <!-- Right block: Itemized Costs -->
            <td style="width: 40%; vertical-align: top; padding-left: 10px; border-left: 2px solid #ccc;" class="delivery-info">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; color: #333;">
                    <!-- Empty rows if needed for spacing -->
                    <tr>
                        <td colspan="2" style="padding: 8px; border: 1px solid #ccc;"></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 8px; border: 1px solid #ccc;"></td>
                    </tr>

                    <!-- Item rows with borders -->
                    <tr>
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">Subtotal (USD):</td>
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">${{$deal->subtotal}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">Freight (+S&amp;T):</td>
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">0.00</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">GST:</td>
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">${{$deal->gst_amount}}</td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f2f2f2;">
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">Total USD:</td>
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">${{$deal->total}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">Paid to Date:</td>
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">0.00</td>
                    </tr>
                    <tr style="font-weight: bold; background-color: #f9f9f9;">
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">Balance Due:</td>
                        <td style="padding: 8px; text-align: right; border: 1px solid #ccc;">${{$deal->amount_due}}</td>
                    </tr>
                </table>
            </td>


        </tr>
    </table>
</div>


<!-- Payment Instructions -->
<div class="section payment-instructions">
    <h3 style="font-size: 14px;text-align: left">How to Pay</h3>
    <div>
        @php
            $usdBank = $companyDetail->bank_details_usd ?? '';
            $usdBank = preg_replace('/<p[^>]*>/', '', $usdBank);
            $usdBank = preg_replace('/<\/p>/', '<br/>', $usdBank);
            $usdBank = trim(preg_replace('/(<br\/>)+$/', '', trim($usdBank)));
        @endphp
        {!! $usdBank !!}
    </div>
</div>

</body>
</html>
