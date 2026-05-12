@extends('layouts.admin')
@section('title') Deals @endsection
@section('inline-css')
    <!-- custom style -->
    <link rel="stylesheet" href="{{ asset('backend/css/flatpickr.min.css') }}" />
    <style>
        .review-row-under-review {
            background-color: #fff8e1;
        }

        .review-row-reviewed {
            background-color: #e8f5e9;
        }

        .review-badge-under-review {
            background-color: #ff9800;
            color: #fff;
        }

        .review-badge-reviewed {
            background-color: #28a745;
            color: #fff;
        }

        .review-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
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
            border-radius: 8px;
            font-weight: 700;
            padding: 4px 10px;
        }

        .review-cta-done {
            background-color: #198754;
            border-color: #198754;
            color: #fff;
        }
    </style>
@endsection
@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<div class="row">
					<div class="col-md-10">
						<h4>All Deals</h4>
					</div>
					<div class="col-md-2">
						<a href="{{route('admin.deal-download-excel', request()->all())}}" class="btn btn-dark btn-sm custom_btn float-right"><i class="fa fa-download" aria-hidden="true"></i>
							Download CSV</a>
					</div>
				</div>
			</div>
            <div class="card-body table-border-style mb-2">
                {{ html()->modelForm($search, 'get', route('admin.deals.index'))
    ->class('bg-light p-4 rounded shadow-sm') // Adds padding, rounded corners, and shadow
    ->id('filter-form')
    ->open()
}}

                <div class="card p-3 shadow-sm">
                    <div class="row gx-3 gy-2 align-items-end">

                        <!-- Search Box -->
                        <div class="col-md-3">
                            <div class="form-floating">
                                {{ html()->text('search_text')
                                    ->class('form-control')
                                    ->placeholder('Search by Model Number')
                                }}
                                <label for="search_text">Search by Model Number</label>
                            </div>
                        </div>

                        <!-- Deal Status -->
                        <div class="col-md-3">
                            <div class="form-floating">
                                @php $status = ['' => 'Select Deal Status'] + Config::get('constants.DEAL_STATUS'); @endphp
                                {{ html()->select('deal_status', $status)
                                    ->class('form-select')
                                    ->id('deal_status')
                                }}
                                <label for="deal_status">Deal Status</label>
                            </div>
                        </div>

                        <!-- Brand Selection -->
                        <div class="col-md-3">
                            <div class="form-floating">
                                <input type="text"
                                       name="brand_name"
                                       id="brand_name"
                                       class="form-control brand-autocomplete"
                                       placeholder="Select Brand"
                                       autocomplete="off"
                                       value="{{ old('brand_name') }}">
                                <label for="brand_name">Brand</label>

                                <input type="hidden"
                                       name="brand_id"
                                       id="brand_id"
                                       value="{{ old('brand_id', request('brand_id')) }}">
                            </div>
                        </div>


                        <!-- Start Date -->
                        <div class="col-md-3">
                            <div class="form-floating">
                                {{ html()->text('start_date')
                                    ->class('form-control flatpickr')
                                    ->id('start_date')
                                    ->placeholder('Select Start Date')
                                }}
                                <label for="start_date">Start Date</label>
                            </div>
                        </div>

                        <!-- End Date -->
                        <div class="col-md-3">
                            <div class="form-floating">
                                {{ html()->text('end_date')
                                    ->class('form-control flatpickr')
                                    ->id('end_date')
                                    ->placeholder('Select End Date')
                                }}
                                <label for="end_date">End Date</label>
                            </div>
                        </div>

                        <!-- Per Page Selection -->
                        <div class="col-md-3 text-md-end">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold">Per Page:</span>
                                @php $showrecord = Config::get('constants.SHOW_RECORD'); @endphp
                                {{ html()->select('showrecord', $showrecord)
                                    ->class('form-select w-auto')
                                    ->id('showrecord')
                                }}
                            </div>
                        </div>

                        <!-- Filter & Reset Buttons -->
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i data-feather="filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.deals.index') }}" class="btn btn-dark btn-sm">
                                <i data-feather="refresh-ccw"></i> Reset
                            </a>
                        </div>

                    </div>
                </div>

                {{ html()->form()->close() }}


                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Model No.</th>
                            <th>Serial No.</th>
                            <th>Year</th>
                            <th>Deal Status</th>
                            <th>Review Status</th>
                            <th>Brand</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th class="text-right">Action</th>
                        </tr>
                        </thead>
                        <tbody class="list" id="countries">
                        @if(count($deals))
                            @php $i = ($deals->currentPage() - 1) * $deals->perPage(); @endphp
                            @foreach($deals as $deal)
                                @php
                                    $reviewStatusKey = $deal->review_status ?? 'under_review';
                                    $reviewStatuses = config('constants.REVIEW_STATUS', []);
                                    $reviewLabel = $reviewStatuses[$reviewStatusKey] ?? 'Under Review';
                                    $reviewBadgeClass = $reviewStatusKey === 'reviewed' ? 'review-status-done' : 'review-status-under';
                                    $reviewRowClass = $reviewStatusKey === 'reviewed' ? 'review-row-reviewed' : 'review-row-under-review';
                                @endphp
                                <tr class="{{ $reviewRowClass }}">
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $deal->model_number }}</td>
                                    <td>{{ $deal->serial_number }}</td>
                                    <td>{{ $deal->year }}</td>
                                    <td>
                                        @php $deal_status = Config::get('constants.DEAL_STATUS'); @endphp
                                        {!! $deal_status[$deal->deal_status] !!}
                                    </td>
                                    <td>
                                        <span class="review-status-pill {{ $reviewBadgeClass }}">
                                            <i class="fa {{ $reviewStatusKey === 'reviewed' ? 'fa-check-circle' : 'fa-clock-o' }}"></i>
                                            {{ $reviewLabel }}
                                        </span>
                                    </td>
                                    <td>{{$deal->watchBrandDetail->name ?? ""}}</td>
                                    <td>{{date('D, M d, Y', strtotime($deal->created_at))}}</td>
                                    <td>{{date('D, M d, Y', strtotime($deal->updated_at))}}</td>
                                    <td class="noselect text-center align-middle">
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <a href="{{ Route('admin.deals.show', $deal->id ) }}"
                                               class="btn btn-success btn-sm" data-toggle="tooltip" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            @if(($deal->review_status ?? 'under_review') !== 'reviewed')
                                                <form method="POST" action="{{ route('admin.deals.mark-reviewed', $deal->id) }}" class="d-inline" onsubmit="return confirm('Mark this deal as reviewed and notify funding team?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm review-cta d-inline-flex align-items-center gap-1" data-toggle="tooltip" title="Click to mark reviewed">
                                                        <i class="fa fa-check-circle"></i> Mark Reviewed
                                                    </button>
                                                </form>
                                            @else
                                                <span class="btn btn-sm review-cta review-cta-done disabled d-inline-flex align-items-center gap-1" title="Already reviewed">
                                                    <i class="fa fa-check-circle"></i> Reviewed
                                                </span>
                                            @endif
                                            <!-- Sale Invoice PDF buttons (USD / AUD) -->
                                            <a href="{{ route('admin.invoice.download', ['deal_id' => $deal->id, 'currency' => 'usd']) }}" target="_blank"
                                               class="btn btn-secondary btn-sm" data-toggle="tooltip" title="USD Sale Invoice PDF">
                                                <i class="fa fa-file-pdf"></i> USD
                                            </a>
                                            <a href="{{ route('admin.invoice.download', ['deal_id' => $deal->id, 'currency' => 'aud']) }}" target="_blank"
                                               class="btn btn-secondary btn-sm" data-toggle="tooltip" title="AUD Sale Invoice PDF">
                                                <i class="fa fa-file-pdf"></i> AUD
                                            </a>
                                            @if(auth('admin')->user()->hasRole('super admin'))
                                                <a href="{{ url('/admin/deals/'.$deal->id.'/edit') }}"
                                                   class="btn btn-info btn-sm action-btn edit" data-toggle="tooltip"
                                                   title=""
                                                   data-original-title="{{trans('admin.EDIT')}}"><i
                                                        class="far fa-edit"></i>
                                                </a>
                                                <a href="{{ Route('admin.delete-deal', $deal->id ) }}"
                                                   onclick="confirmation(event)"
                                                   class="btn btn-danger btn-sm action-btn delete" data-toggle="tooltip"
                                                   title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="noselect text-center" colspan="6">{{trans('admin.NO_ITEM_FOUND')}}</th>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    @if (count($deals))
                        {!! $deals->withQueryString()->links('pagination::bootstrap-5') !!}
                    @endif
                </div>
            </div>
		</div>
	</div>
