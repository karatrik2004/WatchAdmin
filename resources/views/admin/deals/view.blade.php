@extends('layouts.admin')
@section('title')
    View Deal
@endsection
@section('inline-css')
    <style>
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-label {
            font-weight: bold;
        }

        .review-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-top: 14px;
        }

        .review-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
        }

        .review-status-under {
            background-color: #fff3cd;
            border: 1px solid #ffda88;
            color: #8a6d1f;
        }

        .review-status-done {
            background-color: #d1f2dd;
            border: 1px solid #8ed3a8;
            color: #146c43;
        }

        .review-cta {
            border-radius: 10px;
            font-weight: 700;
            padding: 8px 14px;
        }

        .review-cta-pending {
            background-color: #198754;
            border-color: #198754;
            color: #fff;
        }

        .review-cta-pending:hover {
            background-color: #157347;
            border-color: #146c43;
            color: #fff;
        }

        .review-cta-done {
            background-color: #198754;
            border-color: #198754;
            color: #fff;
            opacity: 0.95;
        }
    </style>
    <!-- Fancybox CSS -->
    <link rel="stylesheet" href="{{ asset('backend/css/fancybox.min.css') }}" />
@endsection
@section('content')
    <!-- Page Heading -->
    <div class="col-md-12">
        <div class="card">
            {{--<div class="card-header">
                <div class="header-actions">
                    <h5 class="mb-0">View Deal Detail</h5>
                    <a href="{{ route('admin.deal.account.details', $deal->id) }}" class="btn btn-success btn-user btn-md">
                        <i class="fas fa-eye"></i> View Account Deal
                    </a>
                </div>
            </div>--}}
            <div class="card-header py-4 px-4 bg-light border-bottom position-relative rounded-top">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 48px; height: 48px; font-size: 2rem;">
                            <i class="fa fa-info-circle"></i>
                        </span>
                        <div>
                            <h3 class="mb-0 text-primary fw-bold">Deal Details Overview</h3>
                            <div class="text-muted small">Comprehensive summary and actions for this deal</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap mt-3 mt-md-0">
                        @if(auth('admin')->user()->hasRole('super admin'))
                            <a href="{{route('admin.deals.edit', $deal->id)}}"
                                class="btn btn-success btn-user btn-md d-inline-flex align-items-center gap-2"><i
                                    class="fa fa-edit"></i> Edit</a>
                        @endif
                        <a href="{{ route('admin.deals.index') }}"
                            class="btn btn-dark btn-md d-inline-flex align-items-center gap-2"><i
                                class="fa fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                <div class="review-toolbar">
                    @php
                        $reviewStatusKey = $deal->review_status ?? 'under_review';
                        $reviewStatuses = config('constants.REVIEW_STATUS', []);
                        $reviewLabel = $reviewStatuses[$reviewStatusKey] ?? 'Under Review';
                        $reviewBadgeClass = $reviewStatusKey === 'reviewed' ? 'review-status-done' : 'review-status-under';
                    @endphp
                    <span class="review-status-pill {{ $reviewBadgeClass }}">
                        <i class="fa {{ $reviewStatusKey === 'reviewed' ? 'fa-check-circle' : 'fa-clock-o' }}"></i>
                        {{ $reviewLabel }}
                    </span>
                    @if($reviewStatusKey !== 'reviewed')
                        <form method="POST" action="{{ route('admin.deals.mark-reviewed', $deal->id) }}" class="d-inline"
                            onsubmit="return confirm('Mark this deal as reviewed and notify funding team?');">
                            @csrf
                            <button type="submit"
                                class="btn review-cta review-cta-pending btn-md d-inline-flex align-items-center gap-2">
                                <i class="fa fa-check-circle"></i> <span>Mark as Reviewed</span>
                            </button>
                        </form>
                    @else
                        <span class="btn review-cta review-cta-done btn-md disabled d-inline-flex align-items-center gap-2">
                            <i class="fa fa-check-circle"></i> <span>Reviewed</span>
                        </span>
                    @endif
                    <a href="{{ route('admin.deal.account.details', $deal->id) }}"
                        class="btn btn-outline-success d-inline-flex align-items-center gap-2"><i class="fa fa-eye"></i>
                        <span>Account Summary</span></a>
                    <a href="{{ route('admin.deals.myob-invoice', $deal->id) }}"
                        class="btn btn-warning text-white d-inline-flex align-items-center gap-2"
                        onclick="return confirm('Create XERO invoice for this deal?');"><i class="fa fa-plus-circle"></i>
                        <span>Add to XERO</span></a>
                    {{-- <button class="btn btn-warning text-white d-inline-flex align-items-center gap-2"
                        id="generateShippingBtn" data-dealid="{{$deal->id}}">Generate Shipping</button> --}}
                    <a href="{{ route('admin.invoice.download', ['deal_id' => $deal->id, 'currency' => 'usd']) }}"
                        class="btn btn-success btn-md custom_btn d-inline-flex align-items-center text-white">
                        <i class="fa fa-download mr-2" aria-hidden="true"></i>
                        <span>USD Sale Invoice PDF</span>
                    </a>
                    <a href="{{ route('admin.invoice.download', ['deal_id' => $deal->id, 'currency' => 'aud']) }}"
                        class="btn btn-warning btn-md custom_btn d-inline-flex align-items-center">
                        <i class="fa fa-download mr-2" aria-hidden="true"></i>
                        <span>AUD Sale Invoice PDF</span>
                    </a>
                    @if($deal->dealBuyerDetail)
                        <a href="{{ route('admin.shipping.invoice.template.download', ['deal_id' => $deal->id]) }}"
                            class="btn btn-info btn-md custom_btn d-inline-flex align-items-center text-white">
                            <i class="fa fa-download mr-2" aria-hidden="true"></i>
                            <span>Shipping Invoice PDF</span>
                        </a>
                        <form method="post" action="{{ route('admin.deals.email-invoice-customer', $deal) }}" class="d-inline"
                            onsubmit="return confirm('Send invoice email with PDF to the sales notification list?');">
                            @csrf
                            <button type="submit"
                                class="btn btn-primary btn-md custom_btn d-inline-flex align-items-center text-white">
                                <i class="fa fa-envelope mr-2" aria-hidden="true"></i>
                                <span>Email invoice to customer</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>


            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 text-primary fw-bold">Deal Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Model Number:</span>
                                            </div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->model_number }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Serial Number:</span>
                                            </div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->serial_number }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Reference
                                                    Number:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->material_watch }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Dial:</span></div>
                                            <div class="flex-grow-1"><span class="value-box">{{ $deal->dial }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Condition:</span></div>
                                            <div class="flex-grow-1"><span class="value-box">{!! $deal->condition !!}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Year:</span></div>
                                            <div class="flex-grow-1"><span class="value-box">{!! $deal->year !!}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Full Set:</span></div>
                                            <div class="flex-grow-1">
                                                @php
                                                    $fullStatus = config('constants.FULL_STATUS');
                                                    $statusText = $fullStatus[$deal->full_set] ?? 'Unknown';
                                                    $badgeClass = match ($deal->full_set) {
                                                        '1' => 'success',
                                                        '0' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}">{{ $statusText }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">GST Code:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->gst_code ?? '-' }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Deal Supplier
                                                    Type:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->deal_supplier_status ?? '-' }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Purchase Price:</span>
                                            </div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ strtoupper($deal->purchase_currency ?? 'AUD') }}
                                                    {{ number_format($deal->purchase_price, 2, '.', ',') }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Estimate Sale
                                                    Price:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ strtoupper($deal->purchase_currency ?? 'AUD') }}
                                                    {{ number_format($deal->sale_price, 2, '.', ',') }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Deal Type:</span></div>
                                            <div class="flex-grow-1">
                                                <span class="badge
                                                            @if($deal->customer_type == 'individual') bg-success
                                                            @elseif($deal->customer_type == 'company') bg-primary
                                                            @else bg-secondary
                                                            @endif">
                                                    {{ ucfirst($deal->customer_type) ?? "N/A" }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Brand Name:</span>
                                            </div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->watchBrandDetail->name ?? "" }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Deal Status:</span>
                                            </div>
                                            <div class="flex-grow-1">
                                                @php
                                                    $deal_status = Config::get('constants.DEAL_STATUS');
                                                    if (!is_null($deal->deal_status) && isset($deal_status[$deal->deal_status])) {
                                                        $statusText = $deal_status[$deal->deal_status];
                                                        // Assign badge class based on status value
                                                        $badgeClass = match ($deal->deal_status) {
                                                            '1' => 'warning',
                                                            '2' => 'secondary',
                                                            '3' => 'success',
                                                            '4' => 'danger',
                                                            default => 'secondary',
                                                        };
                                                    } else {
                                                        $statusText = 'Unknown';
                                                        $badgeClass = 'secondary';
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}">{!! $statusText !!}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Delivery Cost:</span>
                                            </div>
                                            <div class="flex-grow-1"><span class="value-box">A$
                                                    {{ number_format($deal->delivery_cost, 2, '.', ',') }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Created:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ date('D, M d, Y h:i:s a', strtotime($deal->created_at)) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Updated:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ date('D, M d, Y h:i:s a', strtotime($deal->updated_at)) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Status
                                                    (Active/Deactive):</span></div>
                                            <div class="flex-grow-1">
                                                @php $status = Config::get('constants.STATUS'); @endphp
                                                <span class="value-box">{!! $status[$deal->status] !!}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($deal->customer_type == 'individual')
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 text-primary fw-bold">Individual Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">First Name:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->first_name ?? "N/A" }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Last Name:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->last_name ?? "N/A" }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Email:</span></div>
                                            <div class="flex-grow-1"><span class="value-box">{{ $deal->email ?? "N/A" }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Contact Number:</span>
                                            </div>
                                            <div class="flex-grow-1"><span class="value-box">{{ $deal->mobile ?? "N/A" }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Country:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{!! $deal['country'] ?? "N/A" !!}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">State:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{!! $deal['state'] ?? "N/A" !!}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">City:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{!!  $deal['city'] ?? "N/A" !!}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Address:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->address ?? "N/A" }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Zipcode:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->zipcode ?? "N/A" }}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif


                    @if($deal->customer_type == 'company')
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 text-primary fw-bold">Company Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Company Name:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealCustomerTypeDetail->company_name ?? "N/A" }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Company Email:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealCustomerTypeDetail->company_email ?? "N/A" }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Company Country:</span>
                                            </div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealCustomerTypeDetail->company_country ?? "N/A" }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">State:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealCustomerTypeDetail->company_state ?? "N/A" }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">City:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealCustomerTypeDetail->company_city ?? "N/A" }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Address:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealCustomerTypeDetail->company_address ?? "N/A" }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Zip Code:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealCustomerTypeDetail->company_zip_code ?? "N/A" }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">ABN Number:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealCustomerTypeDetail->abn_number ?? "N/A" }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Invoice File:</span></div>
                                            <div class="flex-grow-1">
                                                @if($deal->dealCustomerTypeDetail->invoice_file)
                                                    <a href="{{ asset('storage/' . $deal->dealCustomerTypeDetail->invoice_file) }}"
                                                        target="_blank" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i> View Invoice
                                                    </a>
                                                @else
                                                    <span class="value-box">N/A</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif



                    @if ($deal->deal_status == 3 && $deal->dealBuyerDetail)
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 text-primary fw-bold">Buyer Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Name of Buyer:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealBuyerDetail->buyer_name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Buyer email:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealBuyerDetail->buyer_email ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Country:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealBuyerDetail->buyer_country ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">State:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealBuyerDetail->buyer_state ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">City:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealBuyerDetail->buyer_city ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Address:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealBuyerDetail->buyer_address ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Zipcode:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealBuyerDetail->buyer_zipcode ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Sold Watch Sale
                                                    Price:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ strtoupper(optional($deal->dealBuyerDetail)->buyer_currency ?? 'AUD') }}
                                                    {{ $deal->dealBuyerDetail->buyer_sale_price ?? 'N/A' }}</span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Invoice Number:</span>
                                            </div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealBuyerDetail->invoice_number ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Invoice Date:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box">{{ $deal->dealBuyerDetail->invoice_date ? date('D, M d, Y', strtotime($deal->dealBuyerDetail->invoice_date)) : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">GST Inclusive:</span></div>
                                            <div class="flex-grow-1"><span class="value-box">
                                                    @php $gstTypes = config('constants.GST_TYPE'); @endphp
                                                    {{ $gstTypes[$deal->dealBuyerDetail->gst_type ?? ''] ?? 'N/A' }}
                                                </span></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 w-50"><span class="form-label">Profit/Loss:</span></div>
                                            <div class="flex-grow-1"><span
                                                    class="value-box {{ $deal->is_loss ? 'text-danger' : 'text-success' }}">{{ $deal->is_loss ? 'Loss' : 'Profit' }}:
                                                    {{ number_format(abs($deal->profit_amount ?? 0), 2) }}</span></div>
                                        </div>
                                    </div>
                                    @if($deal->is_loss && optional($deal->dealBuyerDetail)->buyer_loss_remark)
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="flex-shrink-0" style="width:25%;"><span class="form-label">Loss
                                                        Remark:</span></div>
                                                <div class="flex-grow-1"><span
                                                        class="value-box text-danger">{{ $deal->dealBuyerDetail->buyer_loss_remark }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif




                    @if(isset($deal) && $deal->images->isNotEmpty())
                        <div class="card mb-4 shadow-sm mt-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 text-primary fw-bold">Uploaded Images</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($deal->images as $image)
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                            <div class="gallery-img-box position-relative mb-3">
                                                <a href="{{ asset('storage/deals/' . $deal->id . '/' . $image->document_name) }}"
                                                    data-fancybox="gallery" data-caption="Image {{ $loop->iteration }}">
                                                    <img src="{{ asset('storage/deals/' . $deal->id . '/' . $image->document_name) }}"
                                                        alt="Uploaded Image" class="img-thumbnail shadow-sm"
                                                        style="width: 100%; height: 140px; object-fit: cover; border-radius: 8px;">
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" id="deletedImages" name="deleted_images" value="">
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Removed footer Edit/Back buttons for consistency -->

            </div>
        </div>
    </div>
    </div>
    </div>
@endsection

@section('inline-js')
    <script type="text/javascript" src="{{ asset('backend/js/jquery.fancybox.min.js') }}"></script>
    <script>
        // Initialize Fancybox
        $(document).ready(function () {
            $('#generateShippingBtn').on('click', function () {
                let dealId = $(this).data('dealid');

                $.ajax({
                    url: '/admin/generate-shipping/' + dealId,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            alert('Shipping generated successfully!');
                            console.log(response);
                        } else {
                            alert('Error: ' + response.error);
                        }
                    },
                    error: function (xhr) {
                        alert('AJAX error: ' + xhr.responseText);
                    }
                });
            });
            $('[data-fancybox="gallery"]').fancybox({
                // You can customize Fancybox settings here
                loop: true,  // Allows looping through images
                buttons: [  // Customize the buttons on the modal
                    'slideShow',
                    'fullScreen',
                    'thumbs',
                    'close'
                ]
            });
        });
    </script>
@endsection