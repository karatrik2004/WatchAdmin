<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif
    <div class="form-group">
        <label for="name">Brand Name</label>
        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $brand->name ?? '') }}" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" class="form-control">{{ old('description', $brand->description ?? '') }}</textarea>
    </div>
    <button type="submit" class="btn btn-success mt-2">{{ $buttonText }}</button>
    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary mt-2">Back</a>
</form>