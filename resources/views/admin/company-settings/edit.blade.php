
@extends('layouts.admin')

@section('inline-css')
<link href="{{ asset('backend/css/summernote-lite.min.css') }}" rel="stylesheet">
<style>
    .note-editor.note-frame { border: 1px solid #ced4da; border-radius: .375rem; }
    .note-editor.note-frame .note-editable { min-height: 140px; }
</style>
@endsection

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Company Settings</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <form method="POST" action="{{ url('admin/company-settings') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">ABN</label>
                            <input type="text" name="abn" class="form-control" value="{{ old('abn', $details['abn'] ?? $details->abn ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $details['company_name'] ?? $details->company_name ?? '') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $details['address'] ?? $details->address ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bank Details (AUD)</label>
                            <textarea id="bank_details" name="bank_details" class="form-control" rows="4">{{ old('bank_details', $details['bank_details'] ?? $details->bank_details ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bank Details (USD)</label>
                            <textarea id="bank_details_usd" name="bank_details_usd" class="form-control" rows="4">{{ old('bank_details_usd', $details['bank_details_usd'] ?? $details->bank_details_usd ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo</label><br>
                            @if(!empty($details['logo'] ?? $details->logo ?? ''))
                                <img src="{{ $details['logo'] ?? $details->logo ?? '' }}" alt="Logo" style="max-height:80px; border-radius:8px; box-shadow:0 2px 8px #0001; margin-bottom:10px;">
                                <input type="hidden" name="current_logo" value="{{ $details['logo'] ?? $details->logo ?? '' }}">
                            @endif
                            <input type="file" name="logo" class="form-control mt-2">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Update Company Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('inline-js')
<script src="{{ asset('backend/js/summernote-lite.min.js') }}"></script>
<script>
    jQuery(document).ready(function ($) {
        var editorOptions = {
            height: 160,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['misc', ['codeview']],
            ],
            disableDragAndDrop: true,
            callbacks: {
                onImageUpload: function () { return false; }
            }
        };
        $('#bank_details').summernote(editorOptions);
        $('#bank_details_usd').summernote(editorOptions);

        // Sync Summernote HTML back to textarea before form submits
        $('form').on('submit', function () {
            $('#bank_details').val($('#bank_details').summernote('code'));
            $('#bank_details_usd').val($('#bank_details_usd').summernote('code'));
        });
    });
</script>
@endsection
