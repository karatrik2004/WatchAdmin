@extends('layouts.admin')
@section('title') Notification Emails @endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Notification Emails</h4>
                <a href="{{ route('admin.notification-emails.create') }}" class="btn btn-primary btn-sm">Add Email</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Email</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($emails as $e)
                            <tr>
                                <td>{{ $e->id }}</td>
                                <td>{{ $e->type }}</td>
                                <td>{{ $e->email }}</td>
                                <td>{{ optional($e->created_at)->format('Y-m-d') }}</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input status-toggle" type="checkbox" role="switch" id="statusSwitch{{ $e->id }}" data-id="{{ $e->id }}" {{ $e->status ? 'checked' : '' }}>
                                        <label class="form-check-label" for="statusSwitch{{ $e->id }}">{{ $e->status ? 'Active' : 'Inactive' }}</label>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.notification-emails.edit', $e->id) }}" class="btn btn-info btn-sm">Edit</a>
                                    <form action="{{ route('admin.notification-emails.destroy', $e->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $emails->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('inline-js')
    <script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.status-toggle').forEach(function(checkbox){
        checkbox.addEventListener('change', function(){
            var id = this.dataset.id;
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var self = this;
            fetch("{{ url('admin/notification-emails') }}/"+id+"/toggle-status", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            }).then(function(res){ return res.json(); }).then(function(json){
                if(json.success){
                    var label = document.querySelector('label[for="statusSwitch'+id+'"]');
                    if(label){
                        label.textContent = json.status ? 'Active' : 'Inactive';
                    }
                    // keep checkbox state as is (server toggled)
                } else {
                    // revert checkbox if server failed
                    self.checked = !self.checked;
                }
            }).catch(function(err){
                console.error(err);
                self.checked = !self.checked;
            });
        });
    });
});
</script>
@endsection
