@extends('layouts.admin')
@section('title')
    View Deal
@endsection
@section('inline-css')
@endsection
@section('content')
    <!-- Page Heading -->

    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>View Deal Detail</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-10">
                        <div class="row">
                            <div class="col-sm-3">
                                <label class="form-label fw-bold"> Model Number:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>{{ $deal->model_number }}</p>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Serial Number:</label>
                            </div>
                            <div class="col-sm-3">
                                <p> {{$deal->serial_number}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Material of the watch:</label>
                            </div>
                            <div class="col-sm-3">
                                <p> {{$deal->material_watch}}</p>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Condition:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>  {!! $deal->condition !!}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Year:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>  {!! $deal->year !!}</p>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Full set or not:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>   {!! $deal->full_set !!}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Purchase Price:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>   {!! 'A$ '.number_format($deal->purchase_price, 2, '.', ',') !!}</p>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Sale Price:</label>
                            </div>
                            <div class="col-sm-3">
                                <p> {!! 'A$ '. number_format($deal->sale_price, 2, '.', ',') !!}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Country:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>  {!! (!empty($deal['country_id']) && isset($countries[$deal['country_id']]))?$countries[$deal['country_id']]:'' !!} </p>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">State:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>   {!! (!empty($deal['state_id']) && isset($states[$deal['state_id']]))?$states[$deal['state_id']]:'' !!}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">City:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>  {!! (!empty($deal['city_id']) && isset($cities[$deal['city_id']]))?$cities[$deal['city_id']]:'' !!}</p>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Zipcode:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>  {!! $deal->zipcode !!}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Deal Status:</label>
                            </div>
                            <div class="col-sm-3">
                                @php $deal_status = Config::get('constants.DEAL_STATUS'); @endphp
                                <p>   {!! $deal_status[$deal->deal_status] !!}</p>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Status (Active/Deactive):</label>
                            </div>
                            <div class="col-sm-3">
                                @php $status = Config::get('constants.STATUS'); @endphp
                                <p>   {!! $status[$deal->status] !!}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Created:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>{{date('D, M d, Y h:i:s a',strtotime($deal->created_at))}}</p>
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label fw-bold">Updated:</label>
                            </div>
                            <div class="col-sm-3">
                                <p>{{date('D, M d, Y h:i:s a',strtotime($deal->updated_at))}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm-4">
                            <a href="{{route('admin.deals.edit',$deal->id)}}" class="btn btn-success btn-user btn-md">Edit</a>
                            <a href="{{route('admin.deals.index')}}" class="btn btn-dark btn-md">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
