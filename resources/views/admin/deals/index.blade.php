@extends('layouts.admin')
@section('title') Deals @endsection
@section('inline-css')
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
				{{ html()->modelForm($search,'get',route('admin.deals.index'))->class('')->id('filter-form')->open() }}
					<div class="row mb-4">
						<div class="col-md-3">
							{{ html()->text('search_text')->class('form-control')->placeholder('Search by Model Number') }}
						</div>
						<div class="col-md-3 ">
							@php $status = Config::get('constants.SEARCH_DEAL_STATUS'); @endphp
							{{ html()->select('deal_status', $status)->class('form-control')->id('deal_status')  }}
						</div>
						<div class="col-md-3 fiter-btn-pd">
							<button type="submit" class="btn btn-sm custom_btn btn-primary filter-btn"><i data-feather="filter"></i></button>
							<a href="{{ route('admin.deals.index') }}" class="btn btn-dark btn-sm reset-btn"><i data-feather="refresh-ccw"></i></a>
						</div>
						<div class="col-md-3 text-right">
							<span class="col-form-label">Per Page: </span>
							@php $showrecord = Config::get('constants.SHOW_RECORD'); @endphp
							{{ html()->select('showrecord', $showrecord)->class('form-control perpage_select')->id('showrecord')  }}
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
								<th>Created</th>
								<th>Updated</th>
								<th class="text-right">Action</th>
							</tr>
						</thead>
						<tbody class="list" id="countries">
							@if(count($deals))
								@php $i = ($deals->currentPage() - 1) * $deals->perPage(); @endphp
								@foreach($deals as $deal)
									<tr>
										<td>{{ ++$i }}</td>
										<td>{{ $deal->model_number }}</td>
										<td>{{ $deal->serial_number }}</td>
										<td>{{ $deal->year }}</td>
										<td>
											@php $deal_status = Config::get('constants.DEAL_STATUS'); @endphp
											{!! $deal_status[$deal->deal_status] !!}
										</td>
										<td>{{date('D, M d, Y', strtotime($deal->created_at))}}</td>
										<td>{{date('D, M d, Y', strtotime($deal->updated_at))}}</td>
										<td class="noselect text-right">
											<div class="action-tools">
											
												<a href="{{ Route('admin.deals.show', $deal->id ) }}" class="btn btn-success btn-sm" data-toggle="tooltip" title="View">
													<i class="fa fa-eye"></i> 
												</a>
												&nbsp;&nbsp;
												<a href="{{ url('/admin/deals/'.$deal->id.'/edit') }}"
													class="btn btn-info btn-sm action-btn edit" data-toggle="tooltip" title=""
													data-original-title="{{trans('admin.EDIT')}}"><i class="far fa-edit"></i>
												</a>
												&nbsp;&nbsp;
												<a href="{{ Route('admin.delete-deal', $deal->id ) }}" onclick="confirmation(event)" class="btn btn-danger btn-sm action-btn delete" data-toggle="tooltip" title="Delete">
													<i class="fa fa-trash"></i> 
												</a>
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
@endsection