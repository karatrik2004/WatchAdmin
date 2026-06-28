<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<title>Sales Invoice</title>
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
				<b>Sales Invoice</b>
			</td>
		</tr>
	</table>



	<table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;margin-bottom:8px;">
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
						<td style="color:#111;font-size:12px;padding-bottom:2px;">
							<b>{{ $shippingInvoice->shipper_name }}</b>
						</td>
					</tr>
					<tr>
						<td style="font-size:11px;padding-bottom:2px;">{{ $shippingInvoice->shipper_address_line_1 }}
						</td>
					</tr>
					<tr>
						<td style="font-size:11px;">{{ $shippingInvoice->shipper_address_line_2 }}</td>
					</tr>
				</table>
			</td>
			<td style="width:22%;vertical-align:top;text-align:right;border:none;padding-left:8px;">
				<table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;">
					<tr>
						<td style="color:#111;font-size:12px;white-space:nowrap;"><b>&nbsp;</b></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td style="border:none;"></td>
			<td style="width:60%;vertical-align:top;border:none;">
				<table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;">
					<tr>
						<td style="color:#111;font-size:12px;padding-bottom:2px;"><b>RECEIVER</b></td>
					</tr>
					<tr>
						<td style="color:#111;font-size:12px;padding-bottom:2px;">
							<b>{{ $shippingInvoice->receiver_name }}</b>
						</td>
					</tr>
					<tr>
						<td style="font-size:11px;padding-bottom:2px;">{{ $shippingInvoice->receiver_address_line_1 }}
						</td>
					</tr>
					<tr>
						<td style="font-size:11px;">{{ $shippingInvoice->receiver_address_line_2 }}</td>
					</tr>
				</table>
			</td>
			<td style="width:22%;vertical-align:top;text-align:right;border:none;">
				<table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;">
					<tr>
						<td style="color:#111;font-size:12px;white-space:nowrap;"><b>Date
								{{ $shippingInvoice->date }}</b></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<table cellpadding="0" cellspacing="0" border="0"
		style="width:100%;border-collapse:collapse;margin-top:10px;border:1px solid #b0b7c3;table-layout:fixed;">
		<colgroup>
			<col style="width:43%;">
			<col style="width:10%;">
			<col style="width:22%;">
			<col style="width:7%;">
			<col style="width:18%;">
		</colgroup>
		<thead>
			<tr style="background:#f0f2f5;">
				<th
					style="border:1px solid #b0b7c3;padding:6px 4px;text-align:center;color:#111;font-size:11px;letter-spacing:0.5px;width:43%;">
					<b>Description</b>
				</th>
				<th
					style="border:1px solid #b0b7c3;padding:6px 4px;text-align:center;color:#111;font-size:11px;letter-spacing:0.5px;width:10%;">
					<b>Quantity</b>
				</th>
				<th
					style="border:1px solid #b0b7c3;padding:6px 4px;text-align:center;color:#111;font-size:11px;letter-spacing:0.5px;width:22%;">
					<b>Unit price</b>
				</th>
				<th
					style="border:1px solid #b0b7c3;padding:6px 4px;text-align:center;color:#111;font-size:11px;letter-spacing:0.5px;width:7%;">
					<b>GST</b>
				</th>
				<th
					style="border:1px solid #b0b7c3;padding:6px 4px;text-align:center;color:#111;font-size:11px;letter-spacing:0.5px;width:18%;">
					<b>Amount</b>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr style="vertical-align:top;">
				<td
					style="border:1px solid #b0b7c3;padding:8px 6px;line-height:1.6;text-align:left;vertical-align:top;width:43%;">
					@php
						$lines = array_filter(explode("\n", $shippingInvoice->item_description));
					@endphp
					@foreach($lines as $line)
						{{ $line }}<br />
					@endforeach
				</td>
				<td style="border:1px solid #b0b7c3;padding:8px 6px;text-align:center;vertical-align:middle;width:10%;">
					1</td>
				<td style="border:1px solid #b0b7c3;padding:8px 6px;text-align:center;vertical-align:middle;width:22%;">
					{{ $shippingInvoice->unit_price }}
				</td>
				<td style="border:1px solid #b0b7c3;padding:8px 6px;text-align:center;vertical-align:middle;width:7%;">
					{{ !empty($shippingInvoice->gst_percent_numeric) && $shippingInvoice->gst_percent_numeric > 0 ? $shippingInvoice->gst_percent_numeric . '%' : (is_string($shippingInvoice->gst) ? $shippingInvoice->gst : '-') }}
				</td>
				<td style="border:1px solid #b0b7c3;padding:8px 6px;text-align:center;vertical-align:middle;width:18%;">
					{{ $shippingInvoice->unit_price }}
				</td>
			</tr>
		</tbody>
	</table>

	<table cellpadding="0" cellspacing="0" border="0"
		style="width:100%;border-collapse:collapse;margin-top:8px;border:1px solid #b0b7c3;table-layout:fixed;">
		<tr style="vertical-align:top;">
			<td style="width:82%;border:1px solid #b0b7c3;padding:8px 6px;text-align:right;color:#111;font-size:11px;">
				<b>Subtotal</b>
			</td>
			<td style="width:18%;border:1px solid #b0b7c3;padding:8px 6px;text-align:right;color:#111;font-size:11px;">
				<b>{{ $shippingInvoice->subtotal }}</b>
			</td>
		</tr>
		<tr style="vertical-align:top;">
			<td style="border:1px solid #b0b7c3;padding:8px 6px;text-align:right;">GST</td>
			<td style="border:1px solid #b0b7c3;padding:8px 6px;text-align:right;">{{ $shippingInvoice->gst_amount }}
			</td>
		</tr>
		<tr style="vertical-align:top;">
			<td style="border:1px solid #b0b7c3;padding:8px 6px;text-align:right;color:#111;font-size:11px;"><b>Total
					with GST</b></td>
			<td style="border:1px solid #b0b7c3;padding:8px 6px;text-align:right;color:#111;font-size:11px;">
				<b>{{ $shippingInvoice->total_with_gst }}</b>
			</td>
		</tr>
		<tr style="vertical-align:top;">
			<td style="border:1px solid #b0b7c3;padding:8px 6px;text-align:right;">Total Paid:</td>
			<td style="border:1px solid #b0b7c3;padding:8px 6px;text-align:right;">{{ $shippingInvoice->total_paid }}
			</td>
		</tr>
	</table>

	<table cellpadding="0" cellspacing="0" border="0"
		style="width:100%;border-collapse:collapse;margin-top:6px;border:none;table-layout:fixed;">
		<tr style="vertical-align:top;">
			<td style="width:75%;text-align:right;border:none;color:#111;font-size:11px;padding:6px 0;"><b>Balance
					Outstanding</b></td>
			<td style="width:25%;text-align:right;border:none;color:#111;font-size:11px;padding:6px 0;">
				<b>{{ $shippingInvoice->balance_outstanding }}</b>
			</td>
		</tr>
	</table>



	@if(!empty($companyDetail->bank_details))
		<div style="margin-top:8px;font-size:11px;color:#111;">
			{!! $companyDetail->bank_details !!}
		</div>
	@else
		<table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;margin-top:8px;">
			<tr>
				<td style="font-size:11px;color:#111;width:15%;"><b>NAME:</b></td>
				<td style="font-size:11px;color:#111;width:85%;"><b>{{ $shippingInvoice->name }}</b></td>
			</tr>
			<tr>
				<td style="font-size:11px;color:#111;width:15%;"><b>BSB:</b></td>
				<td style="font-size:11px;color:#111;width:85%;"><b>{{ $shippingInvoice->bsb }}</b></td>
			</tr>
			<tr>
				<td style="font-size:11px;color:#111;width:15%;"><b>ACCOUNT:</b></td>
				<td style="font-size:11px;color:#111;width:85%;"><b>{{ $shippingInvoice->account }}</b></td>
			</tr>
		</table>
	@endif

	@if(!empty($companyDetail->bank_details_usd))
		<div style="margin-top:2px;font-size:10px;color:#111;">
			{!! $companyDetail->bank_details_usd !!}
		</div>
	@else
		<table cellpadding="0" cellspacing="0" border="0" style="width:100%;border:none;margin-top:8px;">
			<tr>
				<td colspan="2" style="font-size:12px;color:#111;padding-bottom:6px;"><b>For all USD payments to ZMAN
						Watches, please remit to below account.</b></td>
			</tr>
			<tr>
				<td style="font-size:10px;color:#111;width:25%;"><b>Account Name:</b></td>
				<td style="font-size:10px;color:#111;width:75%;"><b>{{ $shippingInvoice->bank_account_name }}</b></td>
			</tr>
			<tr>
				<td style="font-size:10px;color:#111;width:25%;"><b>Bank:</b></td>
				<td style="font-size:10px;color:#111;width:75%;"><b>{{ $shippingInvoice->bank_name }}</b></td>
			</tr>
			<tr>
				<td style="font-size:10px;color:#111;width:25%;"><b>BSB Number:</b></td>
				<td style="font-size:10px;color:#111;width:75%;"><b>{{ $shippingInvoice->bsb }}</b></td>
			</tr>
			<tr>
				<td style="font-size:10px;color:#111;width:25%;"><b>Account Number:</b></td>
				<td style="font-size:10px;color:#111;width:75%;"><b>{{ $shippingInvoice->bank_account_number }}</b></td>
			</tr>
			<tr>
				<td style="font-size:10px;color:#111;width:25%;"><b>Swift Code:</b></td>
				<td style="font-size:10px;color:#111;width:75%;"><b>{{ $shippingInvoice->swift_code }}</b></td>
			</tr>
			<tr>
				<td style="font-size:10px;color:#111;width:25%;"><b>Bank Address:</b></td>
				<td style="font-size:10px;color:#111;width:75%;"><b>{{ $shippingInvoice->bank_address }}</b></td>
			</tr>
			<tr>
				<td style="font-size:10px;color:#111;width:25%;"><b>Beneficiary Address:</b></td>
				<td style="font-size:10px;color:#111;width:75%;"><b>{{ $shippingInvoice->beneficiary_address }}</b></td>
			</tr>
		</table>
	@endif

</body>

</html>