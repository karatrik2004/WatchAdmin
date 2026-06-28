@php
    $isEdit = isset($notificationEmail);
    $action = $isEdit ? route('admin.notification-emails.update', $notificationEmail->id) : route('admin.notification-emails.store');
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Type</label>
        @php
            $types = [
                'deal_created' => 'Deal Created',
                'sales_invoice_created' => 'Sales Invoice Created',
                'deal_reviewed_ready_for_funding' => 'Deal Reviewed (Ready for Funding)',
            ];
            $selected = old('type', $notificationEmail->type ?? 'deal_created');
        @endphp
        <select name="type" class="form-control" required>
            @foreach($types as $val => $label)
                <option value="{{ $val }}" {{ $selected === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $notificationEmail->email ?? '') }}" required>
        @error('email')<div class="text-danger">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3 form-check">
        <input type="hidden" name="status" value="0">
        <input type="checkbox" name="status" class="form-check-input" id="notificationStatus" value="1" {{ old('status', isset($notificationEmail) ? $notificationEmail->status : true) ? 'checked' : '' }}>
        <label class="form-check-label" for="notificationStatus">Active</label>
    </div>

    <div>
        <button class="btn btn-primary">Save</button>
        <a href="{{ route('admin.notification-emails.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
