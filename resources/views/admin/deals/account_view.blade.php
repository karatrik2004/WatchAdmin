@extends('layouts.admin')
@section('title')
    View Account Deal
@endsection
@section('inline-css')
    <style>
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .highlight {
            background-color: yellow;
        }

        .brown {
            background-color: brown;
            color: white;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
@endsection
@section('content')
    <div class="col-md-12">
        <div class="card">
            <div class="card-header text-white">
                <div class="header-actions">
                    <h5 class="mb-0">View Account Deal Detail</h5>
                    <a href="{{route('admin.deals.show',$deal->id)}}" class="btn btn-dark">Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        @php
                            $salePrice = $deal->sale_price;
                            $purchasePrice = $deal->purchase_price; // Ensure correct property name
                            $gstPercent = 11; // GST percentage
                            $finderPer = 0.25;

                            // Sale Price GST Amount
                            $salePriceGSTAmount = ($salePrice/$gstPercent);
                            $purchasePriceGSTAmount = $purchasePrice/$gstPercent;
                             // GST Calculation
                            $salePriceLessWithGST = $salePrice - $salePriceGSTAmount;
                            $purchasePriceLessWithGST = $purchasePrice - $purchasePriceGSTAmount;

                            $grossProfit = $salePriceLessWithGST - $purchasePriceLessWithGST;
                            $finderFees = $grossProfit*$finderPer;
                            $deliveryFees = $deal->delivery_cost;
                            $estimateProfit = $grossProfit - $finderFees - $deliveryFees;
                            if (($purchasePrice + $estimateProfit) != 0) {
                            $margin_percent = ($estimateProfit / ($purchasePrice + $estimateProfit)) * 100;
                            } else {
                            $margin_percent = 0; // or any default value you prefer
                            }


                        @endphp

                        <div class="row">
                            <table>
                                <tr>
                                    <th colspan="4"
                                        style="padding: 10px; text-align: center; background-color: #00ec8d; color: #000;">
                                        If Offer/Sales Made Within Australia
                                    </th>
                                </tr>
                                <tr>
                                    <th>Party</th>
                                    <th>USD</th>
                                    <th>Current Exchange</th>
                                    <th>AUD</th>
                                </tr>
                                <tr>
                                    <td>Sale Price</td>
                                    <td>-</td>
                                    <td>0</td>
                                    <td class="highlight">${{number_format($salePrice,'2','.',',')}}</td>
                                </tr>
                                <tr>
                                    <td>Less GST (if app)</td>
                                    <td></td>
                                    <td></td>
                                    <td>${{number_format($salePriceGSTAmount,'2','.',',')}}</td>
                                </tr>
                                <tr>
                                    <td>Net Sales Value</td>
                                    <td></td>
                                    <td></td>
                                    <td>${{number_format($salePriceLessWithGST,'2','.',',')}}</td>
                                </tr>
                                <tr>
                                    <td>Purchase</td>
                                    <td></td>
                                    <td></td>
                                    <td class="brown">${{number_format($purchasePrice,'2','.',',')}}</td>
                                </tr>
                                <tr>
                                    <td>Less GST</td>
                                    <td></td>
                                    <td></td>
                                    <td>${{number_format($purchasePriceGSTAmount,'2','.',',')}}</td>
                                </tr>
                                <tr>
                                    <td>Net Cost</td>
                                    <td></td>
                                    <td></td>
                                    <td>${{number_format($purchasePriceLessWithGST,'2','.',',')}}</td>
                                </tr>
                                <tr>
                                    <td>Gross Profit</td>
                                    <td></td>
                                    <td></td>
                                    <td>${{number_format($grossProfit,'2','.',',')}}</td>
                                </tr>
                                <tr>
                                    <td>Finder's Fee</td>
                                    <td></td>
                                    <td></td>
                                    <td>${{number_format($finderFees,'2','.',',')}}</td>
                                </tr>
                                <tr>
                                    <td>Delivery Costs</td>
                                    <td></td>
                                    <td></td>
                                    <td>${{number_format($deliveryFees,'2','.',',')}}</td>
                                </tr>
                                <tr>
                                    <td>Zman Estimated Profit</td>
                                    <td></td>
                                    <td></td>
                                    <td>${{number_format($estimateProfit,'2','.',',')}}</td>
                                </tr>
                                <tr>
                                    <td>Break Even Exchange Rate</td>
                                    <td colspan="3">#VALUE!</td>
                                </tr>
                            </table>
                        </div>
                        @php
                            $steps = isset($deal->checklist_steps) ? json_decode($deal->checklist_steps, true) ?? [] : [];
                        @endphp
                        <div class="row mt-3">
                            <table>
                                <thead>
                                <tr>
                                    <th colspan="3"
                                        style="padding: 10px; text-align: center; background-color: #ffff00; color: #000;">
                                        Conditions for Evaluation of Deals
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td style="padding: 10px; text-align: center;"><b>1</b></td>
                                    <td style="padding: 10px;">
                                        <strong>Minimum Margin of 1% of Purchase price achieved</strong>
                                    </td>
                                    @php
                                        $bgColor = $margin_percent >= 1 ? '#90EE90' : '#FFB6C1'; // lightgreen or lightred
                                    @endphp
                                    <td style="padding: 10px; text-align: center; background-color: {{ $bgColor }}; color: white;">
                                        {{$margin_percent>=1 ? 'Yes' : 'No'}}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; text-align: center;"><b>2</b></td>
                                    <td style="padding: 10px;">
                                        <strong>Confirmed Written offer & Evidence received</strong>
                                    </td>
                                    <td style="padding: 10px; text-align: center;
        background-color: {{ old('step.2', $steps[2] ?? false) ? '#90EE90' : '#FFB6C1' }};
        color: white;">
                                        {{ old('step.2', $steps[2] ?? false) ? 'Yes' : 'No' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 10px; text-align: center;"><b>3</b></td>
                                    <td style="padding: 10px;">
                                        <strong>Expected Turnaround time must be specified in the email</strong>
                                    </td>
                                    <td style="padding: 10px; text-align: center;
        background-color: {{ old('step.3', $steps[3] ?? false) ? '#90EE90' : '#FFD580' }};
        color: white;">
                                        {{ old('step.3', $steps[3] ?? false) ? 'Yes' : 'No' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 10px; text-align: center;"><b>4</b></td>
                                    <td style="padding: 10px;">
                                        <strong>Deposit required for Deals above $300K or more</strong>
                                    </td>
                                    <td style="padding: 10px; text-align: center;
        background-color: {{ old('step.4', $steps[4] ?? false) ? '#90EE90' : '#FFD580' }};
        color: white;">
                                        {{ old('step.4', $steps[4] ?? false) ? 'Yes' : 'No' }}
                                    </td>
                                </tr>

                                {{-- <tr>
                                     <td style="padding: 10px; text-align: center;"><b>2</b></td>
                                     <td style="padding: 10px;">
                                         <strong>Confirmed Written offer & Evidence received</strong>
                                     </td>
                                     <td style="padding: 10px; text-align: center; background-color: #FFB6C1; color: white;">
                                         No
                                     </td>
                                 </tr>
                                 <tr>
                                     <td style="padding: 10px; text-align: center;"><b>3</b></td>
                                     <td style="padding: 10px;">
                                         <strong>Expected Turnaround time must be specified in the email</strong>
                                     </td>
                                     <td style="padding: 10px; text-align: center; background-color: #FFD580; color: white;">
                                         NA
                                     </td>
                                 </tr>
                                 <tr>
                                     <td style="padding: 10px; text-align: center;"><b>4</b></td>
                                     <td style="padding: 10px;">
                                         <strong>Deposit required for the Deals above $300K or more</strong>
                                     </td>
                                     <td style="padding: 10px; text-align: center; background-color: #FFD580; color: white;">
                                         NA
                                     </td>
                                 </tr>--}}
                                </tbody>
                            </table>
                        </div>

                        @if($margin_percent < 1 && empty($deal->deal_approve_status))
                            <div class="row mt-3">
                                <a href="javascript:void(0);"
                                   class="deal-approve-btn btn btn-lg text-black"
                                   style="background-color: #b2fab4; border-color: #b2fab4;"
                                   data-deal-id="{{ $deal->id }}"
                                   aria-label="{{ __('Approve Deal') }} {{ $deal->id }}"
                                   data-toggle="tooltip"
                                   title="{{ __('Click here to Approve the Deal') }}"
                                   role="button">
                                    <i class="bi bi-check-circle-fill me-2"></i> {{ __('Click here to Approve the Deal') }}
                                </a>
                            </div>
                        @endif


                    @if(!empty($deal->deal_approve_status))
                            <div class="mt-3">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th colspan="2" class="text-center">Deal Approval Notes</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="fw-bold">Approval Notes</td>
                                        <td>
                                            <p class="mb-0">{{ $deal->deal_approve_notes ?: 'No approval notes available' }}</p>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif




                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="dynamicModalContainer"></div>
@endsection


@section('inline-js')
    <script type="text/javascript" src="{{ asset('backend/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        $(document).on('click', '.deal-approve-btn', function () {
            const historyId = $(this).data('deal-id');

            // Generate the URL using the Laravel route helper
            const url = `{{ route('admin.deals.approve.modal.show', ':id') }}`.replace(':id', historyId);

            // Make an AJAX request to fetch the modal HTML
            $.ajax({
                url: url,
                method: 'GET',
                success: function (response) {
                    // Inject the modal HTML into the placeholder
                    $('#dynamicModalContainer').html(response);

                    // Show the modal
                    $('#dealApproveModel').modal({
                        backdrop: 'static', // Enable backdrop
                        keyboard: true, // Close modal on Escape key
                    }).modal('show');
                },
                error: function () {
                    alert('Failed to load modal. Please try again.');
                }
            });
        });
        // Close the modal when the cancel or close button is clicked
        $(document).on('click', '.cancel-btn, .close-btn', function () {
            $('#dealApproveModel').modal('hide');
        });
    </script>
@endsection