</div>
@endsection

@section('inline-js')
    <script type="text/javascript" src="{{ asset('backend/js/flatpickr.js') }}"></script>
    <script>
        initBrandAutocomplete($("#brand_name"));
        function initBrandAutocomplete(inputField) {
            $(inputField).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('admin.deal.brand.autocomplete') }}",
                        dataType: "json",
                        data: {
                            term: request.term
                        },
                        beforeSend: function() {
                            $('#loader').show();
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    label: item.label,
                                    value: item.value
                                };
                            }));
                        },
                        complete: function() {
                            $('#loader').hide();
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    $("#brand_name").val(ui.item.label);
                    $("#brand_id").val(ui.item.value);
                    return false;
                },
                response: function(event, ui) {
                    if (ui.content.length === 0) {
                        console.log('No brand suggestions found');
                    }
                },
                change: function(event, ui) {
                    const term = $(this).val().toLowerCase();
                    let found = false;

                    $(this).autocomplete("widget").find("div").each(function() {
                        if ($(this).text().toLowerCase() === term) {
                            found = true;
                            return false;
                        }
                    });

                    if (!found) {
                        $("#brand_name").val('');
                        $("#brand_id").val('');
                    }
                },
                create: function() {
                    $(this).data("ui-autocomplete")._renderItem = function(ul, item) {
                        return $("<li>")
                            .append("<div>" + item.label + "</div>")
                            .appendTo(ul);
                    };
                }
            });
        }
        let brandId = $('#brand_id').val();

        if (brandId) {
            $.ajax({
                url: "{{ route('admin.deal.brand.name') }}",
                type: "GET",
                data: { id: brandId },
                success: function (data) {
                    $('#brand_name').val(data.name);
                },
                error: function () {
                    console.log('Failed to load brand name');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const startDatePicker = flatpickr("#start_date", {
                dateFormat: "Y-m-d", // You can change the format as needed
                maxDate: "today", // Disable future dates
                onChange: function(selectedDates, dateStr, instance) {
                    // Set the min date for the end date to the selected start date
                    endDatePicker.set('minDate', selectedDates[0]);
                }
            });

            const endDatePicker = flatpickr("#end_date", {
                dateFormat: "Y-m-d", // You can change the format as needed
                maxDate: "today", // Disable future dates
                minDate: "today", // Set min date for end date initially to today
                onChange: function(selectedDates, dateStr, instance) {
                    const startDate = startDatePicker.selectedDates[0];
                    if (startDate && selectedDates[0] < startDate) {
                        // Disable invalid end date selections
                        alert('End date must be greater than or equal to start date');
                        // Prevent selecting an invalid end date
                        endDatePicker.clear();
                        return;
                    }
                }
            });
        });
    </script>

@endsection
