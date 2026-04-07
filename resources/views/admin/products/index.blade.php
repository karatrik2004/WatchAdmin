@extends('layouts.admin')
@section('title') Products @endsection
@section('inline-css')
@endsection
@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<div class="row">
					<div class="col-md-10">
						<h4>All Products</h4>
					</div>
					<div class="col-md-2">
						<a href="{{ route('admin.products.create') }}" class="btn btn-success btn-sm custom_btn float-right">
							<i class="fa fa-plus" aria-hidden="true"></i> Add Product</a>
					</div>
				</div>
			</div>
			<div class="card-body table-border-style mb-2">
				{{ html()->modelForm($search,'get',route('admin.products.index'))->class('')->id('filter-form')->open() }}
					<div class="row mb-4">
						<div class="col-md-3">
							{{ html()->text('search_text')->class('form-control')->placeholder('Search by Name, Brand or Model Number') }}
						</div>
						<div class="col-md-3">
							@php $status = Config::get('constants.SEARCH_STATUS'); @endphp
							{{ html()->select('status', $status)->class('form-control')->id('status') }}
						</div>
						<div class="col-md-3 fiter-btn-pd">
							<button type="submit" class="btn btn-sm custom_btn btn-primary filter-btn"><i data-feather="filter"></i></button>
							<a href="{{ route('admin.products.index') }}" class="btn btn-dark btn-sm reset-btn"><i data-feather="refresh-ccw"></i></a>
						</div>
						<div class="col-md-3 text-right">
							<span class="col-form-label">Per Page: </span>
							@php $showrecord = Config::get('constants.SHOW_RECORD'); @endphp
							{{ html()->select('showrecord', $showrecord)->class('form-control perpage_select')->id('showrecord') }}
						</div>
					</div>
				{{ html()->form()->close() }}

				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th>Name</th>
								<th>Brand</th>
								<th>Model Number</th>
								<th>Price</th>
								<th>Status</th>
								<th>Created</th>
								<th class="text-right">Action</th>
							</tr>
						</thead>
						<tbody class="list" id="products">
							@if(count($products))
								@php $i = ($products->currentPage() - 1) * $products->perPage(); @endphp
								@foreach($products as $product)
									<tr>
										<td>{{ ++$i }}</td>
										<td>{{ $product->name }}</td>
										<td>{{ $product->brand }}</td>
										<td>{{ $product->model_number }}</td>
										<td>A$ {{ number_format($product->price, 2) }}</td>
										<td>
											@php $status = Config::get('constants.STATUS'); @endphp
											{!! $status[$product->status] !!}
										</td>
										<td>{{ date('D, M d, Y', strtotime($product->created_at)) }}</td>
										<td class="noselect text-right">
											<div class="action-tools">
												<a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-success btn-sm" data-toggle="tooltip" title="View">
													<i class="fa fa-eye"></i>
												</a>
												&nbsp;&nbsp;
												<a href="{{ url('/admin/products/'.$product->id.'/edit') }}"
													class="btn btn-info btn-sm action-btn edit" data-toggle="tooltip" title=""
													data-original-title="{{ trans('admin.EDIT') }}"><i class="far fa-edit"></i>
												</a>
												&nbsp;&nbsp;
												<a href="{{ route('admin.delete-product', $product->id) }}" onclick="confirmation(event)" class="btn btn-danger btn-sm action-btn delete" data-toggle="tooltip" title="Delete">
													<i class="fa fa-trash"></i>
												</a>
											</div>
										</td>
									</tr>
								@endforeach
							@else
							<tr>
								<td class="noselect text-center" colspan="8">{{ trans('admin.NO_ITEM_FOUND') }}</th>
							</tr>
							@endif
						</tbody>
					</table>
					@if (count($products))
						{!! $products->withQueryString()->links('pagination::bootstrap-5') !!}
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('inline-js')
@endsection
