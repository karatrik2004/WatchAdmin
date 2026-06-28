<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Shipping Invoice</title>
    <style>
        .bank-details-block {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    @php
        $logoPath = public_path('backend/images/invoice_pdf.jpeg');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;margin-bottom:8px;">
        <tr>
            <td
                style="font-size:28px;color:#111;letter-spacing:1px;font-family:Helvetica,Arial,sans-serif;text-align:left;">
                <b>Shipping Invoice</b>
            </td>
        </tr>
    </table>

    <br>

    <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;">
        <tr>
            <td style="width:18%;vertical-align:top;border:none;padding-right:12px;">
                @if($logoBase64)
                    <img src="data:image/jpeg;base64,{{ $logoBase64 }}" width="90" alt="Logo"
                        style="display:block;margin-bottom:10px;">
                @endif
            </td>
            <td style="width:60%;vertical-align:top;border:none;padding-left:8px;">
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;">
                    <tr>
                        <td style="color:#111;font-size:12px;padding-bottom:2px;"><b>SHIPPER</b></td>
                    </tr>
                    <tr>
                        <td style="color:#111;font-size:12px;padding-bottom:2px;"><b>Atarah Group Pty Ltd</b></td>
                    </tr>
                    <tr>
                        <td style="font-size:11px;padding-bottom:2px;">Collins st tower 5, level 22</td>
                    </tr>
                    <tr>
                        <td style="font-size:11px;">727 Collins st Docklands, Victoria, Melbourne</td>
                    </tr>
                </table>
            </td>
            <td style="width:22%;vertical-align:top;text-align:right;border:none;padding-left:8px;">
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;">
                    <tr>
                        <td style="color:#111;font-size:12px;white-space:nowrap;"><b>Date:
                                {{ $invoice->invoice_date->format('d/m/Y') }}</b></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:none;"></td>
            <td style="width:60%;vertical-align:top;border:none;" colspan="1">
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;">
                    <tr>
                        <td style="color:#111;font-size:12px;padding-bottom:2px;"><b>RECEIVER</b></td>
                    </tr>
                    <tr>
                        <td style="color:#111;font-size:12px;padding-bottom:2px;"><b>Time Universe watch company
                                limited</b></td>
                    </tr>
                    <tr>
                        <td style="font-size:11px;padding-bottom:2px;">12/F CHINACHEM CAMERON CENTRE</td>
                    </tr>
                    <tr>
                        <td style="font-size:11px;">42 CAMERON ROAD TSIM SHA TSUI KOWLOON</td>
                    </tr>
                </table>
            </td>
            <td style="border:none;"></td>
        </tr>
    </table>

    <br><br>

    <table cellpadding="0" cellspacing="0" border="0"
        style="width:100%;border-collapse:collapse;border:1px solid #b0b7c3;table-layout:fixed;">
        <colgroup>
            <col style="width:6%;">
            <col style="width:44%;">
            <col style="width:8%;">
            <col style="width:16%;">
            <col style="width:8%;">
            <col style="width:18%;">
        </colgroup>
        <thead>
            <tr style="background:#f0f2f5;">
                <th style="border:1px solid #b0b7c3;padding:8px 6px;text-align:center;font-size:10px;"><b>#</b></th>
                <th style="border:1px solid #b0b7c3;padding:8px 5px;text-align:center;font-size:10px;">
                    <b>Description</b>
                </th>
                <th style="border:1px solid #b0b7c3;padding:8px 6px;text-align:center;font-size:10px;"><b>Qty</b></th>
                <th style="border:1px solid #b0b7c3;padding:8px 5px;text-align:center;font-size:10px;"><b>Unit price</b>
                </th>
                <th style="border:1px solid #b0b7c3;padding:8px 6px;text-align:center;font-size:10px;"><b>GST</b></th>
                <th style="border:1px solid #b0b7c3;padding:8px 5px;text-align:center;font-size:10px;"><b>Amount</b>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr nobr="true" style="vertical-align:top;">
                    <td
                        style="border:1px solid #b0b7c3;padding:8px 6px;text-align:center;vertical-align:middle;font-size:10px;">
                        {{ $index + 1 }}
                    </td>
                    <td
                        style="border:1px solid #b0b7c3;padding:8px 5px;line-height:1.6;text-align:left;vertical-align:top;font-size:10px;">
                        @foreach(array_filter(explode("\n", $item['description'])) as $line)
                            {{ $line }}<br />
                        @endforeach
                    </td>
                    <td
                        style="border:1px solid #b0b7c3;padding:8px 6px;text-align:center;vertical-align:middle;font-size:10px;">
                        1</td>
                    <td
                        style="border:1px solid #b0b7c3;padding:8px 5px;text-align:right;vertical-align:middle;font-size:10px;">
                        {{ $item['unit_price'] }}
                    </td>
                    <td
                        style="border:1px solid #b0b7c3;padding:8px 6px;text-align:center;vertical-align:middle;font-size:10px;">
                        {{ $item['gst'] }}
                    </td>
                    <td
                        style="border:1px solid #b0b7c3;padding:8px 5px;text-align:right;vertical-align:middle;font-size:10px;">
                        {{ $item['amount'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        @if(!empty($showTotalsInPdf))
            <tfoot>
                <tr>
                    <td colspan="4"
                        style="border:1px solid #b0b7c3;padding:7px 6px;text-align:right;vertical-align:middle;font-size:10px;">
                        <b>Subtotal</b>
                    </td>
                    <td style="border:1px solid #b0b7c3;padding:7px 6px;text-align:center;vertical-align:middle;font-size:10px;">
                        -
                    </td>
                    <td style="border:1px solid #b0b7c3;padding:7px 5px;text-align:right;vertical-align:middle;font-size:10px;">
                        <b>{{ $subtotalLabel }}</b>
                    </td>
                </tr>
                <tr>
                    <td colspan="4"
                        style="border:1px solid #b0b7c3;padding:7px 6px;text-align:right;vertical-align:middle;font-size:10px;">
                        <b>Total</b>
                    </td>
                    <td style="border:1px solid #b0b7c3;padding:7px 6px;text-align:center;vertical-align:middle;font-size:10px;">
                        -
                    </td>
                    <td style="border:1px solid #b0b7c3;padding:7px 5px;text-align:right;vertical-align:middle;font-size:10px;">
                        <b>{{ $totalLabel }}</b>
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    @if($invoice->notes)
        <br>
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;">
            <tr>
                <td style="font-size:11px;color:#111;padding:4px 0;"><b>Notes:</b> {{ $invoice->notes }}</td>
            </tr>
        </table>
    @endif



    <div class="bank-details-block" style="margin-top:14px;" nobr="true">
        @if(!empty($companyDetail->bank_details))
            <div style="font-size:10.5px;line-height:1.6;color:#111;">
                {!! $companyDetail->bank_details !!}
            </div>
        @else
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;" nobr="true">
                <tr>
                    <td style="font-size:10.5px;color:#111;width:18%;padding:2px 0;"><b>NAME:</b></td>
                    <td style="font-size:10.5px;color:#111;width:82%;padding:2px 0;"><b>Atarah Group P/L</b></td>
                </tr>
                <tr>
                    <td style="font-size:10.5px;color:#111;width:18%;padding:2px 0;"><b>BSB:</b></td>
                    <td style="font-size:10.5px;color:#111;width:82%;padding:2px 0;"><b>012606</b></td>
                </tr>
                <tr>
                    <td style="font-size:10.5px;color:#111;width:18%;padding:2px 0;"><b>ACCOUNT:</b></td>
                    <td style="font-size:10.5px;color:#111;width:82%;padding:2px 0;"><b>797503878</b></td>
                </tr>
            </table>
        @endif

        @if(!empty($companyDetail->bank_details_usd))
            <div style="margin-top:10px;font-size:10px;line-height:1.6;color:#111;">
                {!! $companyDetail->bank_details_usd !!}
            </div>
        @else
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;margin-top:10px;" nobr="true">
                <tr>
                    <td colspan="2" style="font-size:11px;color:#111;padding:4px 0 6px 0;"><b>For all USD payments to ZMAN
                            Watches, please remit to below account.</b></td>
                </tr>
                <tr>
                    <td style="font-size:10px;color:#111;width:25%;padding:3px 0;"><b>Account Name:</b></td>
                    <td style="font-size:10px;color:#111;width:75%;padding:3px 0;"><b>Atarah Group Pty Ltd</b></td>
                </tr>
                <tr>
                    <td style="font-size:10px;color:#111;width:25%;padding:3px 0;"><b>Bank:</b></td>
                    <td style="font-size:10px;color:#111;width:75%;padding:3px 0;"><b>ANZ</b></td>
                </tr>
                <tr>
                    <td style="font-size:10px;color:#111;width:25%;padding:3px 0;"><b>BSB Number:</b></td>
                    <td style="font-size:10px;color:#111;width:75%;padding:3px 0;"><b>012-052</b></td>
                </tr>
                <tr>
                    <td style="font-size:10px;color:#111;width:25%;padding:3px 0;"><b>Account Number:</b></td>
                    <td style="font-size:10px;color:#111;width:75%;padding:3px 0;"><b>945303USD00001</b></td>
                </tr>
                <tr>
                    <td style="font-size:10px;color:#111;width:25%;padding:3px 0;"><b>Swift Code:</b></td>
                    <td style="font-size:10px;color:#111;width:75%;padding:3px 0;"><b>ANZBAU3M</b></td>
                </tr>
                <tr>
                    <td style="font-size:10px;color:#111;width:25%;padding:3px 0;"><b>Bank Address:</b></td>
                    <td style="font-size:10px;color:#111;width:75%;padding:3px 0;"><b>23/100 Queen St, Melbourne VIC 3000
                            Australia</b></td>
                </tr>
                <tr>
                    <td style="font-size:10px;color:#111;width:25%;padding:3px 0;"><b>Beneficiary Address:</b></td>
                    <td style="font-size:10px;color:#111;width:75%;padding:3px 0;"><b>15 Springfield Avenue St Kilda East VIC
                            3183 Australia</b></td>
                </tr>
            </table>
        @endif
    </div>

</body>

</html>