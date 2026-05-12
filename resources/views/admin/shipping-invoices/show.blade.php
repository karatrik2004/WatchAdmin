@extends('layouts.admin')
@section('title') Shipping Invoice @endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Shipping Invoice</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.shipping-invoices.download', $invoice->id) }}" class="btn btn-primary btn-sm">
                        <i data-feather="download"></i> Download PDF
                    </a>
                    <a href="{{ route('admin.shipping-invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i data-feather="arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Invoice header --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <p class="mb-1 text-muted small">Invoice Date</p>
                        <strong>{{ $invoice->invoice_date->format('d M Y') }}</strong>
                    </div>
                    @if($invoice->notes)
                    <div class="col-md-12 mt-2">
                        <p class="mb-1 text-muted small">Notes</p>
                        <span>{{ $invoice->notes }}</span>
                    </div>
                    @endif
                </div>

                {{-- Items table --}}
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Watch Description</th>
                                <th style="width:120px">GST</th>
                                <th style="width:160px" class="text-end">Sale price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td style="white-space: pre-line; font-size: 13px;">{{ $item->description }}</td>
                                <td>{{ $item->gst_type ? str_replace('_', ' ', $item->gst_type) : 'None' }}</td>
                                <td class="text-end">{{ strtoupper($item->currency ?? 'USD') }} ${{ number_format($item->unit_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
